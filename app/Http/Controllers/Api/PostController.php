<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'category' => 'required|string|in:Feature,Bug,Question,Improvement'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'category' => $request->category,
            'vote_count' => 0
        ]);

        return response()->json(['message' => 'Post created', 'post' => $post], 201);
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

        return response()->json($post);
    }

    public function update(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10',
            'category' => 'sometimes|string|in:Feature,Bug,Question,Improvement'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post->update($validator->validated());

        return response()->json(['message' => 'Post updated', 'post' => $post]);
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
}
