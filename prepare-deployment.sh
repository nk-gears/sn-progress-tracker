#!/bin/bash

# Prepare files for deployment
# This script copies the necessary backend files to the frontend build directory

echo "🚀 Preparing Backend Files for Deployment"
echo "========================================"

# Build frontend first
echo "📦 Building frontend for production..."
cd frontend
npm run build
cd ..

# Copy backend files to the dist directory
echo "📋 Copying backend API files..."
cp backend/api.php frontend/dist/
cp backend/config.php frontend/dist/

# Copy updated .htaccess  
echo "📋 Copying .htaccess with API routing..."
cp .htaccess frontend/dist/

# Copy deployment guide
echo "📋 Copying deployment documentation..."
cp DEPLOYMENT_BACKEND.md frontend/dist/

echo "✅ Deployment files prepared!"
echo ""
echo "📁 Files copied to frontend/dist/:"
ls -la frontend/dist/ | grep -E "(api\.php|config\.php|\.htaccess|DEPLOYMENT_BACKEND\.md)"

echo ""
echo "🌐 Next steps:"
echo "1. Upload all files from frontend/dist/ to your server"
echo "2. Update config.php with production database credentials"  
echo "3. Test API endpoints as described in DEPLOYMENT_BACKEND.md"
echo "4. Your API will be available at: https://your-domain.com/sn-progress/api/"