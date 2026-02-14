# ProCook Recipe Manager - Quick Start Guide

## 🚀 Getting Started

Your Laravel Recipe CRUD application has been created! Follow these steps to get it running:

### Step 1: Install Composer Dependencies
First, you need to install PHP dependencies using Composer. If you don't have Composer installed, download it from https://getcomposer.org/

```powershell
composer install
```

### Step 2: Configure Your Database
1. Create a MySQL database named `procook_recipes`
2. Update the database credentials in `.env` file if needed:
   - DB_USERNAME=your_mysql_username
   - DB_PASSWORD=your_mysql_password

### Step 3: Generate Application Key
```powershell
php artisan key:generate
```

### Step 4: Run Migrations
This will create all the necessary database tables:
```powershell
php artisan migrate
```

### Step 5: Seed Sample Data (Optional)
To populate your database with sample recipes and users:
```powershell
php artisan db:seed
```

**Sample Login Credentials (after seeding):**
- Email: demo@procook.com
- Password: password123

### Step 6: Start the Development Server
```powershell
php artisan serve
```

Your application will be available at: **http://localhost:8000**

## 📋 Features Included

### ✅ Recipe Management
- Create, Read, Update, Delete recipes
- Rich recipe information:
  - Title, Description, Cuisine Type, Category
  - Ingredients with measurements
  - Substitution options
  - Allergen information
  - Prep time, cook time, serving size
  - Detailed preparation instructions

### ✅ User Authentication
- User registration
- Login/Logout
- Password protection
- Authorization (only recipe owners can edit/delete)

### ✅ User Dashboard
- "My Recipes" page to manage your recipes
- View all published recipes
- Beautiful, responsive design

## 🎨 Design Features
- ProCook-inspired clean interface
- Responsive grid layout
- Color-coded cuisine and category tags
- Smooth hover effects
- Mobile-friendly design

## 📁 Project Structure

```
ProCook - Laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── RecipeController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Recipe.php
│   │   └── Ingredient.php
│   └── Policies/
│       └── RecipePolicy.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_recipes_table.php
│   │   └── create_ingredients_table.php
│   └── seeders/
│       ├── UserSeeder.php
│       └── RecipeSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── recipes/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── my-recipes.blade.php
│       └── auth/
│           ├── login.blade.php
│           └── register.blade.php
└── routes/
    └── web.php
```

## 🔧 Common Commands

### Clear Cache
```powershell
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Reset Database
```powershell
php artisan migrate:fresh --seed
```

### Create New Migration
```powershell
php artisan make:migration create_table_name
```

### Create New Model
```powershell
php artisan make:model ModelName -m
```

## 🐛 Troubleshooting

### Error: "No application encryption key has been specified"
Run: `php artisan key:generate`

### Database Connection Error
- Check your database credentials in `.env`
- Make sure MySQL is running
- Verify the database exists

### Permission Errors (Storage/Bootstrap)
```powershell
# Windows
icacls storage /grant Everyone:(OI)(CI)F /T
icacls bootstrap/cache /grant Everyone:(OI)(CI)F /T
```

## 📚 Next Steps

### Enhancements You Can Add:
1. **Recipe Images** - Add image upload functionality
2. **Search & Filter** - Add search by cuisine, category, or ingredients
3. **Ratings & Reviews** - Let users rate and review recipes
4. **Favorites** - Allow users to bookmark favorite recipes
5. **Print View** - Add a print-friendly recipe format
6. **Social Sharing** - Add share buttons for social media
7. **Recipe Collections** - Create recipe books or collections
8. **Advanced Search** - Filter by cooking time, serving size, etc.

## 💡 Tips
- The layout file is at `resources/views/layouts/app.blade.php` - customize the design here
- All routes are defined in `routes/web.php`
- Recipe validation happens in `RecipeController.php`
- Authorization is handled by `RecipePolicy.php`

## 🎓 Learning Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Blade Templates](https://laravel.com/docs/blade)
- [Eloquent ORM](https://laravel.com/docs/eloquent)

---

**Happy Cooking! 🍳👨‍🍳👩‍🍳**

Need help? Check the README.md for more detailed information.
