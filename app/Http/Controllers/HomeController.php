<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->take(8)
            ->get();

        $categories = Category::orderBy('order')->get();

        $latestRecipes = Recipe::with('user')
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('featuredProducts', 'categories', 'latestRecipes'));
    }
}
