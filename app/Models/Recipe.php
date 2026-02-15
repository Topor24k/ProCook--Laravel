<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get the user that owns the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ingredients for the recipe.
     */
    public function ingredients()
    {
        return $this->hasMany(Ingredient::class)->orderBy('order');
    }

    /**
     * Get the comments for the recipe.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the ratings for the recipe.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get users who saved this recipe.
     */
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_recipes')
            ->withTimestamps();
    }

    /**
     * Get the average rating for the recipe.
     */
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    /**
     * Get the total number of ratings.
     */
    public function ratingsCount()
    {
        return $this->ratings()->count();
    }
}
