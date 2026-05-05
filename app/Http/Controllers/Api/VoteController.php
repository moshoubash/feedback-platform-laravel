<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function vote(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        $type = $request->type === 'up' ? 1 : -1;

        $vote = Vote::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ],
            ['type' => $type]
        );

        // Recalculate votes count
        $counts = Vote::where('post_id', $post->id)
            ->selectRaw('SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) as upvotes')
            ->selectRaw('SUM(CASE WHEN type = -1 THEN 1 ELSE 0 END) as downvotes')
            ->first();

        return response()->json([
            'message' => 'Vote cast successfully',
            'vote' => $vote,
            'upvotes' => (int) $counts->upvotes,
            'downvotes' => (int) $counts->downvotes
        ]);
    }

    public function upvote($postId)
    {
        return $this->vote(request()->merge(['type' => 'up']), $postId);
    }

    public function downvote($postId)
    {
        return $this->vote(request()->merge(['type' => 'down']), $postId);
    }
}
