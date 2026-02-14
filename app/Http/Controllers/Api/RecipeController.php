<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with('user');

        // Limit results if requested
        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $recipes = $query->latest()->get();

        return response()->json($recipes);
    }

    public function show($id)
    {
        $recipe = Recipe::with(['user', 'ingredients'])->findOrFail($id);

        return response()->json($recipe);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'cuisine_type' => 'required|string',
            'category' => 'required|string',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'serving_size' => 'required|integer|min:1',
            'preparation_notes' => 'required|string',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|string',
            'ingredients.*.unit' => 'nullable|string',
        ]);

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

        return response()->json($recipe->load('ingredients'), 201);
    }

    public function update(Request $request, Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'cuisine_type' => 'required|string',
            'category' => 'required|string',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'serving_size' => 'required|integer|min:1',
            'preparation_notes' => 'required|string',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|string',
            'ingredients.*.unit' => 'nullable|string',
        ]);

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

        return response()->json($recipe->load('ingredients'));
    }

    public function destroy(Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully']);
    }

    public function myRecipes()
    {
        $recipes = Recipe::where('user_id', Auth::id())
            ->with('ingredients')
            ->latest()
            ->get();

        return response()->json($recipes);
    }
}
