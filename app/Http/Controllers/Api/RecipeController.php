<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeRequest;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Recipe::with('user')
                ->withCount('ratings')
                ->withAvg('ratings', 'rating');

            // Limit results if requested (max 100)
            if ($request->has('limit')) {
                $limit = min((int)$request->limit, 100);
                $query->limit($limit);
            }

            $recipes = $query->latest()->get();

            // Format the ratings data
            $recipes = $recipes->map(function ($recipe) {
                $recipe->average_rating = round($recipe->ratings_avg_rating ?? 0, 1);
                $recipe->ratings_count = $recipe->ratings_count ?? 0;
                unset($recipe->ratings_avg_rating);
                return $recipe;
            });

            return response()->json([
                'success' => true,
                'data' => $recipes,
                'count' => $recipes->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching recipes', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipes.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $recipe = Recipe::with(['user', 'ingredients'])
                ->withCount('ratings')
                ->withAvg('ratings', 'rating')
                ->findOrFail($id);

            // Format the ratings data
            $recipe->average_rating = round($recipe->ratings_avg_rating ?? 0, 1);
            $recipe->ratings_count = $recipe->ratings_count ?? 0;
            unset($recipe->ratings_avg_rating);

            return response()->json([
                'success' => true,
                'data' => $recipe
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error fetching recipe', [
                'recipe_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipe details.'
            ], 500);
        }
    }

    public function store(StoreRecipeRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('recipes', $imageName, 'public');
                $validated['image'] = $imagePath;
            }

            // Calculate total time
            $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];
            $validated['user_id'] = Auth::id();

            $recipe = Recipe::create($validated);

            // Create ingredients
            if (isset($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $ingredient) {
                    $recipe->ingredients()->create($ingredient);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recipe created successfully!',
                'data' => $recipe->load('ingredients')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error creating recipe', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create recipe. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(StoreRecipeRequest $request, $id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            // Check if user owns the recipe
            if ($recipe->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this recipe.'
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                    Storage::disk('public')->delete($recipe->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('recipes', $imageName, 'public');
                $validated['image'] = $imagePath;
            }

            // Calculate total time
            $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];

            $recipe->update($validated);

            // Update ingredients - delete old ones and create new ones
            $recipe->ingredients()->delete();
            if (isset($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $ingredient) {
                    $recipe->ingredients()->create($ingredient);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully!',
                'data' => $recipe->load('ingredients')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating recipe', [
                'recipe_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update recipe. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            // Check if user owns the recipe
            if ($recipe->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this recipe.'
                ], 403);
            }

            // Delete recipe image if exists
            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }

            $recipe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting recipe', [
                'recipe_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recipe. Please try again.'
            ], 500);
        }
    }

    public function myRecipes()
    {
        try {
            $recipes = Recipe::where('user_id', Auth::id())
                ->with('ingredients')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $recipes,
                'count' => $recipes->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching user recipes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your recipes.'
            ], 500);
        }
    }
}
