<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Http\Resources\BlogCollection;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Services\BlogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class BlogController extends Controller
{
use AuthorizesRequests;

    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

  //Get all blogs
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $blogs = $this->blogService->getAllBlogs($perPage);

            return response()->json([
                'success' => true,
                'data' => new BlogCollection($blogs),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch blogs.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

   //create a blog
    public function store(StoreBlogRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Blog::class);

            $blog = $this->blogService->createBlog(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Blog created successfully',
                'data' => [
                    'blog' => new BlogResource($blog),
                ],
            ], 201);

        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to create blogs.',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create blog.',

            ], 500);
        }
    }

    //get a single blog
    public function show(int $id): JsonResponse
    {
        try {
            $blog = $this->blogService->getBlog($id);

            $this->authorize('view', $blog);

            return response()->json([
                'success' => true,
                'data' => [
                    'blog' => new BlogResource($blog),
                ],
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found.',
            ], 404);

        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this blog.',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch blog.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    //update a blog
    public function update(UpdateBlogRequest $request, int $id): JsonResponse
{
    try {
        $blog = Blog::findOrFail($id);

        $this->authorize('update', $blog); // Policy check

        $updatedBlog = $this->blogService->updateBlog(
            $id,
            $request->validated(), // validated fields from UpdateBlogRequest
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully',
            'data' => [
                'blog' => new BlogResource($updatedBlog),
            ],
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Blog not found.',
        ], 404);

    } catch (AuthorizationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to update this blog.',
        ], 403);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update blog.',
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}

    //delete a blog
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $blog = Blog::findOrFail($id);

            $this->authorize('delete', $blog);

            $this->blogService->deleteBlog($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Blog deleted successfully',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found.',
            ], 404);

        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this blog.',
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete blog.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

   //get a blog by users
    public function userBlogs(Request $request, int $userId): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $blogs = $this->blogService->getBlogsByUser($userId, $perPage);

            return response()->json([
                'success' => true,
                'data' => new BlogCollection($blogs),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user blogs.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
