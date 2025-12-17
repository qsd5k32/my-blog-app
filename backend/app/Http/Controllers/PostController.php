<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Post::with(['user:id,name,email']);

            // Filter by published status if user is not authenticated or not the owner
            if (!$request->user()) {
                $query->published();
            }

            // Apply filters
            if ($request->has('published')) {
                $query->where('published', $request->boolean('published'));
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $posts = $query->latest()->paginate($perPage);

            return response()->json($posts, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created post.
     *
     * @param StorePostRequest $request
     * @return JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $post = Post::create([
                'title' => $request->title,
                'content' => $request->content,
                'user_id' => $request->user()->id,
                'published' => $request->boolean('published', false),
            ]);

            $post->load('user:id,name,email');

            return response()->json([
                'message' => 'Post created successfully',
                'post' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified post.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $post = Post::with(['user:id,name,email', 'comments.user:id,name,email'])
                ->findOrFail($id);

            return response()->json($post, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified post.
     *
     * @param UpdatePostRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);

            // Check if user is authorized to update this post
            if ($post->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized to update this post',
                ], 403);
            }

            $post->update($request->only(['title', 'content', 'published']));

            $post->load('user:id,name,email');

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $post,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified post.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);

            // Check if user is authorized to delete this post
            if ($post->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized to delete this post',
                ], 403);
            }

            $post->delete();

            return response()->json([
                'message' => 'Post deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}