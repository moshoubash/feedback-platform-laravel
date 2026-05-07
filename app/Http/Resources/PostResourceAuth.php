<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResourceAuth extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $upvotes = $this->votes->where('type', 1)->count();
        $downvotes = $this->votes->where('type', -1)->count();
        $total_votes = $upvotes - $downvotes;
        $total_votes = $total_votes <= 0 ? 0 : $total_votes;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'total_votes' => $total_votes,
            'user_voted' => $this->user_voted,
            'user_voted_type' => $this->user_voted ? $this->votes->where('user_id', auth()->user()->id)->first()->type : null,
        ];
    }
}
