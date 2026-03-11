<?php

namespace App\Http\Controllers\Api;

// Import statements for controller dependencies
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

// Controller handles comment operations for recipes
class CommentController extends Controller
{
    // OOP: Public method retrieves comments. CRUD: READ operation gets all comments for a recipe.
    public function index($recipeId)
    {
        try {
            // Validates recipe exists or throws 404
            $recipe = Recipe::findOrFail($recipeId);
            
            // Get parent comments with nested replies using eager loading
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
            Log::error('Error fetching comments', [
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments.'
            ], 500);
        }
    }

    // OOP: Public method creates comments or replies. CRUD: CREATE operation inserts new comment.
    public function store(Request $request, $recipeId)
    {
        try {
            // Validates recipe exists before creating comment
            $recipe = Recipe::findOrFail($recipeId);

            // Validate comment text and optional parent_id
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000',
                'parent_id' => 'nullable|exists:comments,id'
            ]);
            if ($validator->fails()) {
                // Returns validation errors with HTTP 422 Unprocessable Entity status
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors() // Returns array of validation error messages
                ], 422);
            }

            // Verify parent comment belongs to same recipe if replying
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

            // Create new comment with user and recipe associations
            $comment = Comment::create([
                'recipe_id' => $recipeId,
                'user_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'comment' => $request->comment
            ]);

            // Load user relationship for response
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
            Log::error('Error creating comment', [
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

    // OOP: Public method updates comment with authorization. CRUD: UPDATE operation modifies existing comment.
    public function update(Request $request, $recipeId, $commentId)
    {
        try {
            // Find comment for specified recipe
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Verify user owns the comment before allowing update
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this comment.'
                ], 403);
            }

            // Validate updated comment text
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:1|max:1000'
            ]);
            if ($validator->fails()) {
                // Returns validation errors with HTTP 422 Unprocessable Entity status
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors() // Array of validation error messages
                ], 422);
            }

            // Update comment text in database
            $comment->update([
                'comment' => $request->comment
            ]);

            // Load user relationship for response
            $comment->load('user:id,name');

            // Returns success response with HTTP 200 OK status
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
            Log::error('Error updating comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment.'
            ], 500);
        }
    }

    // OOP: Public method deletes comment with authorization. CRUD: DELETE operation removes comment (cascades to replies).
    public function destroy($recipeId, $commentId)
    {
        try {
            // Find comment for specified recipe
            $comment = Comment::where('recipe_id', $recipeId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Verify user owns the comment before deletion
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this comment.'
                ], 403);
            }

            // Delete comment (cascades to replies via foreign key)
            $comment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Catches when comment doesn't exist or doesn't belong to recipe
            // Returns not found response with HTTP 404 status
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting comment', [
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
