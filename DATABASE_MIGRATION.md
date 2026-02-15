# Database Export and Migration Guide

## 1. Export from HeidiSQL/Laragon MySQL

### Option A: Using HeidiSQL (Recommended)
1. Open HeidiSQL
2. Connect to your local MySQL server
3. Select your `procook` database
4. Right-click on database → Export database as SQL
5. Choose settings:
   - Export data: YES
   - Export structure: YES
   - Export routines: NO (unless you have stored procedures)
   - Export triggers: NO
   - Export events: NO
6. Save as `procook_backup.sql`

### Option B: Using Command Line
```bash
# Navigate to MySQL bin directory (in Laragon)
cd C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\

# Export database
mysqldump -u root -p procook > procook_backup.sql
```

## 2. Cloud Database Options

### Option A: PlanetScale (Recommended - MySQL compatible)
- Free tier: 1 database, 1GB storage
- Serverless MySQL platform
- Easy scaling
- Built-in branching

### Option B: Render PostgreSQL (Free)
- 90 day limit on free tier
- 1GB storage
- Need to convert MySQL to PostgreSQL

### Option C: Railway MySQL 
- $5/month after free trial
- MySQL compatible

## 3. Data Migration Steps

### For PlanetScale (MySQL):
1. Create PlanetScale account
2. Create database
3. Get connection string
4. Import your .sql file via their dashboard or CLI

### For Render PostgreSQL:
1. Create Render account  
2. Create PostgreSQL database
3. Convert MySQL dump to PostgreSQL format
4. Import via psql command

## 4. Update Laravel Configuration

Update your `.env.render` file with the new database credentials:
```
DB_CONNECTION=mysql  # or pgsql for PostgreSQL
DB_HOST=your-db-host
DB_PORT=your-db-port  
DB_DATABASE=your-db-name
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password
```