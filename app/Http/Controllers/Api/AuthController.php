<?php

namespace App\Http\Controllers\Api;

// Import statements for OOP inheritance and dependencies
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

// Controller handles user authentication operations
class AuthController extends Controller
{
    // OOP: Public method handles registration. CRUD: CREATE operation inserts user via User::create().
    public function register(RegisterRequest $request)
    {
        try {
            // Rate limiting prevents spam - max 3 registration attempts per hour
            $key = 'register:' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            // Increment rate limiter counter
            RateLimiter::hit($key, 3600);

            // Create new user with hashed password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Send welcome email - failure won't prevent registration
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Log user in after successful registration
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to ProCook.',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null // Shows error only in debug mode
            ], 500);
        }
    }

    // OOP: Public method handles login with rate limiting. CRUD: READ operation authenticates user credentials.
    public function login(LoginRequest $request)
    {
        try {
            // Rate limiting prevents brute force - max 5 login attempts per minute
            $key = 'login:' . $request->email . ':' . $request->ip();
            
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please wait before trying again.',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            // Verify credentials with Auth::attempt()
            if (!Auth::attempt($request->only('email', 'password'))) {
                RateLimiter::hit($key, 60);
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

            // Regenerate session to prevent fixation attacks
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Login successful! Welcome back.',
                'user' => Auth::user()
            ]);

        } catch (\Exception $e) {
            Log::error('Login error', [
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

    // OOP: Public method terminates session. CRUD: Logout operation ends authenticated session.
    public function logout(Request $request)
    {
        try {
            // End authenticated session
            Auth::guard('web')->logout();

            // Destroy session data and regenerate CSRF token
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.'
            ], 500);
        }
    }

    // OOP: Public method returns authenticated user. CRUD: READ operation fetches current user data.
    public function user(Request $request)
    {
        try {
            // Get currently authenticated user
            $user =  $request->user();

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
            Log::error('Error fetching user', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user information.'
            ], 500);
        }
    }

    // OOP: Public method retrieves detailed profile with statistics. CRUD: READ operation with relationship counts.
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            // Count related records using model relationships
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
            Log::error('Error fetching profile', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile information.'
            ], 500);
        }
    }

    // OOP: Public method updates user data. CRUD: UPDATE operation modifies user information.
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validate name and email (unique except current user)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            // Update user record with validated data
            $user->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user->fresh()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating profile', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.'
            ], 500);
        }
    }

    // OOP: Public method changes user password. CRUD: UPDATE operation updates password with verification.
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validate password fields
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Verify current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['The current password is incorrect.']]
                ], 422);
            }

            // Update password with new hashed value
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error changing password', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password.'
            ], 500);
        }
    }

    // OOP: Public method deletes account with optional data preservation. CRUD: DELETE operation removes user using transaction.
    public function deleteAccount(Request $request)
    {
        try {
            // Validate password and mode (delete_all or keep_data)
            $request->validate([
                'password' => 'required|string',
                'mode' => 'required|in:delete_all,keep_data',
            ]);

            $user = $request->user();

            // Verify password before account deletion
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect.',
                    'errors' => ['password' => ['The password is incorrect.']]
                ], 422);
            }

            // Transaction ensures all deletions succeed or all fail
            DB::transaction(function () use ($user, $request) {
                if ($request->mode === 'delete_all') {
                    // Delete all user data including recipes and related content
                    $user->recipes()->each(function ($recipe) {
                        // Cascade delete: comments, ratings, ingredients, saved_recipes
                        $recipe->comments()->delete();
                        $recipe->ratings()->delete();
                        $recipe->ingredients()->delete();
                        DB::table('saved_recipes')->where('recipe_id', $recipe->id)->delete();
                        $recipe->delete();
                    });

                    // Delete user's comments and ratings on other recipes
                    $user->comments()->delete();
                    $user->ratings()->delete();
                    $user->savedRecipes()->detach();
                } else {
                    // Keep data but nullify user_id so content remains anonymized
                    DB::table('recipes')->where('user_id', $user->id)
                        ->update(['user_id' => null]);
                    DB::table('comments')->where('user_id', $user->id)
                        ->update(['user_id' => null]);
                    DB::table('ratings')->where('user_id', $user->id)
                        ->update(['user_id' => null]);
                    $user->savedRecipes()->detach();
                }

                // Logout and delete user account
                Auth::guard('web')->logout();
                $user->tokens()->delete();
                $user->delete();
            });
            $request->session()->invalidate();
            // $request->session()->regenerateToken() - creates new CSRF token
            $request->session()->regenerateToken();

            // Returns success response with HTTP 200 OK status
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error deleting account', [
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
