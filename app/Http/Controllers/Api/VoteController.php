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

        $existingVote = Vote::where('user_id', auth()->id())->where('post_id', $post->id)->first();

        if ($existingVote && $existingVote->type === $type) {
            $existingVote->delete();
            return response()->json([
                'message' => 'Vote removed successfully',
                'vote' => null,
                'upvotes' => $post->upvotes,
                'downvotes' => $post->downvotes,
                'post_votes' => $post->total_votes
            ]);
        }

        $vote = Vote::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ],
            ['type' => $type]
        );

        // Recalculate votes count
        $upvotes = Vote::where('post_id', $post->id)->where('type', 1)->count();
        $downvotes = Vote::where('post_id', $post->id)->where('type', -1)->count();

        return response()->json([
            'message' => 'Vote cast successfully',
            'vote' => $vote,
            'upvotes' => (int) $upvotes,
            'downvotes' => (int) $downvotes,
            'post_votes' => $upvotes - $downvotes
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
