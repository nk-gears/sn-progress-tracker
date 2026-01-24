# GitHub Actions - Automated Web App Deployment

This document explains how to set up and use the GitHub Actions workflow for automated deployment of the web app.

## Overview

The workflow automatically deploys the web application whenever:
1. A change is pushed to `version.txt` file
2. Changes are made to files in the `web-app/` directory
3. The workflow file itself is modified (for testing)

The workflow can also be triggered manually using GitHub's workflow dispatch feature.

## Setup Instructions

### Step 1: Add GitHub Secrets

The workflow requires the following secrets to be configured in your GitHub repository. Go to:
**Settings → Secrets and variables → Actions**

Add the following secrets:

#### Required Secrets:

| Secret Name | Value | Description |
|------------|-------|-------------|
| `FTP_HOST` | `your-ftp-server.com` | FTP server hostname (e.g., happy-village.org) |
| `FTP_USER` | `your-ftp-username` | FTP account username |
| `FTP_PASSWORD` | `your-ftp-password` | FTP account password |
| `VITE_API_BASE_URL` | `https://happy-village.org/sn-progress` | Production API base URL (optional) |

#### Optional Secrets:

| Secret Name | Value | Description |
|------------|-------|-------------|
| `SLACK_WEBHOOK_URL` | `https://hooks.slack.com/...` | Slack webhook for failure notifications |

### Step 2: Update version.txt to Trigger Deployment

To deploy the application, simply update the `version.txt` file and push it:

```bash
# Update version
echo "1.1.0" > version.txt

# Commit and push
git add version.txt
git commit -m "Release v1.1.0"
git push origin main
```

This will automatically trigger the deployment workflow.

### Step 3: Monitor Deployment

1. Go to **Actions** tab in your GitHub repository
2. Click on the "Deploy Web App on Version Change" workflow
3. You'll see the deployment in progress with logs for each step:
   - **Detect Version**: Reads the version from version.txt
   - **Build**: Builds the web app with `NODE_ENV=production`
   - **Deploy**: Uploads built files to FTP server
   - **Notify**: Sends summary (and optional Slack notification)

## Workflow Details

### Trigger Conditions

The workflow is triggered when:

```yaml
paths:
  - 'version.txt'           # Primary trigger
  - 'web-app/**'            # Any changes in web-app directory
  - '.github/workflows/deploy-web-app.yml'  # Workflow file changes
```

### Build Process

The workflow:
1. ✅ Checks out the code
2. ✅ Sets up Node.js 18
3. ✅ Installs dependencies with `npm ci`
4. ✅ Builds with `NODE_ENV=production npm run build`
5. ✅ Verifies `dist/` directory exists
6. ✅ Uploads artifacts to GitHub (30-day retention)

### Deployment Process

The workflow:
1. ✅ Downloads the build artifacts
2. ✅ Creates `.htaccess` file for Vue SPA routing
3. ✅ Uploads to FTP server at `/public_html/sn-join/`
4. ✅ Uses smart sync to only upload changed files
5. ✅ Preserves existing files (safe deployment)

## Using the Workflow

### Automatic Deployment

Simply update `version.txt` and push:

```bash
echo "1.2.0" > version.txt
git add version.txt
git commit -m "Release v1.2.0"
git push
```

The workflow will automatically:
- Detect the version change
- Build the application
- Deploy to production
- Send notifications

### Manual Deployment

You can manually trigger the workflow from GitHub:

1. Go to **Actions** → **Deploy Web App on Version Change**
2. Click **Run workflow**
3. Select the environment (production or staging)
4. Click **Run workflow**

### Example: Release Process

```bash
# 1. Make your changes
git add web-app/src/components/...
git commit -m "feat: add new feature"

# 2. Bump version
echo "1.3.0" > version.txt
git add version.txt
git commit -m "Release v1.3.0"

# 3. Push to trigger deployment
git push origin main

# 4. Monitor in GitHub Actions tab
```

## Environment Variables

### .env.production

The workflow uses `NODE_ENV=production`, which makes Vite automatically load `.env.production`:

```env
# .env.production
VITE_API_BASE_URL=https://happy-village.org/sn-progress
VITE_API_MODE=real
VITE_ENABLE_API_SWITCHER=false
VITE_ENABLE_MOCK_DELAY=false
```

You can override `VITE_API_BASE_URL` by setting the `VITE_API_BASE_URL` GitHub Secret.

## Troubleshooting

### Deployment Failed

1. **Check the logs**: Go to Actions tab and click the failed workflow run
2. **Verify secrets**: Ensure FTP credentials are correct in GitHub Secrets
3. **Check FTP server**: Verify the FTP server is accessible and credentials are valid
4. **Check disk space**: Ensure FTP server has sufficient space

### Build Failed

1. **Check Node.js version**: The workflow uses Node.js 18
2. **Check dependencies**: Verify `web-app/package.json` is valid
3. **Check for errors**: Review the build log for specific errors

### Files Not Uploading

1. **Check FTP path**: Default path is `/public_html/sn-join/`
2. **Check .htaccess**: Ensure `.htaccess` file is created properly
3. **Check FTP permissions**: Verify the FTP user has write permissions

## Secrets Setup Example

Here's how to add secrets via GitHub CLI:

```bash
# Set FTP credentials
gh secret set FTP_HOST -b "your-ftp-server.com"
gh secret set FTP_USER -b "your-ftp-username"
gh secret set FTP_PASSWORD -b "your-ftp-password"

# Set API URL (optional)
gh secret set VITE_API_BASE_URL -b "https://happy-village.org/sn-progress"

# Set Slack webhook (optional)
gh secret set SLACK_WEBHOOK_URL -b "https://hooks.slack.com/services/YOUR/WEBHOOK/URL"
```

## Monitoring and Notifications

### GitHub Actions Notifications

GitHub will send email notifications for:
- ✅ Workflow runs (if enabled in settings)
- ❌ Workflow failures
- ⏸️ Workflow cancellations

### Slack Notifications (Optional)

If you set the `SLACK_WEBHOOK_URL` secret, the workflow will send Slack messages on deployment failures.

To set up Slack:
1. Create a Slack App or use an existing one
2. Add an Incoming Webhook to your channel
3. Copy the webhook URL to the `SLACK_WEBHOOK_URL` GitHub Secret

## Best Practices

### Version Management

Use semantic versioning for version.txt:
- `1.0.0` → `1.0.1` for bug fixes
- `1.0.0` → `1.1.0` for new features
- `1.0.0` → `2.0.0` for major changes

### Commit Messages

Use clear commit messages for deployments:
```bash
git commit -m "Release v1.2.0 - Add new features"
```

### Testing Before Deployment

Always test locally before pushing:
```bash
cd web-app
npm run build
npm run preview
```

### Backup Previous Deployments

GitHub artifacts are kept for 30 days:
- Each deployment creates a timestamped artifact
- You can restore previous versions if needed

## File Structure

```
.github/
└── workflows/
    └── deploy-web-app.yml          # Main deployment workflow
version.txt                          # Version file (triggers deployment)
web-app/
├── dist/                           # Built files (auto-generated)
├── src/
├── package.json
├── .env
└── .env.production                 # Production environment variables
```

## Workflow Status Badge

You can add a status badge to your README:

```markdown
![Deploy Web App](https://github.com/YOUR_USERNAME/sn-progress-app/actions/workflows/deploy-web-app.yml/badge.svg)
```

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [FTP Deploy Action](https://github.com/SamKirkland/FTP-Deploy-Action)
- [Slack GitHub Action](https://github.com/slackapi/slack-github-action)
- [Node.js Setup Action](https://github.com/actions/setup-node)

## Support

For issues or questions:
1. Check the workflow logs in GitHub Actions
2. Verify all secrets are configured correctly
3. Ensure FTP server is accessible
4. Review error messages in the workflow run logs

---

**Last Updated**: January 24, 2026
**Workflow Version**: 1.0
