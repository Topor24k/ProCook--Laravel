<?php

namespace App\Models;

// OOP: Importing classes to extend Model functionality and enable features like factory pattern
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// OOP: Class inherits from Model to gain Eloquent ORM capabilities for database operations
class Recipe extends Model
{
    // OOP: Trait provides factory pattern for testing and seeding
    use HasFactory;

    // OOP: Protected property defines which fields can be mass-assigned for security
    protected $fillable = [
        'user_id',
        'title',
        'short_description',
        'image',
        'cuisine_type',
        'category',
        'prep_time',
        'cook_time',
        'total_time',
        'serving_size',
        'preparation_notes',
    ];

    // OOP: Defines belongsTo relationship where a recipe belongs to one user via user_id foreign key
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // OOP: Defines hasMany relationship where a recipe can have multiple ingredients via recipe_id foreign key
    public function ingredients()
    {
        return $this->hasMany(Ingredient::class)->orderBy('order');
    }

    // OOP: Defines hasMany relationship where a recipe can have multiple comments via recipe_id foreign key
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    // OOP: Defines hasMany relationship where a recipe can have multiple ratings via recipe_id foreign key
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // OOP: Defines belongsToMany relationship where recipes can be saved by multiple users via saved_recipes pivot table
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_recipes')
            ->withTimestamps();
    }

    // OOP: Method calculates average rating from related ratings using aggregation
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    // OOP: Method counts total number of ratings from related ratings using aggregation
    public function ratingsCount()
    {
        return $this->ratings()->count();
    }
}
