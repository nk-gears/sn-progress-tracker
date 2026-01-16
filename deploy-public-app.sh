#!/bin/bash

# FTP Deployment Script for Public Web App (Shivanum Naanum Join)
# Deploys web-app build output to /public_html/sn-join/
# Usage: ./deploy-public-app.sh [production|staging] [--force]

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
    # Override remote directory to sn-join
    FTP_REMOTE_DIR="/sn-join"
else
    FTP_HOST="$STAGING_FTP_HOST"
    FTP_USER="$STAGING_FTP_USER"
    FTP_PASS="$STAGING_FTP_PASS"
    # Override remote directory to sn-join for staging as well
    FTP_REMOTE_DIR="/sn-join"
fi

# Validate FTP credentials
if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASS" ]; then
    echo -e "${RED}Error: FTP credentials not configured for $ENVIRONMENT environment${NC}"
    exit 1
fi

echo -e "${YELLOW}🚀 Starting deployment of Public Web App to $ENVIRONMENT environment...${NC}"
echo "FTP Host: $FTP_HOST"
echo "Remote Directory: $FTP_REMOTE_DIR"
echo ""

# Create temporary directory for deployment
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

echo -e "${YELLOW}📦 Building Public Web App (web-app)...${NC}"

# Build web-app
cd web-app
if [ ! -f "package.json" ]; then
    echo -e "${RED}Error: package.json not found in web-app directory${NC}"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing web-app dependencies..."
    npm install
fi

# Build for production with explicit NODE_ENV to use .env.production
echo "Using NODE_ENV=production to load .env.production..."
NODE_ENV=production npm run build

if [ ! -d "dist" ]; then
    echo -e "${RED}Error: Web app build failed - dist directory not found${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Web app build completed${NC}"

# Copy built web app to temp directory
cp -r dist/* "$TEMP_DIR/"

# Create/Copy .htaccess file for Vue.js SPA routing
HTACCESS_CONTENT='# Enable mod_rewrite
RewriteEngine On

# Handle Vue.js SPA routing
# Redirect all requests to index.html unless they are actual files/directories
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]

# Optional: Force HTTPS (uncomment if needed)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/x-javascript "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
</IfModule>

# Compress output
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml application/javascript application/json image/svg+xml
</IfModule>
'

# Check if .htaccess exists in web-app or root
if [ -f ".htaccess" ]; then
    cp ".htaccess" "$TEMP_DIR/"
    echo -e "${GREEN}✅ Added .htaccess file for SPA routing${NC}"
elif [ -f "../.htaccess" ]; then
    cp "../.htaccess" "$TEMP_DIR/"
    echo -e "${GREEN}✅ Added .htaccess file for SPA routing${NC}"
else
    # Create default .htaccess
    echo "$HTACCESS_CONTENT" > "$TEMP_DIR/.htaccess"
    echo -e "${GREEN}✅ Created default .htaccess file for SPA routing${NC}"
fi

cd ..

echo -e "${GREEN}✅ Web app files prepared${NC}"
echo ""

echo -e "${YELLOW}🌐 Uploading files via FTP to $FTP_REMOTE_DIR...${NC}"

# Create deployment metadata directory
DEPLOY_META_DIR="$HOME/.sn-progress-deploy"
mkdir -p "$DEPLOY_META_DIR"
LAST_DEPLOY_FILE="$DEPLOY_META_DIR/last-deploy-public-${ENVIRONMENT}.txt"

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

    TOTAL_FILES=$(wc -l < "$CHANGED_FILES")
    echo -e "${YELLOW}Uploading $TOTAL_FILES files...${NC}"

    lftp -f - <<EOF
open ftp://$FTP_USER:$FTP_PASS@$FTP_HOST
cd $FTP_REMOTE_DIR || mkdir -p $FTP_REMOTE_DIR

# Clear existing files for clean deployment
echo "Clearing existing files..."
glob -a rm -f *
glob rm -rf assets/

# Upload all web app files
echo "Uploading new files..."
mirror --reverse --verbose "$TEMP_DIR/" ./

quit
EOF
else
    # Fallback to curl for FTP upload
    echo "Using curl for upload..."

    TOTAL_FILES=$(wc -l < "$CHANGED_FILES")
    echo -e "${YELLOW}Uploading $TOTAL_FILES files...${NC}"

    # Create the remote directory if it doesn't exist
    echo -e "${YELLOW}Creating remote directory if needed...${NC}"
    curl -s --ftp-create-dirs \
         -T /dev/null \
         "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST$FTP_REMOTE_DIR/" >/dev/null 2>&1 || true

    # Clear existing files first
    echo -e "${YELLOW}Clearing existing files...${NC}"
    for file in index.html favicon.ico favicon.svg manifest.json; do
        curl -s --quote "DELE $FTP_REMOTE_DIR/$file" \
             "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST/" >/dev/null 2>&1 || true
    done

    # Delete assets directory
    curl -s --quote "RMD $FTP_REMOTE_DIR/assets" \
         "ftp://$ENCODED_USER:$ENCODED_PASS@$FTP_HOST/" >/dev/null 2>&1 || true

    echo -e "${YELLOW}📤 Uploading all web app files...${NC}"

    # Upload all files
    COUNTER=0
    find "$TEMP_DIR" -type f | while read -r file; do
        COUNTER=$((COUNTER + 1))
        relative_path="${file#$TEMP_DIR/}"
        remote_path="$FTP_REMOTE_DIR/$relative_path"
        remote_parent_dir=$(dirname "$remote_path")

        echo "[$COUNTER/$TOTAL_FILES] Uploading: $relative_path"

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
            exit 1
        fi
    done
fi

if [ $? -eq 0 ]; then
    # Save current checksums for next deployment
    cp "$CURRENT_CHECKSUMS" "$LAST_DEPLOY_FILE"

    echo ""
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo -e "${GREEN}🌍 Your public web app should now be live at:${NC}"

    if [ "$ENVIRONMENT" = "production" ] && [ -n "$PROD_DOMAIN" ]; then
        echo -e "${GREEN}   http://$PROD_DOMAIN/sn-join/${NC}"
    elif [ "$ENVIRONMENT" = "staging" ] && [ -n "$STAGING_DOMAIN" ]; then
        echo -e "${GREEN}   http://$STAGING_DOMAIN/sn-join/${NC}"
    else
        echo -e "${GREEN}   http://$FTP_HOST/sn-join/${NC}"
    fi

    echo -e "${GREEN}📝 File checksums saved for next incremental deployment${NC}"
else
    echo -e "${RED}❌ Deployment failed during FTP upload${NC}"
    exit 1
fi

# Optional: Test the deployment
if [ -n "$PROD_DOMAIN" ] && [ "$ENVIRONMENT" = "production" ]; then
    echo ""
    echo -e "${YELLOW}🔍 Testing deployment...${NC}"
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://$PROD_DOMAIN/sn-join/" || echo "000")
    if [ "$HTTP_STATUS" = "200" ]; then
        echo -e "${GREEN}✅ Website is responding correctly${NC}"
    else
        echo -e "${YELLOW}⚠️  Website returned HTTP $HTTP_STATUS (may need time to propagate)${NC}"
    fi
fi

echo ""
echo -e "${GREEN}🎉 Public Web App deployment process completed!${NC}"
echo ""
echo -e "${YELLOW}📝 Note: This deployment only includes the public web app (Shivanum Naanum Join form)${NC}"
echo -e "${YELLOW}   The admin app and backend API are deployed separately using ./deploy.sh${NC}"
