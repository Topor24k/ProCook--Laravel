<?php

namespace App\Http\Controllers\Api;

// Import statements for controller dependencies
use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

// Controller handles recipe rating operations
class RatingController extends Controller
{
    // OOP: Public method handles upsert. CRUD: CREATE/UPDATE operation submits or updates rating using transaction.
    public function store(Request $request, $recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            // Prevent users from rating their own recipes
            if ($recipe->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot rate your own recipe.'
                ], 403);
            }

            // Validate rating value (1-5 stars)
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5'
            ]);
            if ($validator->fails()) {
                // Returns validation errors with HTTP 422 Unprocessable Entity status
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors() // Array of validation error messages
                ], 422);
            }

            // Transaction ensures rating update and stats calculation are atomic
            $result = DB::transaction(function () use ($recipeId, $request) {
                // Update or create rating (unique constraint prevents duplicates)
                $rating = Rating::updateOrCreate(
                    [
                        'recipe_id' => $recipeId,
                        'user_id' => Auth::id()
                    ],
                    [
                        'rating' => $request->rating
                    ]
                );

                // Calculate updated statistics within transaction
                $averageRating = Rating::where('recipe_id', $recipeId)->avg('rating');
                $ratingsCount = Rating::where('recipe_id', $recipeId)->count();

                return compact('rating', 'averageRating', 'ratingsCount');
            });
            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully.',
                'data' => [
                    'rating' => $result['rating'], // User's Rating model with id, recipe_id, user_id, rating
                    'averageRating' => round($result['averageRating'], 1), // Rounded average (e.g., 4.5)
                    'ratingsCount' => $result['ratingsCount'] // Total count of ratings
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Catches when recipe doesn't exist in database
            // Returns not found response with HTTP 404 status
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error submitting rating', [
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

    // OOP: Public method retrieves user's rating. CRUD: READ operation gets current user's rating with statistics.
    public function show($recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            // Find user's specific rating for this recipe
            $userRating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

            // Calculate aggregate statistics
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
            Log::error('Error fetching rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    // OOP: Public method returns public rating data. CRUD: READ operation gets statistics without authentication.
    public function showPublic($recipeId)
    {
        try {
            // Validates recipe exists
            $recipe = Recipe::findOrFail($recipeId);

            // Calculate aggregate statistics
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
            Log::error('Error fetching public rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    // OOP: Public method deletes rating. CRUD: DELETE operation removes user's rating.
    public function destroy($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);
            // Throws ModelNotFoundException if recipe doesn't exist
            $recipe = Recipe::findOrFail($recipeId);

            // Find user's rating for this recipe
            $rating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

            // Check if rating exists
            if (!$rating) {
                // Returns not found response if user hasn't rated this recipe
                // HTTP 404 Not Found status
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found.'
                ], 404);
            }

            // Delete rating and recalculate statistics
            $rating->delete();

            // Calculate updated statistics after deletion using relationship
            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            // Returns success response with HTTP 200 OK status
            return response()->json([
                'success' => true,
                'message' => 'Rating removed successfully.',
                'data' => [
                    'averageRating' => round($averageRating, 1),
                    'ratingsCount' => $ratingsCount
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
            // Catches any other unexpected errors
            // Log::error() - writes to storage/logs/laravel.log
            Log::error('Error deleting rating', [
                'recipe_id' => $recipeId, // Logs which recipe failed
                'error' => $e->getMessage() // Logs error details
            ]);

            // Returns error response with HTTP 500 Internal Server Error status
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rating.'
            ], 500);
        }
    }
}
