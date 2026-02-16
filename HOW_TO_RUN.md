# ProCook Recipe Manager — How to Run the System

## Prerequisites

Make sure you have the following installed on your machine:

| Tool        | Version   | Download Link                          |
|-------------|-----------|----------------------------------------|
| PHP         | >= 8.1    | https://windows.php.net/download       |
| Composer    | Latest    | https://getcomposer.org/download       |
| Node.js     | >= 18     | https://nodejs.org                     |
| npm         | >= 9      | (comes with Node.js)                   |
| MySQL       | >= 5.7    | https://dev.mysql.com/downloads        |

> **Tip:** You can use **XAMPP** or **Laragon** which bundle PHP + MySQL together.

---

## Step-by-Step Setup

### 1. Open the Project

Open a terminal (PowerShell) and navigate to the project folder:

```powershell
cd "C:\Users\Kayeen Campana\ProCook - Laravel"
```

---

### 2. Install PHP Dependencies

```powershell
composer install
```

This downloads all Laravel packages into the `vendor/` folder.

---

### 3. Install JavaScript Dependencies

```powershell
npm install
```

This downloads React, Vite, and other frontend packages into `node_modules/`.

---

### 4. Create the `.env` File

```powershell
copy .env.example .env
```

---

### 5. Generate Application Key

```powershell
php artisan key:generate
```

This sets the `APP_KEY` in your `.env` file (required for encryption).

---

### 6. Configure the Database

1. **Start MySQL** (via XAMPP, Laragon, or standalone MySQL service).

2. **Create a database** named `procook_recipes`:

   - Using **phpMyAdmin**: Go to http://localhost/phpmyadmin → click "New" → name it `procook_recipes` → click "Create".
   - Using **MySQL CLI**:
     ```sql
     CREATE DATABASE procook_recipes;
     ```

3. **Edit `.env`** and set your MySQL credentials:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=procook_recipes
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   > If you use XAMPP, the default username is `root` with an **empty** password.

---

### 7. Run Database Migrations

```powershell
php artisan migrate
```

This creates all required tables (users, recipes, ingredients, categories, products, comments, ratings, saved_recipes, etc.).

---

### 8. (Optional) Import the Backup Database

If you want pre-populated data from `procook_backup.sql`:

```powershell
mysql -u root -p procook_recipes < procook_backup.sql
```

Or import it via **phpMyAdmin** → select `procook_recipes` database → "Import" tab → choose `procook_backup.sql` → click "Go".

---

### 9. Create the Storage Symlink

```powershell
php artisan storage:link
```

This links `storage/app/public` to `public/storage` so uploaded images (e.g., recipe photos) are accessible from the browser.

---

### 10. Start the Application

You need **two terminals** running at the same time:

#### Terminal 1 — Laravel Backend (API Server)

```powershell
php artisan serve
```

This starts the backend API at **http://localhost:8000**.

#### Terminal 2 — Vite Dev Server (React Frontend)

```powershell
npm run dev
```

This starts the Vite dev server (usually at **http://localhost:5173**) which handles hot-reloading for the React frontend.

---

### 11. Open in Browser

Go to: **http://localhost:8000**

The Laravel app serves the React SPA. Vite compiles and hot-reloads the frontend in real-time.

---

## Quick Reference Commands

| Action                    | Command                              |
|---------------------------|--------------------------------------|
| Start backend server      | `php artisan serve`                  |
| Start frontend dev server | `npm run dev`                        |
| Run migrations            | `php artisan migrate`                |
| Reset & reseed database   | `php artisan migrate:fresh`          |
| Clear all caches          | `php artisan cache:clear; php artisan config:clear; php artisan view:clear` |
| Build for production      | `npm run build`                      |
| Run Laravel Tinker (REPL) | `php artisan tinker`                 |

---

## API Endpoints (for reference)

### Public (no login required)
| Method | Endpoint                             | Description            |
|--------|--------------------------------------|------------------------|
| GET    | `/api/recipes`                       | List all recipes       |
| GET    | `/api/recipes/{id}`                  | View a single recipe   |
| GET    | `/api/recipes/{id}/comments`         | View recipe comments   |
| POST   | `/api/register`                      | Register a new user    |
| POST   | `/api/login`                         | Log in                 |

### Authenticated (login required)
| Method | Endpoint                             | Description            |
|--------|--------------------------------------|------------------------|
| GET    | `/api/user`                          | Get current user       |
| POST   | `/api/logout`                        | Log out                |
| POST   | `/api/recipes`                       | Create a recipe        |
| PUT    | `/api/recipes/{id}`                  | Update a recipe        |
| DELETE | `/api/recipes/{id}`                  | Delete a recipe        |
| GET    | `/api/my-recipes`                    | List user's recipes    |
| POST   | `/api/recipes/{id}/comments`         | Add a comment          |
| POST   | `/api/recipes/{id}/rating`           | Rate a recipe          |
| POST   | `/api/recipes/{id}/save`             | Save/unsave a recipe   |
| GET    | `/api/saved-recipes`                 | List saved recipes     |

---

## Troubleshooting

### "No application encryption key has been specified"
```powershell
php artisan key:generate
```

### Database connection refused
- Make sure MySQL is **running**.
- Verify credentials in `.env` match your MySQL setup.
- Confirm the database `procook_recipes` **exists**.

### Vite manifest not found (500 error)
Run the Vite dev server or build assets:
```powershell
npm run dev    # for development
npm run build  # for production
```

### Permission errors on `storage/` or `bootstrap/cache/`
```powershell
icacls storage /grant Everyone:(OI)(CI)F /T
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T
```

### Port 8000 already in use
```powershell
php artisan serve --port=8080
```

---

## Summary — Minimum Steps to Run

```powershell
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
copy .env.example .env
php artisan key:generate

# 3. Create database "procook_recipes" in MySQL, then:
php artisan migrate

# 4. Link storage
php artisan storage:link

# 5. Run (two terminals)
php artisan serve        # Terminal 1
npm run dev              # Terminal 2

# 6. Open http://localhost:8000
```
