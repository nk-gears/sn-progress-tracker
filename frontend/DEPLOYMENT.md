# Deploying to Vercel

This guide will help you deploy the Meditation Time Tracker Vue.js app to Vercel.

## 🚀 Quick Deploy

### Option 1: Deploy via Vercel CLI (Recommended)

1. **Install Vercel CLI:**
   ```bash
   npm i -g vercel
   ```

2. **Login to Vercel:**
   ```bash
   vercel login
   ```

3. **Deploy from the frontend directory:**
   ```bash
   cd frontend
   vercel
   ```

4. **Follow the prompts:**
   - Set up and deploy: `Y`
   - Which scope: Choose your account
   - Link to existing project: `N` (for first deployment)
   - Project name: `meditation-tracker` (or your preferred name)
   - Directory: `./` (current directory)

5. **Production deployment:**
   ```bash
   vercel --prod
   ```

### Option 2: Deploy via GitHub Integration

1. **Push your code to GitHub:**
   ```bash
   git add .
   git commit -m "Prepare for Vercel deployment"
   git push origin main
   ```

2. **Connect to Vercel:**
   - Go to [vercel.com](https://vercel.com)
   - Click "New Project"
   - Import your GitHub repository
   - Set the root directory to `frontend`
   - Vercel will auto-detect it's a Vite project

3. **Configure environment variables** (in Vercel dashboard):
   - `VITE_API_MODE` = `mock`
   - `VITE_API_BASE_URL` = `` (empty)
   - `VITE_ENABLE_API_SWITCHER` = `false`
   - `VITE_ENABLE_MOCK_DELAY` = `false`

## 📋 Pre-deployment Checklist

- ✅ Vercel configuration file (`vercel.json`) is created
- ✅ Production environment variables are set
- ✅ Build script includes type checking
- ✅ App is configured to use mock API in production
- ✅ All dependencies are properly listed in `package.json`

## 🔧 Configuration Files

### vercel.json
```json
{
  "version": 2,
  "name": "meditation-tracker",
  "builds": [
    {
      "src": "package.json",
      "use": "@vercel/static-build",
      "config": {
        "distDir": "dist"
      }
    }
  ],
  "routes": [
    {
      "src": "/(.*)",
      "dest": "/index.html"
    }
  ]
}
```

### Environment Variables
The app is configured to run with mock data in production, making it a complete standalone demo without requiring a backend server.

## 🎯 Demo Features

Once deployed, your app will include:

- **Complete Mock API**: Realistic data simulation
- **Mobile-Optimized UI**: Touch-friendly interface
- **Session Management**: Create, edit, delete meditation sessions
- **Participant Management**: Smart autocomplete with conditional fields
- **Dashboard Analytics**: Monthly stats and insights
- **Multi-branch Support**: Switch between different locations

## 📱 Demo Credentials

**Mobile:** 9283181228  
**Password:** meditation123

## 🔄 Updating the Deployment

### Via CLI:
```bash
cd frontend
vercel --prod
```

### Via GitHub:
Simply push your changes to the main branch, and Vercel will automatically redeploy.

## 🛠️ Troubleshooting

### Build Failures
1. **Type Errors**: Run `npm run type-check` locally first
2. **Dependency Issues**: Ensure all dependencies are in `package.json`
3. **Environment Variables**: Check Vercel dashboard settings

### Runtime Errors
1. **404 on Refresh**: Vercel.json routing should handle this
2. **API Errors**: App uses mock API, no backend required
3. **Mobile Issues**: Test responsive design locally first

### Performance Issues
1. **Slow Loading**: App includes code splitting and optimizations
2. **Large Bundle**: Vite automatically optimizes the build
3. **SEO**: This is a SPA, consider SSG if SEO is needed

## 📊 Build Output

Expected build output:
```
✓ built in [time]ms
dist/index.html                [size]
dist/assets/index-[hash].js     [size]
dist/assets/index-[hash].css    [size]
```

## 🌐 Custom Domain (Optional)

1. **In Vercel Dashboard:**
   - Go to your project settings
   - Navigate to "Domains"
   - Add your custom domain
   - Configure DNS records as instructed

2. **SSL Certificate:**
   - Automatically provided by Vercel
   - No manual configuration needed

Your meditation tracker app will be live and accessible to volunteers across Tamil Nadu! 🧘‍♂️