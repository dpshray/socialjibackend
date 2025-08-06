<?php

namespace App\Http\Resources\Review;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comment' => $this->comment,
            'commented_at' => $this->updated_at->diffForHumans(),
            'reviewer' => $this->whenLoaded('reviewer', function(){
                $user = $this->reviewer;
                return [
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'nick_name' => $user->nick_name,
                    'image' => $user->getFirstMediaUrl(Constants::MEDIA_USER),
                ];
            })
        ];
    }
}
