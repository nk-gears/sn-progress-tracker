#!/bin/bash

# FTP Deployment Configuration
# Copy this file to deploy.config.sh and fill in your FTP credentials

# # Production Environment
# PROD_FTP_HOST="your-production-ftp-host.com"
# PROD_FTP_USER="your-ftp-username"
# PROD_FTP_PASS="your-ftp-password"
# PROD_FTP_REMOTE_DIR="/public_html/sn-progress"  # or "/htdocs" or "/www" depending on your host
# PROD_DOMAIN="your-domain.com"

# Staging Environment (optional)
STAGING_FTP_HOST="82.180.152.203"
STAGING_FTP_USER="u388678206.happy-village.org"
STAGING_FTP_PASS="2X{.@f+j*X0N"
STAGING_FTP_REMOTE_DIR="/sn-progress"
STAGING_DOMAIN="happy-village.org"

# Common FTP Settings
# Most shared hosting providers use these paths:
# - cPanel: /public_html
# - Plesk: /httpdocs
# - DirectAdmin: /public_html
# - Custom: Ask your hosting provider

# Example configurations for popular hosting providers:

# # cPanel/WHM hosting:
# PROD_FTP_REMOTE_DIR="/public_html"

# # Plesk hosting:
# PROD_FTP_REMOTE_DIR="/httpdocs"

# # Subdirectory deployment:
# PROD_FTP_REMOTE_DIR="/public_html/meditation-app"