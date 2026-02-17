<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SavedRecipeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes with rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
    Route::get('/recipes/{recipe}/rating/public', [RatingController::class, 'showPublic']);
    Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']);
});

// Auth routes with stricter rate limiting
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authenticated users only)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Profile management
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);
    
    // Recipe management
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    Route::get('/my-recipes', [RecipeController::class, 'myRecipes']);
    
    // Comments
    Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']);
    Route::post('/recipes/{recipe}/comments', [CommentController::class, 'store']);
    Route::put('/recipes/{recipe}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/recipes/{recipe}/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Ratings
    Route::get('/recipes/{recipe}/rating', [RatingController::class, 'show']);
    Route::post('/recipes/{recipe}/rating', [RatingController::class, 'store']);
    Route::delete('/recipes/{recipe}/rating', [RatingController::class, 'destroy']);
    
    // Saved Recipes
    Route::get('/saved-recipes', [SavedRecipeController::class, 'index']);
    Route::get('/recipes/{recipe}/saved', [SavedRecipeController::class, 'check']);
    Route::post('/recipes/{recipe}/save', [SavedRecipeController::class, 'toggle']);
});
