<?php

namespace App\Models;

// OOP: Importing classes to extend authentication functionality and enable API tokens, notifications, and factories
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// OOP: Class inherits from Authenticatable to gain user authentication and Eloquent ORM capabilities
class User extends Authenticatable
{
    // OOP: Traits provide API token auth, factory pattern, and notification capabilities
    use HasApiTokens, HasFactory, Notifiable;

    // OOP: Protected property defines which fields can be mass-assigned for security
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // OOP: Protected property hides sensitive fields from JSON serialization for security
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // OOP: Protected property defines automatic type casting for attributes
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // OOP: Defines hasMany relationship where a user can create multiple recipes via user_id foreign key
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    // OOP: Defines hasMany relationship where a user can write multiple comments via user_id foreign key
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // OOP: Defines hasMany relationship where a user can give multiple ratings via user_id foreign key
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // OOP: Defines belongsToMany relationship where users can save multiple recipes via saved_recipes pivot table
    public function savedRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'saved_recipes')
            ->withTimestamps()
            ->orderBy('saved_recipes.created_at', 'desc');
    }
}
