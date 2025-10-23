#!/bin/bash

# Remote Backup Script - Download files from FTP before deployment
# Usage: ./backup-remote.sh [production|staging]

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse command line arguments
ENVIRONMENT="${1:-staging}"

# Load configuration
CONFIG_FILE="deploy.config.sh"
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: $CONFIG_FILE not found${NC}"
    exit 1
fi

source "$CONFIG_FILE"

# Set environment-specific variables
if [ "$ENVIRONMENT" = "production" ]; then
    FTP_HOST="$PROD_FTP_HOST"
    FTP_USER="$PROD_FTP_USER"
    FTP_PASS="$PROD_FTP_PASS"
    FTP_REMOTE_DIR="$PROD_FTP_REMOTE_DIR"
elif [ "$ENVIRONMENT" = "staging" ]; then
    FTP_HOST="$STAGING_FTP_HOST"
    FTP_USER="$STAGING_FTP_USER"
    FTP_PASS="$STAGING_FTP_PASS"
    FTP_REMOTE_DIR="$STAGING_FTP_REMOTE_DIR"
else
    echo -e "${RED}Error: Invalid environment '$ENVIRONMENT'. Use 'production' or 'staging'.${NC}"
    exit 1
fi

# Validate FTP credentials
if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASS" ]; then
    echo -e "${RED}Error: FTP credentials not configured for $ENVIRONMENT environment${NC}"
    exit 1
fi

# Create backup directory with timestamp
BACKUP_BASE="/Users/Nirmal/Documents/backups-hv"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$BACKUP_BASE/${ENVIRONMENT}_${TIMESTAMP}"

mkdir -p "$BACKUP_DIR"

echo -e "${BLUE}════════════════════════════════════════════${NC}"
echo -e "${YELLOW}🔄 Backing up remote files from $ENVIRONMENT${NC}"
echo -e "${BLUE}════════════════════════════════════════════${NC}"
echo "FTP Host: $FTP_HOST"
echo "Remote Directory: $FTP_REMOTE_DIR"
echo "Backup Location: $BACKUP_DIR"
echo ""

# Function to download files using lftp
download_with_lftp() {
    echo -e "${YELLOW}📥 Using lftp for downloading...${NC}"

    if ! lftp -f - <<EOF
open ftp://$FTP_USER:$FTP_PASS@$FTP_HOST
cd $FTP_REMOTE_DIR
mirror --verbose --continue . "$BACKUP_DIR"
quit
EOF
    then
        echo -e "${RED}lftp download failed${NC}"
        return 1
    fi
}

# Function to download files using ncftpget (macOS native)
download_with_ncftpget() {
    echo -e "${YELLOW}📥 Using ncftpget for downloading...${NC}"

    if ncftpget -R -v -u "$FTP_USER" -p "$FTP_PASS" "$FTP_HOST" "$BACKUP_DIR" "$FTP_REMOTE_DIR" 2>&1; then
        return 0
    else
        echo -e "${RED}ncftpget download failed${NC}"
        return 1
    fi
}

# Function to download files using curl with proper recursive approach
download_with_curl_recursive() {
    echo -e "${YELLOW}📥 Using curl for downloading...${NC}"

    # Create FTP URL (without credentials - we'll use --user instead)
    FTP_URL="ftp://$FTP_HOST$FTP_REMOTE_DIR"

    # Test FTP connection first
    echo -e "${YELLOW}Testing FTP connection...${NC}"
    if ! curl -s --connect-timeout 10 --max-time 15 \
              --user "$FTP_USER:$FTP_PASS" \
              --list-only "$FTP_URL/" > /dev/null 2>&1; then
        echo -e "${RED}Cannot connect to FTP server${NC}"
        echo -e "${YELLOW}Details:${NC}"
        echo "Host: $FTP_HOST"
        echo "User: $FTP_USER"
        echo "Dir: $FTP_REMOTE_DIR"
        return 1
    fi

    echo -e "${GREEN}✅ FTP connection successful${NC}"

    # Get file listing first (with full details to detect directories)
    echo -e "${YELLOW}Getting file list...${NC}"
    FILELIST=$(mktemp)
    trap "rm -f $FILELIST" RETURN

    if ! curl -s --max-time 15 \
              --user "$FTP_USER:$FTP_PASS" \
              -l "$FTP_URL/" > "$FILELIST" 2>&1; then
        echo -e "${YELLOW}⚠️  Trying alternative listing method...${NC}"
        # Fallback to basic listing
        curl -s --max-time 15 \
             --user "$FTP_USER:$FTP_PASS" \
             --list-only "$FTP_URL/" > "$FILELIST" 2>&1
    fi

    # Function to check if a path is a directory by attempting to list it
    is_directory() {
        local remote_path=$1
        curl -s --max-time 5 \
             --user "$FTP_USER:$FTP_PASS" \
             --list-only "$FTP_URL$remote_path/" 2>/dev/null | head -1 | grep -q . && return 0 || return 1
    }

    # Function to recursively download directory contents
    download_directory() {
        local remote_path=$1
        local local_path=$2
        local depth=${3:-0}

        # Limit recursion depth
        if [ "$depth" -gt 5 ]; then
            return 0
        fi

        # Get file listing for this directory
        local dir_filelist=$(mktemp)
        trap "rm -f $dir_filelist" RETURN

        if ! curl -s --max-time 15 \
                  --user "$FTP_USER:$FTP_PASS" \
                  --list-only "$FTP_URL$remote_path/" > "$dir_filelist" 2>&1; then
            return 1
        fi

        # Check if we got any results
        if [ ! -s "$dir_filelist" ]; then
            return 1
        fi

        local found_any=0

        while IFS= read -r line; do
            [ -z "$line" ] && continue

            filename=$(echo "$line" | awk '{print $NF}')
            [ -z "$filename" ] && continue
            [ "$filename" = "." ] && continue
            [ "$filename" = ".." ] && continue

            # Try to detect if it's a directory by checking if we can list it
            echo "    Checking: $filename"
            if is_directory "$remote_path/$filename"; then
                # It's a directory - recurse
                echo "      → Directory, recursing..."
                mkdir -p "$local_path/$filename"
                download_directory "$remote_path/$filename" "$local_path/$filename" $((depth+1))
                found_any=1
            else
                # It's a file - download it
                echo "      → File, downloading..."
                if curl -s --max-time 30 \
                         --user "$FTP_USER:$FTP_PASS" \
                         -o "$local_path/$filename" \
                         "$FTP_URL$remote_path/$filename" 2>/dev/null; then
                    found_any=1
                fi
            fi
        done < "$dir_filelist"

        [ "$found_any" -eq 1 ] && return 0 || return 1
    }

    # Parse and download each item from root directory
    echo -e "${YELLOW}Downloading files and directories...${NC}"

    while IFS= read -r line; do
        # Skip empty lines and special entries
        [ -z "$line" ] && continue

        # Extract filename (last field in FTP listing or just the name)
        filename=$(echo "$line" | awk '{print $NF}')
        [ -z "$filename" ] && continue
        [ "$filename" = "." ] && continue
        [ "$filename" = ".." ] && continue

        echo "  📁 Processing: $filename"

        # Check if it's a directory
        if is_directory "/$filename"; then
            # It's a directory - download its contents recursively
            echo "    → Directory found, downloading contents..."
            mkdir -p "$BACKUP_DIR/$filename"
            download_directory "/$filename" "$BACKUP_DIR/$filename" 0
        else
            # It's a file - download it
            echo "    → File found, downloading..."
            curl -s --max-time 30 \
                 --user "$FTP_USER:$FTP_PASS" \
                 -o "$BACKUP_DIR/$filename" \
                 "$FTP_URL/$filename" 2>/dev/null || echo "    ⚠️  Failed to download $filename"
        fi
    done < "$FILELIST"

    echo -e "${GREEN}Download complete${NC}"

    # Check if anything was downloaded
    if [ -n "$(find "$BACKUP_DIR" -type f 2>/dev/null)" ]; then
        return 0
    else
        return 1
    fi
}

# Try download methods in order
SUCCESS=0

if command -v lftp >/dev/null 2>&1; then
    echo -e "${YELLOW}Found lftp, attempting download...${NC}"
    if download_with_lftp; then
        SUCCESS=1
    fi
elif command -v ncftpget >/dev/null 2>&1; then
    echo -e "${YELLOW}Found ncftpget, attempting download...${NC}"
    if download_with_ncftpget; then
        SUCCESS=1
    fi
else
    echo -e "${YELLOW}Using curl for download (slowest method)...${NC}"
    if download_with_curl_recursive; then
        SUCCESS=1
    fi
fi

# Verify backup
FILE_COUNT=$(find "$BACKUP_DIR" -type f 2>/dev/null | wc -l)
DIR_COUNT=$(find "$BACKUP_DIR" -type d 2>/dev/null | wc -l)

echo ""
echo -e "${BLUE}════════════════════════════════════════════${NC}"

if [ "$FILE_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ Backup completed successfully!${NC}"
    echo -e "${GREEN}📁 Directories: $DIR_COUNT${NC}"
    echo -e "${GREEN}📄 Files: $FILE_COUNT${NC}"
    echo -e "${GREEN}💾 Backup saved to: $BACKUP_DIR${NC}"

    # Create info file
    INFO_FILE="$BACKUP_DIR/BACKUP_INFO.txt"
    cat > "$INFO_FILE" <<EOF
Backup Information
==================
Environment: $ENVIRONMENT
FTP Host: $FTP_HOST
Remote Directory: $FTP_REMOTE_DIR
Backup Date: $(date)
File Count: $FILE_COUNT
Directory Count: $DIR_COUNT

To restore this backup, upload all files from this directory
back to your FTP server at: $FTP_REMOTE_DIR
EOF

    echo -e "${GREEN}📝 Backup info saved to: $INFO_FILE${NC}"

    # Create symbolic link to latest backup
    ln -sf "$BACKUP_DIR" "$BACKUP_BASE/latest-${ENVIRONMENT}" 2>/dev/null || true

    echo -e "${BLUE}════════════════════════════════════════════${NC}"
    echo -e "${GREEN}🎉 Backup process completed successfully!${NC}"
    echo ""
    echo -e "${BLUE}Next steps:${NC}"
    echo "1. Review the backup to ensure it's complete"
    echo "2. Run: ${YELLOW}./deploy.sh $ENVIRONMENT${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}❌ ERROR: Backup directory is empty!${NC}"
    echo -e "${YELLOW}No files were downloaded${NC}"
    echo ""
    echo -e "${BLUE}Troubleshooting:${NC}"
    echo "1. Verify FTP credentials in deploy.config.sh"
    echo "2. Test FTP connection manually:"
    echo "   ${YELLOW}ftp $FTP_HOST${NC}"
    echo "3. Check if remote directory exists: ${YELLOW}$FTP_REMOTE_DIR${NC}"
    echo ""
    echo -e "${YELLOW}If manual testing works, try installing lftp:${NC}"
    echo "   ${YELLOW}brew install lftp${NC}"
    echo ""
    echo "Backup directory: $BACKUP_DIR"
    echo -e "${BLUE}════════════════════════════════════════════${NC}"
    exit 1
fi
