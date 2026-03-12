<?php

namespace App\Policies;

// OOP: Importing Model classes to use as type hints for authorization methods
use App\Models\Recipe;
use App\Models\User;

// OOP: Policy class encapsulates authorization logic for Recipe CRUD operations
class RecipePolicy
{
    /**
     * OOP: Method uses type hints (User, Recipe) and returns bool for authorization decision.
     * CRUD: Authorizes UPDATE operation by checking if the authenticated user owns the recipe.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        // Compares user ID with recipe's owner ID to authorize the update operation
        return $user->id === $recipe->user_id;
    }

    /**
     * OOP: Method uses type hints (User, Recipe) and returns bool for authorization decision.
     * CRUD: Authorizes DELETE operation by checking if the authenticated user owns the recipe.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        // Compares user ID with recipe's owner ID to authorize the delete operation
        return $user->id === $recipe->user_id;
    }
}
