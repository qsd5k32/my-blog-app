<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a specific post.
     *
     * @param Request $request
     * @param int $postId
     * @return JsonResponse
     */
    public function index(Request $request, int $postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);

            $perPage = $request->input('per_page', 15);
            $comments = $post->comments()
                ->with('user:id,name,email')
                ->latest()
                ->paginate($perPage);

            return response()->json($comments, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve comments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created comment for a specific post.
     *
     * @param StoreCommentRequest $request
     * @param int $postId
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request, int $postId): JsonResponse
    {
        try {
            $post = Post::findOrFail($postId);

            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'content' => $request->content,
            ]);

            $comment->load('user:id,name,email');

            return response()->json([
                'message' => 'Comment created successfully',
                'comment' => $comment,
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified comment.
     *
     * @param UpdateCommentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        try {
            $comment = Comment::findOrFail($id);

            // Check if user is authorized to update this comment
            if ($comment->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized to update this comment',
                ], 403);
            }

            $comment->update([
                'content' => $request->content,
            ]);

            $comment->load('user:id,name,email');

            return response()->json([
                'message' => 'Comment updated successfully',
                'comment' => $comment,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Comment not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified comment.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $comment = Comment::findOrFail($id);

            // Check if user is authorized to delete this comment
            if ($comment->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized to delete this comment',
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'message' => 'Comment deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Comment not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}