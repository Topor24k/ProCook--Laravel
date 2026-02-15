<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedRecipeController extends Controller
{
    /**
     * Get all saved recipes for the authenticated user.
     */
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

    /**
     * Check if a recipe is saved by the user.
     */
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
                'data' => [
                    'isSaved' => $isSaved
                ]
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

    /**
     * Save a recipe for the authenticated user.
     */
    public function store($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            // Check if already saved
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
                'data' => [
                    'isSaved' => true
                ]
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

    /**
     * Unsave a recipe for the authenticated user.
     */
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
                'data' => [
                    'isSaved' => false
                ]
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

    /**
     * Toggle saved status for a recipe.
     */
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
                'data' => [
                    'isSaved' => $newStatus
                ]
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
