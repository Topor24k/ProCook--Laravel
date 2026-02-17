<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of recipes.
     */
    public function index()
    {
        $recipes = Recipe::with('user', 'ingredients')
            ->latest()
            ->paginate(12);
        
        return view('recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create()
    {
        return view('recipes.create');
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'cuisine_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'serving_size' => 'required|integer|min:1',
            'preparation_notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.measurement' => 'required|string|max:100',
            'ingredients.*.substitution_option' => 'nullable|string|max:255',
            'ingredients.*.allergen_info' => 'nullable|string|max:255',
        ]);

        // Calculate total time
        $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];
        $validated['user_id'] = Auth::id();

        // Transaction: create recipe + ingredients atomically to prevent partial data
        $recipe = DB::transaction(function () use ($validated, $request) {
            // Create recipe
            $recipe = Recipe::create($validated);

            // Create ingredients
            foreach ($request->ingredients as $index => $ingredientData) {
                $recipe->ingredients()->create([
                    'name' => $ingredientData['name'],
                    'measurement' => $ingredientData['measurement'],
                    'substitution_option' => $ingredientData['substitution_option'] ?? null,
                    'allergen_info' => $ingredientData['allergen_info'] ?? null,
                    'order' => $index + 1,
                ]);
            }

            return $recipe;
        });

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe created successfully!');
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe)
    {
        $recipe->load('user', 'ingredients');
        return view('recipes.show', compact('recipe'));
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe)
    {
        $this->authorize('update', $recipe);
        $recipe->load('ingredients');
        return view('recipes.edit', compact('recipe'));
    }

    /**
     * Update the specified recipe in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'cuisine_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'serving_size' => 'required|integer|min:1',
            'preparation_notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.measurement' => 'required|string|max:100',
            'ingredients.*.substitution_option' => 'nullable|string|max:255',
            'ingredients.*.allergen_info' => 'nullable|string|max:255',
        ]);

        // Calculate total time
        $validated['total_time'] = $validated['prep_time'] + $validated['cook_time'];

        // Transaction: update recipe + replace ingredients atomically
        // Prevents data loss if failure occurs after deleting old ingredients
        DB::transaction(function () use ($recipe, $validated, $request) {
            // Update recipe
            $recipe->update($validated);

            // Delete old ingredients and create new ones
            $recipe->ingredients()->delete();
            foreach ($request->ingredients as $index => $ingredientData) {
                $recipe->ingredients()->create([
                    'name' => $ingredientData['name'],
                    'measurement' => $ingredientData['measurement'],
                    'substitution_option' => $ingredientData['substitution_option'] ?? null,
                    'allergen_info' => $ingredientData['allergen_info'] ?? null,
                    'order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully!');
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);
        
        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully!');
    }

    /**
     * Display recipes for the authenticated user.
     */
    public function myRecipes()
    {
        $recipes = Auth::user()->recipes()
            ->with('ingredients')
            ->latest()
            ->paginate(12);
        
        return view('recipes.my-recipes', compact('recipes'));
    }
}
