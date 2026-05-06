<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\Vote;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user:id,name')
            ->withCount([
                'votes as upvotes' => fn($q) => $q->where('type', 1),
                'votes as downvotes' => fn($q) => $q->where('type', -1),
            ])
            ->withExists([
                'votes as user_voted' => fn($q) =>
                    $q->where('user_id', auth()->id())
            ])
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'category' => $request->category,
        ]);

        return response()->json([
            'message' => 'Post created', 
            'post' => new PostResource($post)
        ], 201);
    }

    public function show(string $slug)
    {
        $post = Post::with('user:id,name')
            ->withCount([
                'votes as upvotes' => fn($q) => $q->where('type', 1),
                'votes as downvotes' => fn($q) => $q->where('type', -1),
            ])
            ->withExists([
                'votes as user_voted' => fn($q) =>
                    $q->where('user_id', auth()->id())
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->update($request->validated());

        return response()->json([
            'message' => 'Post updated', 
            'post' => new PostResource($post)
        ]);
    }

    public function destroy(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function votes(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $votes = Vote::where('post_id', $post->id)
            ->selectRaw('SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) as upvotes')
            ->selectRaw('SUM(CASE WHEN type = -1 THEN 1 ELSE 0 END) as downvotes')
            ->first();

        return response()->json([
            'upvotes' => (int) $votes->upvotes,
            'downvotes' => (int) $votes->downvotes,
            'post_votes' => $votes->upvotes - $votes->downvotes
        ]);
    }
}
