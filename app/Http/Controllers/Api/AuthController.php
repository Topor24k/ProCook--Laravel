<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            // Rate limiting - max 3 registration attempts per hour
            $key = 'register:' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            RateLimiter::hit($key, 3600); // 1 hour

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Send welcome email (don't block on failure)
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to ProCook.',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            // Rate limiting - max 5 login attempts per minute
            $key = 'login:' . $request->email . ':' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please wait before trying again.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                RateLimiter::hit($key, 60); // Track failed attempts for 1 minute

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Please check your email and password.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 401);
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login successful! Welcome back.',
                'user' => Auth::user()
            ]);

        } catch (\Exception $e) {
            \Log::error('Login error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Logout error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.'
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching user', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user information.'
            ], 500);
        }
    }

    /**
     * Get detailed profile info with statistics.
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            $recipesCount = $user->recipes()->count();
            $commentsCount = $user->comments()->count();
            $ratingsCount = $user->ratings()->count();
            $savedCount = $user->savedRecipes()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'stats' => [
                        'recipes_count' => $recipesCount,
                        'comments_count' => $commentsCount,
                        'ratings_count' => $ratingsCount,
                        'saved_count' => $savedCount,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching profile', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile information.'
            ], 500);
        }
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error updating profile', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.'
            ], 500);
        }
    }

    /**
     * Change the user's password.
     */
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['The current password is incorrect.']]
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error changing password', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password.'
            ], 500);
        }
    }

    /**
     * Delete account.
     *
     * @param string $mode  "delete_all" removes all user data;
     *                      "keep_data" removes only the account, orphaning recipes/comments.
     */
    public function deleteAccount(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
                'mode' => 'required|in:delete_all,keep_data',
            ]);

            $user = $request->user();

            // Verify password before destructive action
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect.',
                    'errors' => ['password' => ['The password is incorrect.']]
                ], 422);
            }

            DB::transaction(function () use ($user, $request) {
                if ($request->mode === 'delete_all') {
                    // Delete everything: recipes (cascade deletes ingredients, comments, ratings), 
                    // user's comments on other recipes, ratings, saved recipes
                    $user->recipes()->each(function ($recipe) {
                        // Cascade: ingredients, comments & replies, ratings, saved_recipes pivot
                        $recipe->comments()->delete();
                        $recipe->ratings()->delete();
                        $recipe->ingredients()->delete();
                        DB::table('saved_recipes')->where('recipe_id', $recipe->id)->delete();
                        $recipe->delete();
                    });

                    // Delete user's comments on OTHER people's recipes
                    $user->comments()->delete();

                    // Delete user's ratings on other recipes
                    $user->ratings()->delete();

                    // Delete saved recipes pivot entries
                    $user->savedRecipes()->detach();
                } else {
                    // keep_data: nullify user_id so content remains but is disassociated
                    // Update recipes to have null user_id
                    DB::table('recipes')->where('user_id', $user->id)
                        ->update(['user_id' => null]);

                    // Update comments to have null user_id
                    DB::table('comments')->where('user_id', $user->id)
                        ->update(['user_id' => null]);

                    // Update ratings to have null user_id  
                    DB::table('ratings')->where('user_id', $user->id)
                        ->update(['user_id' => null]);

                    // Remove saved recipes pivot (these are personal bookmarks, no need to keep)
                    $user->savedRecipes()->detach();
                }

                // Logout and delete user
                Auth::guard('web')->logout();
                $user->tokens()->delete(); // Revoke sanctum tokens
                $user->delete();
            });

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error deleting account', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again.'
            ], 500);
        }
    }
}
