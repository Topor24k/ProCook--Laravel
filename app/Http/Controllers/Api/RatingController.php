<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Get or create/update a user's rating for a recipe.
     */
    public function store(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            // Prevent users from rating their own recipes
            if ($recipe->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot rate your own recipe.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update or create rating
            $rating = Rating::updateOrCreate(
                [
                    'recipe_id' => $recipeId,
                    'user_id' => Auth::id()
                ],
                [
                    'rating' => $request->rating
                ]
            );

            // Get updated recipe stats
            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully.',
                'data' => [
                    'rating' => $rating,
                    'averageRating' => round($averageRating, 1),
                    'ratingsCount' => $ratingsCount
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error submitting rating', [
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

    /**
     * Get user's rating for a recipe.
     */
    public function show($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $userRating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

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
            \Log::error('Error fetching rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    /**
     * Get public rating data for a recipe (no auth required).
     */
    public function showPublic($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

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
            \Log::error('Error fetching public rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rating.'
            ], 500);
        }
    }

    /**
     * Delete user's rating for a recipe.
     */
    public function destroy($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $rating = Rating::where('recipe_id', $recipeId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found.'
                ], 404);
            }

            $rating->delete();

            // Get updated recipe stats
            $averageRating = $recipe->ratings()->avg('rating');
            $ratingsCount = $recipe->ratings()->count();

            return response()->json([
                'success' => true,
                'message' => 'Rating removed successfully.',
                'data' => [
                    'averageRating' => round($averageRating, 1),
                    'ratingsCount' => $ratingsCount
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting rating', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rating.'
            ], 500);
        }
    }
}
