<?php

namespace App\Models;

// OOP: Importing classes to extend Model functionality and enable features like factory pattern
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// OOP: Class inherits from Model to gain Eloquent ORM capabilities for database operations
class Comment extends Model
{
    // OOP: Trait provides factory pattern for testing and seeding
    use HasFactory;

    // OOP: Protected property defines which fields can be mass-assigned for security
    protected $fillable = [
        'recipe_id',
        'user_id',
        'parent_id',
        'comment',
    ];

    // OOP: Defines belongsTo relationship where a comment belongs to one recipe via recipe_id foreign key
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    // OOP: Defines belongsTo relationship where a comment belongs to one user via user_id foreign key
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // OOP: Self-referencing belongsTo relationship where a comment can have a parent comment via parent_id
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // OOP: Self-referencing hasMany relationship where a comment can have multiple reply comments via parent_id
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user:id,name')->orderBy('created_at', 'asc');
    }
}
