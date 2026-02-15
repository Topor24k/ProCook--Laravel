# ProCook Recipe Manager - Complete Deployment Guide

## 📋 Pre-Deployment Checklist

- [ ] Export database from HeidiSQL/Laragon
- [ ] Update environment variables with production URLs
- [ ] Test application locally
- [ ] Commit all changes to Git
- [ ] Push to GitHub repository

## 🌐 Frontend Deployment to Vercel

### Step 1: Prepare Frontend Repository
1. Create a new repository on GitHub for frontend only
2. Copy contents of `frontend-deploy/` folder to the new repository
3. Update `src/services/api.js` with your actual Render backend URL

### Step 2: Deploy to Vercel
1. Go to [vercel.com](https://vercel.com) and sign in with GitHub
2. Click "New Project"
3. Import your frontend repository
4. Configure build settings:
   - Framework Preset: `Vite`
   - Build Command: `npm run build`
   - Output Directory: `dist`
   - Install Command: `npm install`

5. Add Environment Variables:
   ```
   VITE_API_URL=https://your-backend-app.onrender.com
   VITE_APP_NAME=ProCook Recipe Manager
   VITE_APP_ENV=production
   ```

6. Click "Deploy"
7. Your frontend will be available at: `https://your-app-name.vercel.app`

## ⚙️ Backend Deployment to Render

### Step 1: Setup Database
Choose one of these options:

#### Option A: PlanetScale (Recommended)
1. Go to [planetscale.com](https://planetscale.com)
2. Create account and new database
3. Import your MySQL backup
4. Get connection string

#### Option B: Render PostgreSQL
1. In Render dashboard, create PostgreSQL database
2. Convert and import your data
3. Note connection details

### Step 2: Deploy Laravel to Render
1. Go to [render.com](https://render.com) and sign up
2. Click "New +" → "Web Service"
3. Connect your GitHub repository (the Laravel project)
4. Configure:
   - Name: `procook-api` (or your choice)
   - Environment: `Docker`
   - Build Command: `chmod +x render-build.sh && ./render-build.sh`
   - Start Command: `php -S 0.0.0.0:$PORT -t public/`

5. Add Environment Variables (copy from `.env.render`):
   ```
   APP_NAME=ProCook Recipe Manager
   APP_ENV=production
   APP_KEY=base64:Cd+ko13utMu0bT8EWySoVp9RpOKMcdSizhSHGpdW2t8=
   APP_DEBUG=false
   APP_URL=https://your-backend-app.onrender.com
   
   # Database (update with your actual values)
   DB_CONNECTION=mysql
   DB_HOST=your-db-host
   DB_PORT=3306
   DB_DATABASE=your-db-name
   DB_USERNAME=your-db-username
   DB_PASSWORD=your-db-password
   
   # Frontend URL (update after frontend is deployed)
   SANCTUM_STATEFUL_DOMAINS=your-frontend-app.vercel.app
   FRONTEND_URL=https://your-frontend-app.vercel.app
   
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   ```

6. Click "Create Web Service"

### Step 3: Update CORS and Frontend API URL

1. Once backend is deployed, update your **Vercel environment variables**:
   ```
   VITE_API_URL=https://your-actual-backend-url.onrender.com
   ```

2. Redeploy your frontend on Vercel

3. Update `config/cors.php` in your Laravel app:
   ```php
   'allowed_origins' => [
       'https://your-actual-frontend-url.vercel.app',
       // ... other origins
   ],
   ```

4. Update environment variables in Render:
   ```
   SANCTUM_STATEFUL_DOMAINS=your-actual-frontend-url.vercel.app
   FRONTEND_URL=https://your-actual-frontend-url.vercel.app
   ```

## 🔍 Testing Deployment

### Test these endpoints:
1. `GET https://your-backend-app.onrender.com/api/recipes` - Should return recipes
2. Visit your frontend URL - Should load the React app
3. Try registration/login - Should work end-to-end

## ⚠️ Common Issues & Solutions

### Issue: CORS Errors
**Solution**: Double-check that your frontend URL is added to both:
- Laravel `config/cors.php` `allowed_origins`  
- Render environment variable `SANCTUM_STATEFUL_DOMAINS`

### Issue: 500 Server Error on Render
**Solutions**:
- Check Render logs for specific error
- Ensure `APP_KEY` is set correctly
- Verify database connection details
- Make sure `render-build.sh` has correct permissions

### Issue: Database Connection Failed
**Solutions**:
- Verify database credentials in Render environment variables
- Check if database server allows external connections
- Test connection string manually

### Issue: Frontend Can't Connect to API
**Solutions**:
- Verify `VITE_API_URL` in Vercel environment variables
- Check if API is returning proper CORS headers
- Test API endpoints directly in browser/Postman

## 📊 Costs Summary

- **Vercel Frontend**: FREE (personal use)
- **Render Backend**: FREE tier (may sleep after 15 min of inactivity)  
- **PlanetScale Database**: FREE tier (1GB storage)
- **Total**: $0/month for portfolio use! 🎉

## 🔄 Future Updates

To update your deployed application:
1. Make changes locally
2. Commit and push to GitHub
3. Both Vercel and Render will auto-deploy from GitHub pushes
4. Database changes may require manual migration

## 📞 Support

If you encounter issues:
1. Check the deployment logs in Vercel/Render dashboards
2. Test API endpoints with tools like Postman
3. Verify environment variables are set correctly
4. Check database connectivity

Your app should now be live and accessible worldwide! 🌍