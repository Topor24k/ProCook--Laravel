# ProCook Laravel — OOP & CRUD Study Guide

This document lists **every Laravel PHP file** in the project, then provides a deep-dive README (with full code) for each file that demonstrates **OOP** (classes, inheritance, traits, relationships, polymorphism, encapsulation) and/or **CRUD** (Create, Read, Update, Delete) operations.

---

## 1. Complete List of Laravel PHP Files

### Core
| File | Purpose |
|------|---------|
| `artisan` | Laravel CLI entry point |
| `bootstrap/app.php` | Application bootstrap |
| `public/index.php` | Web entry point |

### Config (`config/`)
| File | Purpose |
|------|---------|
| `config/app.php` | Application config |
| `config/auth.php` | Authentication guards |
| `config/cache.php` | Cache drivers |
| `config/cors.php` | CORS policy |
| `config/database.php` | DB connections |
| `config/filesystems.php` | Filesystem disks |
| `config/logging.php` | Log channels |
| `config/sanctum.php` | API token auth |
| `config/session.php` | Session config |
| `config/view.php` | View paths |

### App — Models (`app/Models/`)  ⭐ OOP
| File | OOP Concepts |
|------|-------------|
| `User.php` | Inheritance, Traits, Relationships (hasMany, belongsToMany) |
| `Recipe.php` | Inheritance, Trait, Relationships, Custom methods |
| `Comment.php` | Inheritance, Trait, Self-referencing relationship (replies) |
| `Ingredient.php` | Inheritance, Trait, belongsTo relationship |
| `Rating.php` | Inheritance, Trait, belongsTo relationships |
| `Category.php` | Inheritance, Trait, hasMany relationship |
| `Product.php` | Inheritance, Trait, Accessors (computed properties), Type casting |

### App — Controllers (`app/Http/Controllers/`)  ⭐ CRUD + OOP
| File | CRUD Operations |
|------|----------------|
| `Api/RecipeController.php` | **Full CRUD** — index, show, store, update, destroy + myRecipes |
| `Api/CommentController.php` | **Full CRUD** — index, store, update, destroy |
| `Api/RatingController.php` | **CRD** — store (create/update), show, destroy |
| `Api/SavedRecipeController.php` | **CRD** — index, store, destroy, toggle, check |
| `Api/AuthController.php` | **CR** — register (Create), login, logout, user (Read) |
| `Controller.php` | Base controller class (OOP inheritance) |
| `HomeController.php` | View rendering |
| `RecipeController.php` | Web view rendering |
| `ProductController.php` | Web view rendering |

### App — Form Requests (`app/Http/Requests/`)  ⭐ OOP
| File | Purpose |
|------|---------|
| `StoreRecipeRequest.php` | Validation class for recipe create/update |
| `LoginRequest.php` | Validation class for login |
| `RegisterRequest.php` | Validation class for registration |

### App — Policy (`app/Policies/`)  ⭐ OOP
| File | Purpose |
|------|---------|
| `RecipePolicy.php` | Authorization — who can update/delete a recipe |

### App — Mail (`app/Mail/`)  ⭐ OOP
| File | Purpose |
|------|---------|
| `WelcomeEmail.php` | Mailable class for welcome emails |

### App — Middleware (`app/Http/Middleware/`)  ⭐ OOP
| File | Purpose |
|------|---------|
| `Authenticate.php` | Auth redirect |
| `SecurityHeaders.php` | Custom security headers |
| `ThrottleApiRequests.php` | Rate limiting |
| + 6 more default Laravel middleware | Standard pipeline |

### App — Providers (`app/Providers/`)  ⭐ OOP
| File | Purpose |
|------|---------|
| `AppServiceProvider.php` | App bootstrapping |
| `AuthServiceProvider.php` | Policy registration |
| `RouteServiceProvider.php` | Route loading |

### Routes
| File | Purpose |
|------|---------|
| `routes/api.php` | All API endpoints |
| `routes/web.php` | Web page routes |
| `routes/console.php` | Artisan commands |

### Database — Migrations (`database/migrations/`)
| File | Purpose |
|------|---------|
| 11 migration files | Schema creation (users, recipes, ingredients, etc.) |

### Database — Seeders
| File | Purpose |
|------|---------|
| `DatabaseSeeder.php` | Test data seeding |

---

## 2. Detailed README For Each File With OOP & CRUD

---

### 📁 FILE: `app/Models/User.php`
**OOP Concepts:** Inheritance, Traits, Encapsulation, Relationships

```php
<?php

namespace App\Models;
    
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function savedRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'saved_recipes')
            ->withTimestamps()
            ->orderBy('saved_recipes.created_at', 'desc');
    }
}
```

#### Study Notes:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Inheritance** | `extends Authenticatable` | `User` inherits from Laravel's `Authenticatable` class (which itself extends `Model`). This gives User all Eloquent ORM abilities plus authentication features. |
| **Traits** | `use HasApiTokens, HasFactory, Notifiable` | Traits are reusable blocks of code. `HasApiTokens` adds token auth, `HasFactory` adds test factories, `Notifiable` adds notification support. |
| **Encapsulation** | `$fillable`, `$hidden`, `$casts` | `$fillable` controls which fields can be mass-assigned (protection against mass-assignment attacks). `$hidden` hides sensitive data during serialization. `$casts` auto-converts attribute types. |
| **One-to-Many** | `hasMany(Recipe::class)` | A user can have **many** recipes, comments, ratings. |
| **Many-to-Many** | `belongsToMany(Recipe::class, 'saved_recipes')` | Uses a **pivot table** (`saved_recipes`) to create a many-to-many relationship — a user can save many recipes; a recipe can be saved by many users. |

---

### 📁 FILE: `app/Models/Recipe.php`
**OOP Concepts:** Inheritance, Trait, Relationships, Custom Methods

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'short_description', 'image',
        'cuisine_type', 'category', 'prep_time', 'cook_time',
        'total_time', 'serving_size', 'preparation_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class)->orderBy('order');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_recipes')
            ->withTimestamps();
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function ratingsCount()
    {
        return $this->ratings()->count();
    }
}
```

#### Study Notes:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Inheritance** | `extends Model` | Inherits all Eloquent ORM features (query building, CRUD, timestamps, etc.) |
| **Inverse Relationship** | `belongsTo(User::class)` | Each recipe **belongs to** one user — the inverse of `User->hasMany(Recipe)` |
| **One-to-Many** | `hasMany(Ingredient::class)` | A recipe has many ingredients, comments, and ratings |
| **Many-to-Many (inverse)** | `belongsToMany(User::class, 'saved_recipes')` | Inverse of the User's `savedRecipes()` |
| **Custom Methods** | `averageRating()`, `ratingsCount()` | Business logic encapsulated inside the model — follows the "Fat Model, Thin Controller" principle |
| **Query Scoping** | `->orderBy('order')` | Chaining query constraints inside relationship definitions |

---

### 📁 FILE: `app/Models/Comment.php`
**OOP Concepts:** Self-referencing Relationship (Polymorphism-like), Inheritance

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id', 'user_id', 'parent_id', 'comment',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with('user:id,name')
            ->orderBy('created_at', 'asc');
    }
}
```

#### Study Notes:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Self-referencing Relationship** | `belongsTo(Comment::class, 'parent_id')` and `hasMany(Comment::class, 'parent_id')` | A comment can have a parent comment and child replies — this creates a **tree structure** using one table. This is a form of **recursive association**. |
| **Eager Loading** | `->with('user:id,name')` | Loads user data with replies to prevent N+1 query problems |
| **Encapsulation** | `$fillable` | Only the listed columns can be mass-assigned |

---

### 📁 FILE: `app/Models/Ingredient.php`
**OOP Concepts:** Inheritance, Relationship

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id', 'name', 'measurement',
        'substitution_option', 'allergen_info', 'order',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
```

#### Study Notes:
- Simple model demonstrating **inheritance** (`extends Model`) and a **belongsTo** inverse relationship.
- The `order` field is used for sorting ingredients in a recipe.

---

### 📁 FILE: `app/Models/Rating.php`
**OOP Concepts:** Inheritance, Multiple Relationships

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id', 'user_id', 'rating',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

#### Study Notes:
- Acts as an **association/junction model** between User and Recipe.
- Two `belongsTo` relationships: every rating **belongs to** exactly 1 user and 1 recipe.

---

### 📁 FILE: `app/Models/Product.php`
**OOP Concepts:** Inheritance, Accessors (Computed Properties), Type Casting

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'features',
        'price', 'sale_price', 'sku', 'stock', 'image',
        'is_featured', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }
}
```

#### Study Notes:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Accessors** | `getDiscountPercentageAttribute()`, `getCurrentPriceAttribute()` | Laravel convention: `get{Name}Attribute()` creates a virtual/computed property. Access via `$product->discount_percentage`. This is **encapsulation** — the calculation logic lives inside the model. |
| **Type Casting** | `$casts` | Auto-converts DB values to PHP types (`decimal:2` for money, `boolean` for flags). This is an OOP pattern of ensuring data integrity. |

---

### 📁 FILE: `app/Models/Category.php`
**OOP Concepts:** Inheritance, Relationship

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
```

#### Study Notes:
- One-to-Many: A category **has many** products.

---

### 📁 FILE: `app/Http/Controllers/Api/RecipeController.php`
**OOP Concepts:** Inheritance, Dependency Injection | **CRUD:** ✅ Create ✅ Read ✅ Update ✅ Delete

This is the **most important file for CRUD study**. It contains all 4 CRUD operations.

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeRequest;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    // ==========================================
    // READ (List) — GET /api/recipes
    // ==========================================
    public function index(Request $request)
    {
        try {
            $query = Recipe::with('user')
                ->withCount('ratings')
                ->withAvg('ratings', 'rating');

            if ($request->has('limit')) {
                $limit = min((int)$request->limit, 100);
                $query->limit($limit);
            }

            $recipes = $query->latest()->get();

            $recipes = $recipes->map(function ($recipe) {
                $recipe->average_rating = round($recipe->ratings_avg_rating ?? 0, 1);
                $recipe->ratings_count = $recipe->ratings_count ?? 0;
                unset($recipe->ratings_avg_rating);
                return $recipe;
            });

            return response()->json([
                'success' => true,
                'data' => $recipes,
                'count' => $recipes->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching recipes', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipes.'
            ], 500);
        }
    }

    // ==========================================
    // READ (Single) — GET /api/recipes/{id}
    // ==========================================
    public function show($id)
    {
        try {
            $recipe = Recipe::with(['user', 'ingredients'])
                ->withCount('ratings')
                ->withAvg('ratings', 'rating')
                ->findOrFail($id);

            $recipe->average_rating = round($recipe->ratings_avg_rating ?? 0, 1);
            $recipe->ratings_count = $recipe->ratings_count ?? 0;
            unset($recipe->ratings_avg_rating);

            return response()->json([
                'success' => true,
                'data' => $recipe
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching recipe', [
                'recipe_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipe details.'
            ], 500);
        }
    }

    // ==========================================
    // CREATE — POST /api/recipes
    // ==========================================
    public function store(StoreRecipeRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('recipes', $imageName, 'public');
                $validated['image'] = $imagePath;
            }

            $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];
            $validated['user_id'] = Auth::id();

            $recipe = Recipe::create($validated);

            // Create related ingredients
            if (isset($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $ingredient) {
                    $recipe->ingredients()->create($ingredient);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recipe created successfully!',
                'data' => $recipe->load('ingredients')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating recipe', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create recipe. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // ==========================================
    // UPDATE — PUT /api/recipes/{id}
    // ==========================================
    public function update(StoreRecipeRequest $request, $id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            if ($recipe->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this recipe.'
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();

            if ($request->hasFile('image')) {
                if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                    Storage::disk('public')->delete($recipe->image);
                }
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('recipes', $imageName, 'public');
                $validated['image'] = $imagePath;
            }

            $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];

            $recipe->update($validated);

            // Replace ingredients
            $recipe->ingredients()->delete();
            if (isset($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $ingredient) {
                    $recipe->ingredients()->create($ingredient);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully!',
                'data' => $recipe->load('ingredients')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating recipe', [
                'recipe_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recipe. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // ==========================================
    // DELETE — DELETE /api/recipes/{id}
    // ==========================================
    public function destroy($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            if ($recipe->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this recipe.'
                ], 403);
            }

            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }

            $recipe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting recipe', [
                'recipe_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recipe. Please try again.'
            ], 500);
        }
    }

    // ==========================================
    // READ (My Recipes) — GET /api/my-recipes
    // ==========================================
    public function myRecipes()
    {
        try {
            $recipes = Recipe::where('user_id', Auth::id())
                ->with('ingredients')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $recipes,
                'count' => $recipes->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching user recipes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your recipes.'
            ], 500);
        }
    }
}
```

#### Study Notes — CRUD Mapping:
| CRUD | Method | HTTP | Route | Eloquent Method |
|------|--------|------|-------|-----------------|
| **Create** | `store()` | POST | `/api/recipes` | `Recipe::create()`, `$recipe->ingredients()->create()` |
| **Read (all)** | `index()` | GET | `/api/recipes` | `Recipe::with()->get()` |
| **Read (one)** | `show()` | GET | `/api/recipes/{id}` | `Recipe::findOrFail($id)` |
| **Update** | `update()` | PUT | `/api/recipes/{id}` | `$recipe->update()` |
| **Delete** | `destroy()` | DELETE | `/api/recipes/{id}` | `$recipe->delete()` |

#### Key OOP Concepts in This Controller:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Inheritance** | `extends Controller` | Inherits base controller functionality |
| **Dependency Injection** | `StoreRecipeRequest $request` | Laravel auto-injects the validated request object — a key OOP/DI pattern |
| **Database Transactions** | `DB::beginTransaction()` / `commit()` / `rollBack()` | Ensures atomicity — if ingredients fail, the recipe isn't saved either |
| **Authorization** | `$recipe->user_id !== Auth::id()` | Ownership check before update/delete |
| **Facade Pattern** | `Auth::id()`, `DB::beginTransaction()`, `Storage::disk()` | Static-looking calls that resolve from the IoC container — a Laravel design pattern |

---

### 📁 FILE: `app/Http/Controllers/Api/CommentController.php`
**CRUD:** ✅ Create ✅ Read ✅ Update ✅ Delete

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // READ — GET /api/recipes/{recipe}/comments
    public function index($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);
            
            $comments = Comment::with(['user:id,name', 'replies.user:id,name'])
                ->where('recipe_id', $recipeId)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
                'count' => $comments->count()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching comments', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments.'
            ], 500);
        }
    }

    // CREATE — POST /api/recipes/{recipe}/comments
    public function store(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000',
                'parent_id' => 'nullable|exists:comments,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->parent_id) {
                $parentComment = Comment::where('id', $request->parent_id)
                    ->where('recipe_id', $recipeId)
                    ->first();
                    
                if (!$parentComment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent comment.'
                    ], 422);
                }
            }

            $comment = Comment::create([
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'comment' => $request->comment
            ]);

            $comment->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully.',
                'data' => $comment
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error creating comment', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment.'
            ], 500);
        }
    }

    // UPDATE — PUT /api/recipes/{recipe}/comments/{comment}
    public function update(Request $request, $recipeId, $commentId)
    {
        try {
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this comment.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment->update([
                'comment' => $request->comment
            ]);

            $comment->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully.',
                'data' => $comment
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment.'
            ], 500);
        }
    }

    // DELETE — DELETE /api/recipes/{recipe}/comments/{comment}
    public function destroy($recipeId, $commentId)
    {
        try {
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this comment.'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment.'
            ], 500);
        }
    }
}
```

#### Study Notes — CRUD Mapping:
| CRUD | Method | Eloquent |
|------|--------|----------|
| **Create** | `store()` | `Comment::create([...])` |
| **Read** | `index()` | `Comment::with([...])->where()->get()` |
| **Update** | `update()` | `$comment->update([...])` |
| **Delete** | `destroy()` | `$comment->delete()` |

#### Key Concepts:
- **Nested Resource:** Comments are nested under recipes (`/recipes/{recipe}/comments`)
- **Validator Facade:** Uses `Validator::make()` for inline validation instead of a FormRequest class
- **Authorization:** Checks `$comment->user_id !== Auth::id()` before update/delete

---

### 📁 FILE: `app/Http/Controllers/Api/RatingController.php`
**CRUD:** ✅ Create/Update ✅ Read ✅ Delete

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    // CREATE or UPDATE — POST /api/recipes/{recipe}/rating
    public function store(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            if ($recipe->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot rate your own recipe.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // UPSERT pattern: update if exists, create if not
            $rating = Rating::updateOrCreate(
                [
                    'recipe_id' => $recipeId,
                    'user_id' => Auth::id()
                ],
                [
                    'rating' => $request->rating
                ]
            );

            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully.',
                'data' => [
                    'rating' => $rating,
                    'averageRating' => round($averageRating, 1),
                    'ratingsCount' => $ratingsCount
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error submitting rating', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating.'
            ], 500);
        }
    }

    // READ — GET /api/recipes/{recipe}/rating
    public function show($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $userRating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'userRating' => $userRating ? $userRating->rating : null,
                    'averageRating' => round($averageRating ?? 0, 1),
                    'ratingsCount' => $ratingsCount
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    // READ (Public) — GET /api/recipes/{recipe}/rating/public
    public function showPublic($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'averageRating' => round($averageRating ?? 0, 1),
                    'ratingsCount' => $ratingsCount,
                    'recipeOwnerId' => $recipe->user_id
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching public rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    // DELETE — DELETE /api/recipes/{recipe}/rating
    public function destroy($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $rating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found.'
                ], 404);
            }

            $rating->delete();

            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'message' => 'Rating removed successfully.',
                'data' => [
                    'averageRating' => round($averageRating, 1),
                    'ratingsCount' => $ratingsCount
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rating.'
            ], 500);
        }
    }
}
```

#### Key Concept — `updateOrCreate()` (Upsert):
```php
Rating::updateOrCreate(
    ['recipe_id' => $recipeId, 'user_id' => Auth::id()],  // Search criteria
    ['rating' => $request->rating]                          // Values to set
);
```
This is a powerful Eloquent method: if a rating already exists for this user+recipe, it **updates** it. If not, it **creates** a new one. One method handles both Create and Update.

---

### 📁 FILE: `app/Http/Controllers/Api/SavedRecipeController.php`
**CRUD:** ✅ Create (save) ✅ Read (list/check) ✅ Delete (unsave)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedRecipeController extends Controller
{
    // READ — GET /api/saved-recipes
    public function index()
    {
        try {
            $savedRecipes = Auth::user()
                ->savedRecipes()
                ->with('user:id,name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $savedRecipes,
                'count' => $savedRecipes->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching saved recipes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch saved recipes.'
            ], 500);
        }
    }

    // READ — GET /api/recipes/{recipe}/saved
    public function check($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $isSaved = Auth::user()
                ->savedRecipes()
                ->where('recipe_id', $recipeId)
                ->exists();

            return response()->json([
                'success' => true,
                'data' => ['isSaved' => $isSaved]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error checking saved recipe', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check saved status.'
            ], 500);
        }
    }

    // CREATE — POST /api/recipes/{recipe}/save  (saves a recipe)
    public function store($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $alreadySaved = Auth::user()
                ->savedRecipes()
                ->where('recipe_id', $recipeId)
                ->exists();

            if ($alreadySaved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe already saved.'
                ], 409);
            }

            Auth::user()->savedRecipes()->attach($recipeId);

            return response()->json([
                'success' => true,
                'message' => 'Recipe saved successfully.',
                'data' => ['isSaved' => true]
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error saving recipe', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save recipe.'
            ], 500);
        }
    }

    // DELETE — unsave a recipe
    public function destroy($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $removed = Auth::user()->savedRecipes()->detach($recipeId);

            if (!$removed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe was not saved.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recipe unsaved successfully.',
                'data' => ['isSaved' => false]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error unsaving recipe', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsave recipe.'
            ], 500);
        }
    }

    // TOGGLE — POST /api/recipes/{recipe}/save
    public function toggle($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $isSaved = Auth::user()
                ->savedRecipes()
                ->where('recipe_id', $recipeId)
                ->exists();

            if ($isSaved) {
                Auth::user()->savedRecipes()->detach($recipeId);
                $message = 'Recipe unsaved successfully.';
                $newStatus = false;
            } else {
                Auth::user()->savedRecipes()->attach($recipeId);
                $message = 'Recipe saved successfully.';
                $newStatus = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => ['isSaved' => $newStatus]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error toggling saved recipe', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle saved status.'
            ], 500);
        }
    }
}
```

#### Key Concept — Pivot Table Operations:
| Method | What it does |
|--------|-------------|
| `->attach($id)` | Inserts a row into the `saved_recipes` pivot table |
| `->detach($id)` | Removes a row from the pivot table |
| `->exists()` | Checks if a relationship exists in the pivot table |

---

### 📁 FILE: `app/Http/Controllers/Api/AuthController.php`
**OOP Concepts:** Inheritance, Facade Pattern | **CRUD:** ✅ Create (Register) ✅ Read (User)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // CREATE user — POST /api/register
    public function register(RegisterRequest $request)
    {
        try {
            $key = 'register:' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            RateLimiter::hit($key, 3600);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to ProCook.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // READ/Authenticate user — POST /api/login
    public function login(LoginRequest $request)
    {
        try {
            $key = 'login:' . $request->email . ':' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please wait before trying again.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                RateLimiter::hit($key, 60);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Please check your email and password.',
                    'errors' => ['email' => ['The provided credentials are incorrect.']]
                ], 401);
            }

            RateLimiter::clear($key);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login successful! Welcome back.',
                'user' => Auth::user()
            ]);
        } catch (\Exception $e) {
            \Log::error('Login error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Destroy session — POST /api/logout
    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.'
            ], 500);
        }
    }

    // READ current user — GET /api/user
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching user', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user information.'
            ], 500);
        }
    }
}
```

#### Key Concepts:
| Concept | Where | Explanation |
|---------|-------|-------------|
| **Rate Limiting** | `RateLimiter::tooManyAttempts()` | Prevents brute-force attacks (OOP: `RateLimiter` is a class with static methods via Facade) |
| **Password Hashing** | `Hash::make($request->password)` | Never stores plain-text passwords — using the `Hash` facade |
| **Session Management** | `$request->session()->regenerate()` | Security pattern: regenerate session ID after login to prevent session fixation |

---

### 📁 FILE: `app/Http/Requests/StoreRecipeRequest.php`
**OOP Concepts:** Inheritance, Method Overriding, Encapsulation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();  // Only authenticated users
    }

    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'min:3', 'max:255'],
            'short_description'  => ['required', 'string', 'min:10', 'max:500'],
            'image'              => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'cuisine_type'       => ['required', 'string', 'max:100'],
            'category'           => ['required', 'string', 'max:100'],
            'prep_time'          => ['required', 'integer', 'min:1', 'max:1440'],
            'cook_time'          => ['required', 'integer', 'min:0', 'max:1440'],
            'serving_size'       => ['required', 'integer', 'min:1', 'max:100'],
            'preparation_notes'  => ['nullable', 'string', 'min:20', 'max:10000'],
            'ingredients'        => ['required', 'array', 'min:1', 'max:50'],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.measurement'          => ['required', 'string', 'max:50'],
            'ingredients.*.substitution_option'  => ['nullable', 'string', 'max:255'],
            'ingredients.*.allergen_info'        => ['nullable', 'string', 'max:100'],
            'ingredients.*.order'                => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```

#### Study Notes:
| Concept | Explanation |
|---------|-------------|
| **Inheritance** | `extends FormRequest` — inherits from Laravel's base Form Request, which handles validation automatically |
| **Method Overriding** | `authorize()` and `rules()` **override** the parent class methods to define custom behavior |
| **Encapsulation** | The validation logic is encapsulated in its own class, separated from the controller — **Single Responsibility Principle** |
| **Nested Validation** | `ingredients.*.name` validates each item inside the ingredients array — dot notation for nested data |

---

### 📁 FILE: `app/Policies/RecipePolicy.php`
**OOP Concepts:** Authorization as a class, Method-based permissions

```php
<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }
}
```

#### Study Notes:
- **Policy Pattern:** Encapsulates authorization rules into a dedicated class. Instead of checking permissions inline in controllers, you call `$this->authorize('update', $recipe)`.
- **Type Hinting:** `User $user, Recipe $recipe` — PHP enforces that the correct types are passed.
- **Return Type:** `: bool` — explicitly states the method returns true/false.

---

### 📁 FILE: `routes/api.php`
**How routes wire CRUD to controllers:**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SavedRecipeController;

// Public routes (no auth required)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/recipes', [RecipeController::class, 'index']);              // READ all
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);      // READ one
    Route::get('/recipes/{recipe}/rating/public', [RatingController::class, 'showPublic']);
    Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']);
});

// Auth routes
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);           // CREATE user
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (must be logged in)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Recipe CRUD
    Route::post('/recipes', [RecipeController::class, 'store']);             // CREATE
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);    // UPDATE
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);// DELETE
    Route::get('/my-recipes', [RecipeController::class, 'myRecipes']);       // READ (own)
    
    // Comment CRUD
    Route::post('/recipes/{recipe}/comments', [CommentController::class, 'store']);
    Route::put('/recipes/{recipe}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/recipes/{recipe}/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Rating CRD
    Route::get('/recipes/{recipe}/rating', [RatingController::class, 'show']);
    Route::post('/recipes/{recipe}/rating', [RatingController::class, 'store']);
    Route::delete('/recipes/{recipe}/rating', [RatingController::class, 'destroy']);
    
    // Saved Recipes
    Route::get('/saved-recipes', [SavedRecipeController::class, 'index']);
    Route::get('/recipes/{recipe}/saved', [SavedRecipeController::class, 'check']);
    Route::post('/recipes/{recipe}/save', [SavedRecipeController::class, 'toggle']);
});
```

#### Study Notes — RESTful HTTP Methods:
| HTTP Method | CRUD | Example |
|------------|------|---------|
| `GET` | **Read** | Fetch data (list, show) |
| `POST` | **Create** | Submit new data (store) |
| `PUT` | **Update** | Modify existing data |
| `DELETE` | **Delete** | Remove data |

---

## 3. Quick OOP Concepts Summary

| OOP Concept | Laravel Example | Files |
|------------|-----------------|-------|
| **Class** | Every PHP file is a class | All files |
| **Inheritance** | `extends Model`, `extends Controller`, `extends FormRequest` | Models, Controllers, Requests |
| **Traits** | `use HasFactory`, `use HasApiTokens`, `use Notifiable` | Models |
| **Encapsulation** | `$fillable`, `$hidden`, `$casts`, private validation rules | Models, Requests |
| **Relationships** | `hasMany()`, `belongsTo()`, `belongsToMany()` | Models |
| **Accessors** | `getDiscountPercentageAttribute()` | Product model |
| **Polymorphism** | Self-referencing `Comment->replies()` | Comment model |
| **Dependency Injection** | Controller methods receive typed `Request` objects | Controllers |
| **Facade Pattern** | `Auth::`, `DB::`, `Storage::`, `Mail::` | Controllers |
| **Policy Pattern** | `RecipePolicy` with `update()` and `delete()` | RecipePolicy |
| **Single Responsibility** | FormRequests handle validation, Policies handle auth, Controllers handle logic | All layers |

## 4. Quick CRUD Summary

| Resource | Create | Read | Update | Delete | Controller |
|----------|--------|------|--------|--------|-----------|
| **Recipe** | `store()` | `index()`, `show()`, `myRecipes()` | `update()` | `destroy()` | `Api/RecipeController` |
| **Comment** | `store()` | `index()` | `update()` | `destroy()` | `Api/CommentController` |
| **Rating** | `store()` | `show()`, `showPublic()` | `store()` (upsert) | `destroy()` | `Api/RatingController` |
| **Saved Recipe** | `store()`, `toggle()` | `index()`, `check()` | — | `destroy()`, `toggle()` | `Api/SavedRecipeController` |
| **User** | `register()` | `user()` | — | — | `Api/AuthController` |
