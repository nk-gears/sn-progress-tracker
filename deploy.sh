#!/bin/bash

# FTP Deployment Script for Vue.js Frontend + PHP Backend
# Usage: ./deploy.sh [production|staging]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Parse command line arguments
FORCE_DEPLOY=false
ENVIRONMENT="production"

while [[ $# -gt 0 ]]; do
    case $1 in
        --force)
            FORCE_DEPLOY=true
            shift
            ;;
        production|staging)
            ENVIRONMENT=$1
            shift
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            echo "Usage: $0 [production|staging] [--force]"
            exit 1
            ;;
    esac
done

# Load configuration
CONFIG_FILE="deploy.config.sh"
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: $CONFIG_FILE not found. Please create it first.${NC}"
    echo "Run: cp deploy.config.example.sh deploy.config.sh"
    exit 1
fi

source "$CONFIG_FILE"

# Validate environment
case $ENVIRONMENT in
    production|staging)
        ;;
    *)
        echo -e "${RED}Error: Invalid environment '$ENVIRONMENT'. Use 'production' or 'staging'.${NC}"
        exit 1
        ;;
esac

# Set environment-specific variables
if [ "$ENVIRONMENT" = "production" ]; then
    FTP_HOST="$PROD_FTP_HOST"
    FTP_USER="$PROD_FTP_USER"
    FTP_PASS="$PROD_FTP_PASS"
    FTP_REMOTE_DIR="$PROD_FTP_REMOTE_DIR"
else
    FTP_HOST="$STAGING_FTP_HOST"
    FTP_USER="$STAGING_FTP_USER"
    FTP_PASS="$STAGING_FTP_PASS"
    FTP_REMOTE_DIR="$STAGING_FTP_REMOTE_DIR"
fi

# Validate FTP credentials
if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASS" ]; then
    echo -e "${RED}Error: FTP credentials not configured for $ENVIRONMENT environment${NC}"
    exit 1
fi

echo -e "${YELLOW}🚀 Starting deployment to $ENVIRONMENT environment...${NC}"
echo "FTP Host: $FTP_HOST"
echo "Remote Directory: $FTP_REMOTE_DIR"
echo ""

# Create temporary directory for deployment
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

echo -e "${YELLOW}📦 Building Vue.js frontend...${NC}"

# Build Vue.js frontend
cd frontend
if [ ! -f "package.json" ]; then
    echo -e "${RED}Error: package.json not found in frontend directory${NC}"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing frontend dependencies..."
    npm install
fi

# Build for production
npm run build

if [ ! -d "dist" ]; then
    echo -e "${RED}Error: Frontend build failed - dist directory not found${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Frontend build completed${NC}"

# Copy built frontend to temp directory
cp -r dist/* "$TEMP_DIR/"

# Copy .htaccess file for Vue.js SPA routing
if [ -f "../.htaccess" ]; then
    cp "../.htaccess" "$TEMP_DIR/"
    echo -e "${GREEN}✅ Added .htaccess file for SPA routing${NC}"
elif [ -f ".htaccess" ]; then
    cp ".htaccess" "$TEMP_DIR/"
    echo -e "${GREEN}✅ Added .htaccess file for SPA routing${NC}"
else
    echo -e "${YELLOW}⚠️  .htaccess file not found - SPA routing may not work${NC}"
fi

cd ..

echo -e "${YELLOW}📋 Preparing PHP backend...${NC}"

# Copy PHP backend files
if [ ! -d "backend" ]; then
    echo -e "${RED}Error: backend directory not found${NC}"
    exit 1
fi

# Create backend directory in temp
mkdir -p "$TEMP_DIR/api"

# Copy PHP files (exclude development files and config.php)
rsync -av --exclude='*.log' \
          --exclude='*.tmp' \
          --exclude='.env' \
          --exclude='config.php' \
          --exclude='test-db-connection.php' \
          --exclude='docker-compose.yml' \
          --exclude='start-*.sh' \
          --exclude='*README.md' \
          --exclude='old-pdo/' \
          backend/ "$TEMP_DIR/api/"

echo -e "${YELLOW}ℹ️  Skipped config.php to preserve remote database credentials${NC}"

echo -e "${GREEN}✅ Backend files prepared${NC}"

echo -e "${YELLOW}🌐 Uploading files via FTP...${NC}"

# Create deployment metadata directory
DEPLOY_META_DIR="$HOME/.sn-progress-deploy"
mkdir -p "$DEPLOY_META_DIR"
LAST_DEPLOY_FILE="$DEPLOY_META_DIR/last-deploy-${ENVIRONMENT}.txt"

# Create checksums for current files
CURRENT_CHECKSUMS="$TEMP_DIR/current-checksums.txt"
find "$TEMP_DIR" -type f -exec md5sum {} \; | sed "s|$TEMP_DIR/||g" > "$CURRENT_CHECKSUMS"

# Compare with last deployment (if exists)
CHANGED_FILES="$TEMP_DIR/changed-files.txt"
if [ -f "$LAST_DEPLOY_FILE" ]; then
    echo -e "${YELLOW}📊 Checking for changed files since last deployment...${NC}"
    
    # Find changed files by comparing checksums
    if diff -u "$LAST_DEPLOY_FILE" "$CURRENT_CHECKSUMS" | grep '^+' | grep -v '^+++' | sed 's/^+[a-f0-9]*  //' > "$CHANGED_FILES"; then
        CHANGED_COUNT=$(wc -l < "$CHANGED_FILES")
        echo -e "${GREEN}Found $CHANGED_COUNT changed files${NC}"
        
        # Show changed files (limit to first 10)
        echo "Changed files:"
        head -10 "$CHANGED_FILES" | sed 's/^/  - /'
        if [ "$CHANGED_COUNT" -gt 10 ]; then
            echo "  ... and $((CHANGED_COUNT - 10)) more"
        fi
    else
        echo -e "${GREEN}No changes detected since last deployment${NC}"
        if [ "$FORCE_DEPLOY" = true ]; then
            echo -e "${YELLOW}--force flag specified, uploading all files anyway${NC}"
            cat "$CURRENT_CHECKSUMS" | cut -d' ' -f3- > "$CHANGED_FILES"
        else
            echo -e "${YELLOW}Skipping upload (use --force to upload anyway)${NC}"
            exit 0
        fi
    fi
else
    echo -e "${YELLOW}First deployment - uploading all files${NC}"
    cat "$CURRENT_CHECKSUMS" | cut -d' ' -f3- > "$CHANGED_FILES"
fi

# Function to URL-encode special characters in passwords
url_encode() {
    local string="$1"
    local strlen=${#string}
    local encoded=""
    local pos c o

    for (( pos=0 ; pos<strlen ; pos++ )); do
        c=${string:$pos:1}
        case "$c" in
            [-_.~a-zA-Z0-9] ) o="${c}" ;;
            * ) printf -v o '%%%02x' "'$c" ;;
        esac
        encoded+="${o}"
    done
    echo "${encoded}"
}

# URL-encode credentials for curl
ENCODED_USER=$(url_encode "$FTP_USER")
ENCODED_PASS=$(url_encode "$FTP_PASS")

# Execute FTP upload
if command -v lftp >/dev/null 2>&1; then
    # Use lftp for smart deployment
    echo "Using lftp for upload..."
    
    # Check if we have frontend files (non-api files)
    FRONTEND_FILES=$(grep -v '^api/' "$CHANGED_FILES" | wc -l)
    BACKEND_FILES=$(grep '^api/' "$CHANGED_FILES" | wc -l)
    
    lftp -f - <<EOF
open ftp://$FTP_USER:$FTP_PASS@$FTP_HOST
cd $FTP_REMOTE_DIR

# Handle frontend deployment (full replacement)
$(if [ "$FRONTEND_FILES" -gt 0 ]; then
    echo "# Frontend files changed - doing full replacement"
    echo "# Delete existing frontend files (keep api/ directory)"
    echo "glob -a rm -f *"
    echo "glob rm -rf assets/"
    echo "# Upload new frontend files"
    echo "mirror --reverse --verbose --exclude=api/ \"$TEMP_DIR/\" ./"
fi)

# Handle backend deployment (incremental)
$(if [ "$BACKEND_FILES" -gt 0 ]; then
    echo "# Backend files changed - incremental upload"
    echo "mirror --reverse --verbose --include=api/ \"$TEMP_DIR/\" ./"
fi)

quit
EOF
else
    # Fallback to curl for FTP upload
    echo "Using curl for upload..."
    
    # Separate frontend and backend files
    FRONTEND_FILES=$(grep -v '^api/' "$CHANGED_FILES" || true)
    BACKEND_FILES=$(grep '^api/' "$CHANGED_FILES" || true)
    
    # Handle frontend deployment (full replacement)
    if [ -n "$FRONTEND_FILES" ]; then
        echo -e "${YELLOW}🎨 Frontend files changed - clearing existing frontend files...${NC}"
        
        # Delete existing frontend files (but preserve api directory)
        # This is a simplified approach - delete common frontend files
        for file in index.html favicon.ico manifest.json; do
            curl -s --quote "DELE $FTP_REMOTE_DIR/$file" \
                 "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST/" >/dev/null 2>&1 || true
        done
        
        # Delete assets directory
        curl -s --quote "RMD $FTP_REMOTE_DIR/assets" \
             "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST/" >/dev/null 2>&1 || true
        
        echo -e "${YELLOW}📤 Uploading all frontend files...${NC}"
        # Upload all frontend files
        find "$TEMP_DIR" -type f ! -path "*/api/*" | while read -r file; do
            relative_path="${file#$TEMP_DIR/}"
            remote_path="$FTP_REMOTE_DIR/$relative_path"
            remote_parent_dir=$(dirname "$remote_path")
            
            echo "Uploading: $relative_path"
            
            # Create directory structure if needed
            if [ "$remote_parent_dir" != "$FTP_REMOTE_DIR" ]; then
                curl -s --ftp-create-dirs \
                     -T /dev/null \
                     "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST$remote_parent_dir/" >/dev/null 2>&1 || true
            fi
            
            # Upload the file
            if ! curl -s --ftp-create-dirs \
                      -T "$file" \
                      "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST$remote_path"; then
                echo -e "${RED}Failed to upload: $relative_path${NC}"
                return 1
            fi
        done
    fi
    
    # Handle backend deployment (incremental)
    if [ -n "$BACKEND_FILES" ]; then
        echo -e "${YELLOW}🔧 Uploading changed backend files...${NC}"
        
        # Upload only changed backend files
        echo "$BACKEND_FILES" | while read -r relative_path; do
            if [ -n "$relative_path" ]; then
                file="$TEMP_DIR/$relative_path"
                remote_path="$FTP_REMOTE_DIR/$relative_path"
                remote_parent_dir=$(dirname "$remote_path")
                
                echo "Uploading: $relative_path"
                
                # Create directory structure if needed
                if [ "$remote_parent_dir" != "$FTP_REMOTE_DIR" ]; then
                    curl -s --ftp-create-dirs \
                         -T /dev/null \
                         "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST$remote_parent_dir/" >/dev/null 2>&1 || true
                fi
                
                # Upload the file
                if ! curl --ftp-create-dirs \
                          -T "$file" \
                          "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST$remote_path"; then
                    echo -e "${RED}Failed to upload: $relative_path${NC}"
                    return 1
                fi
            fi
        done
    fi
fi

if [ $? -eq 0 ]; then
    # Save current checksums for next deployment
    cp "$CURRENT_CHECKSUMS" "$LAST_DEPLOY_FILE"
    
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo -e "${GREEN}🌍 Your application should now be live at your domain${NC}"
    echo -e "${GREEN}📝 File checksums saved for next incremental deployment${NC}"
else
    echo -e "${RED}❌ Deployment failed during FTP upload${NC}"
    exit 1
fi

# Optional: Test the deployment
if [ -n "$PROD_DOMAIN" ] && [ "$ENVIRONMENT" = "production" ]; then
    echo -e "${YELLOW}🔍 Testing deployment...${NC}"
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://$PROD_DOMAIN" || echo "000")
    if [ "$HTTP_STATUS" = "200" ]; then
        echo -e "${GREEN}✅ Website is responding correctly${NC}"
    else
        echo -e "${YELLOW}⚠️  Website returned HTTP $HTTP_STATUS (may need time to propagate)${NC}"
    fi
fi

echo -e "${GREEN}🎉 Deployment process completed!${NC}"