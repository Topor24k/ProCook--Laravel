<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
