<?php

namespace App\Http\Resources\Review;

use App\Constants\Constants;
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
                $upvote = $this->helpfuls->where('vote',1)->count();
                $downvote = $this->helpfuls->where('vote',0)->count(); 
                return compact('upvote','downvote');
            }),
            'reviewer' => $this->whenLoaded('user', function(){
                $reviewer = $this->user;
                return [
                    'first_name' => $reviewer->first_name,
                    'middle_name' => $reviewer->middle_name,
                    'last_name' => $reviewer->last_name,
                    'nick_name' => $reviewer->nick_name
                ];
            }),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
        ];
    }
}
