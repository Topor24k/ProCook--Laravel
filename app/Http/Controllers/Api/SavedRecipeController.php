<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            // Atomic save: use DB unique constraint to prevent duplicate saves
            // instead of a check-then-act pattern (TOCTOU race condition)
            try {
                Auth::user()->savedRecipes()->attach($recipeId);
            } catch (\Illuminate\Database\QueryException $e) {
                // Unique constraint violation = already saved
                if ($e->errorInfo[1] == 1062 || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Recipe already saved.'
                    ], 409);
                }
                throw $e;
            }

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

            // Transaction + lock to prevent TOCTOU race condition on toggle
            $result = DB::transaction(function () use ($recipeId) {
                // Lock the user's saved_recipes rows for this recipe to prevent concurrent toggles
                $isSaved = DB::table('saved_recipes')
                    ->where('user_id', Auth::id())
                    ->where('recipe_id', $recipeId)
                    ->lockForUpdate()
                    ->exists();

                if ($isSaved) {
                    Auth::user()->savedRecipes()->detach($recipeId);
                    return ['message' => 'Recipe unsaved successfully.', 'status' => false];
                } else {
                    Auth::user()->savedRecipes()->attach($recipeId);
                    return ['message' => 'Recipe saved successfully.', 'status' => true];
                }
            });

            $message = $result['message'];
            $newStatus = $result['status'];

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
