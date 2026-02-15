<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Get all comments for a recipe.
     */
    public function index($recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);
            
            // Get only parent comments (no parent_id) with their replies
            $comments = Comment::with(['user:id,name', 'replies.user:id,name'])
                ->where('recipe_id', $recipeId)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
                'count' => $comments->count()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error fetching comments', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments.'
            ], 500);
        }
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::findOrFail($recipeId);

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000',
                'parent_id' => 'nullable|exists:comments,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // If replying, verify parent comment belongs to same recipe
            if ($request->parent_id) {
                $parentComment = Comment::where('id', $request->parent_id)
                    ->where('recipe_id', $recipeId)
                    ->first();
                    
                if (!$parentComment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent comment.'
                    ], 422);
                }
            }

            $comment = Comment::create([
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(), 'parent_id' => $request->parent_id,
                'comment' => $request->comment
            ]);

            $comment->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully.',
                'data' => $comment
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error creating comment', [
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment.'
            ], 500);
        }
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, $recipeId, $commentId)
    {
        try {
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Check if the comment belongs to the authenticated user
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this comment.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment->update([
                'comment' => $request->comment
            ]);

            $comment->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully.',
                'data' => $comment
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error updating comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment.'
            ], 500);
        }
    }

    /**
     * Delete a comment.
     */
    public function destroy($recipeId, $commentId)
    {
        try {
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Check if the comment belongs to the authenticated user
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this comment.'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment.'
            ], 500);
        }
    }
}
