<?php

namespace App\Models;

// OOP: Importing classes to extend Model functionality and enable features like factory pattern
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// OOP: Class inherits from Model to gain Eloquent ORM capabilities for database operations
class Ingredient extends Model
{
    // OOP: Trait provides factory pattern for testing and seeding
    use HasFactory;

    // OOP: Protected property defines which fields can be mass-assigned for security
    protected $fillable = [
        'recipe_id',
        'name',
        'measurement',
        'substitution_option',
        'allergen_info',
        'order',
    ];

    // OOP: Defines belongsTo relationship where an ingredient belongs to one recipe via recipe_id foreign key
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
