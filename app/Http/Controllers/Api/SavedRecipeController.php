<?php

namespace App\Http\Controllers\Api;

// Import statements for controller dependencies
use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Controller handles saved recipe operations (bookmarks)
class SavedRecipeController extends Controller
{
    // OOP: Public method retrieves saved recipes. CRUD: READ operation gets user's bookmarked recipes via pivot table.
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Access many-to-many relationship through saved_recipes pivot table
            $savedRecipes = $user->savedRecipes()
                ->with('user:id,name')
                ->get();
            return response()->json([
                'success' => true,
                'data' => $savedRecipes,
                'count' => $savedRecipes->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching saved recipes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch saved recipes.'
            ], 500);
        }
    }

    // OOP: Public method checks if recipe is saved. CRUD: READ operation verifies relationship existence.
    public function check($recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Check if many-to-many relationship exists in pivot table
            $isSaved = $user->savedRecipes()
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
            Log::error('Error checking saved recipe', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check saved status.'
            ], 500);
        }
    }

    // OOP: Public method saves recipe. CRUD: CREATE operation adds to pivot table with duplicate prevention.
    public function store($recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            // Use database unique constraint to prevent duplicate saves atomically
            try {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                // Insert record in saved_recipes pivot table
                $user->savedRecipes()->attach($recipeId);
            } catch (\Illuminate\Database\QueryException $e) {
                // Unique constraint violation means already saved
                if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint failed') || $e->getCode() == 23000) {
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
            Log::error('Error saving recipe', [
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

    // OOP: Public method unsaves recipe. CRUD: DELETE operation removes from pivot table.
    public function destroy($recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Remove record from saved_recipes pivot table
            $removed = $user->savedRecipes()->detach($recipeId);

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
            Log::error('Error unsaving recipe', [
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

    // OOP: Public method toggles saved status. CRUD: CREATE/DELETE operation using transaction with row locking.
    public function toggle($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            // Transaction with row locking prevents race conditions
            $result = DB::transaction(function () use ($recipeId) {
                // Lock user's saved_recipes rows for this recipe
                $isSaved = DB::table('saved_recipes')
                    ->where('user_id', Auth::id())
                    ->where('recipe_id', $recipeId)
                    ->lockForUpdate()
                    ->exists();

                /** @var \App\Models\User $user */
                $user = Auth::user();
                if ($isSaved) {
                    // Unsave if currently saved
                    $user->savedRecipes()->detach($recipeId);
                    return ['message' => 'Recipe unsaved successfully.', 'status' => false];
                } else {
                    // Save if not currently saved
                    $user->savedRecipes()->attach($recipeId);
                    return ['message' => 'Recipe saved successfully.', 'status' => true];
                }
            });

            $message = $result['message'];
            $newStatus = $result['status'];
            return response()->json([
                'success' => true,
                'message' => $message, // Dynamic message based on action performed
                'data' => [
                    'isSaved' => $newStatus // Boolean: true if now saved, false if now unsaved
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Catches when recipe doesn't exist
            // Returns not found response with HTTP 404 status
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error toggling saved recipe', [
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
