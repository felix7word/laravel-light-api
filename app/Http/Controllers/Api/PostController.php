<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Posts
 *
 * APIs for managing posts
 */
class PostController extends Controller
{
    /**
     * List all posts
     *
     * @queryParam filter[title] string Filter posts by title. Example: Laravel
     * @queryParam filter[content] string Filter posts by content. Example: API
     * @queryParam sort string Sort posts by field. Example: title,-created_at
     * @queryParam page int Page number. Example: 1
     * @queryParam per_page int Number of items per page. Example: 10
     *
     * @response 200 {"data": [{"id": 1, "title": "Laravel API", "content": "Building APIs with Laravel", "user_id": 1, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z", "user": {"id": 1, "name": "John Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}}], "links": {"first": "http://localhost/api/posts?page=1", "last": "http://localhost/api/posts?page=1", "prev": null, "next": null}, "meta": {"current_page": 1, "from": 1, "last_page": 1, "links": [{"url": null, "label": "&laquo; Previous", "active": false}, {"url": "http://localhost/api/posts?page=1", "label": "1", "active": true}, {"url": null, "label": "Next &raquo;", "active": false}], "path": "http://localhost/api/posts", "per_page": 10, "to": 1, "total": 1}}}
     */
    public function index()
    {
        $cacheKey = 'posts_'.request()->getQueryString().'_'.request()->page;

        $posts = cache()->remember($cacheKey, 60, function () {
            return QueryBuilder::for(Post::class)
                ->allowedFilters(['title', 'content'])
                ->allowedSorts(['title', 'created_at'])
                ->with('user')
                ->paginate(10);
        });

        return $this->paginate($posts, 'Posts retrieved successfully');
    }

    /**
     * Create a new post
     *
     * @authenticated
     *
     * @bodyParam title string required The post title. Example: Laravel API
     * @bodyParam content string required The post content. Example: Building APIs with Laravel
     *
     * @response 201 {"id": 1, "title": "Laravel API", "content": "Building APIs with Laravel", "user_id": 1, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = $request->user()->posts()->create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // Clear posts cache
        cache()->flush();

        return $this->success($post, 'Post created successfully', 201);
    }

    /**
     * Get a single post
     *
     * @urlParam post int required The post ID. Example: 1
     *
     * @response 200 {"id": 1, "title": "Laravel API", "content": "Building APIs with Laravel", "user_id": 1, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z", "user": {"id": 1, "name": "John Doe", "email": "john@example.com", "email_verified_at": null, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}}
     */
    public function show(Post $post)
    {
        return $this->success($post->load('user'), 'Post retrieved successfully');
    }

    /**
     * Update a post
     *
     * @authenticated
     *
     * @urlParam post int required The post ID. Example: 1
     *
     * @bodyParam title string The post title. Example: Updated Laravel API
     * @bodyParam content string The post content. Example: Updated content
     *
     * @response 200 {"id": 1, "title": "Updated Laravel API", "content": "Updated content", "user_id": 1, "created_at": "2026-04-05T00:00:00.000000Z", "updated_at": "2026-04-05T00:00:00.000000Z"}
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $post->update($request->all());

        // Clear posts cache
        cache()->flush();

        return $this->success($post, 'Post updated successfully');
    }

    /**
     * Delete a post
     *
     * @authenticated
     *
     * @urlParam post int required The post ID. Example: 1
     *
     * @response 204 {}
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        // Clear posts cache
        cache()->flush();

        return $this->success(null, 'Post deleted successfully', 204);
    }
}
