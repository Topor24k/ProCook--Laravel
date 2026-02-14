# ProCook Recipe Manager - Laravel CRUD Application

A Laravel-based recipe management system inspired by ProCook, where users can publish and share their own recipes.

## Features

### Recipe Management
- **Create Recipes**: Users can publish their own recipes with detailed information
- **View Recipes**: Browse all published recipes with beautiful card-based layout
- **Edit Recipes**: Recipe owners can update their recipes
- **Delete Recipes**: Recipe owners can remove their recipes
- **My Recipes**: Personal dashboard to manage your published recipes

### Recipe Information Structure

#### Core Information
- Recipe Title
- Short Description
- Cuisine Type (e.g., Italian, Chinese, Mexican)
- Category (e.g., Main Course, Dessert, Appetizer)

#### Ingredients
- Ingredients List with names
- Measurements for each ingredient
- Preparation Notes
- Substitution Options (optional alternatives)
- Allergen Information (dietary warnings)

#### Timing & Yield
- Prep Time (in minutes)
- Cook Time (in minutes)
- Total Time (automatically calculated)
- Serving Size

## Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- MySQL/MariaDB database
- Composer (PHP dependency manager)

### Step 1: Install Dependencies
```bash
composer install
```

### Step 2: Configure Environment
1. Copy `.env.example` to `.env`:
```bash
copy .env.example .env
```

2. Update database configuration in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=procook_recipes
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 3: Generate Application Key
```bash
php artisan key:generate
```

### Step 4: Create Database
Create a MySQL database named `procook_recipes` (or your chosen name from `.env`)

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Start Development Server
```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## Usage

### For Guests (Non-authenticated Users)
- Browse all published recipes
- View recipe details including ingredients and instructions
- Register for an account to publish recipes

### For Registered Users
- All guest features
- Create new recipes with full details
- Edit your own recipes
- Delete your own recipes
- Access "My Recipes" dashboard

## Routes

### Public Routes
- `GET /` - Homepage (All recipes)
- `GET /recipes` - All recipes listing
- `GET /recipes/{id}` - View single recipe details
- `GET /login` - Login page
- `GET /register` - Registration page

### Authenticated Routes
- `GET /recipes/create` - Create new recipe form
- `POST /recipes` - Store new recipe
- `GET /recipes/{id}/edit` - Edit recipe form
- `PUT /recipes/{id}` - Update recipe
- `DELETE /recipes/{id}` - Delete recipe
- `GET /recipes/my-recipes` - User's recipes dashboard
- `POST /logout` - Logout

## Database Structure

### Users Table
- id
- name
- email
- password
- timestamps

### Recipes Table
- id
- user_id (foreign key)
- title
- short_description
- cuisine_type
- category
- prep_time
- cook_time
- total_time
- serving_size
- preparation_notes
- timestamps

### Ingredients Table
- id
- recipe_id (foreign key)
- name
- measurement
- substitution_option
- allergen_info
- order
- timestamps

## Technology Stack

- **Framework**: Laravel 10.x
- **PHP**: 8.1+
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel's built-in authentication
- **Frontend**: Blade templates with custom CSS
- **Authorization**: Laravel Policies

## Security Features

- CSRF protection on all forms
- Password hashing using bcrypt
- Authorization checks for recipe editing/deletion
- Input validation on all forms
- SQL injection protection via Eloquent ORM

## Design Features

- Responsive grid layout for recipe cards
- Clean, modern UI inspired by ProCook
- Hover effects and smooth transitions
- Color-coded tags for cuisine types and categories
- Easy-to-read recipe details layout
- Dynamic ingredient management with add/remove functionality

## Future Enhancements

Potential features to add:
- Recipe images/photos
- Star ratings and reviews
- Recipe search and filtering
- Recipe categories with filtering
- Favorite/bookmark recipes
- Print-friendly recipe view
- Social sharing capabilities
- Recipe collections/cookbooks
- Nutritional information

## Contributing

Feel free to submit issues and enhancement requests!

## License

This project is open-source and available under the MIT License.

## Support

For questions or issues, please create an issue in the project repository.

---

**Enjoy cooking and sharing your recipes! 🍳👨‍🍳👩‍🍳**
