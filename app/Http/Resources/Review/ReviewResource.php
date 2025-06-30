<?php

namespace App\Http\Resources\Review;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'review_id' => $this->id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'reviewed_at' => $this->updated_at->diffForHumans(),
            'helpfuls' => $this->whenLoaded('helpfuls', function(){
                $upvote = $downvote = 0;
                $this->helpfuls->each(function($i,$k) use(&$upvote, &$downvote){
                    ($i->vote == 1) ? $upvote++ : $downvote++;
                });
                return compact('upvote','downvote');
            }),
            'reviewer' => $this->whenLoaded('user', new UserResource($this->user))
        ];
    }
}
