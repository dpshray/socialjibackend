<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "id" => $this->id,
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "last_name" => $this->last_name,
            "nick_name" => $this->nick_name,
            "email" => $this->email,
            "image" => $this->image,
            "influencer_rating" => $this->whenLoaded('influencerRatings', function(){
                $rating = $this->influencerRatings->avg('rating');
                return ($rating <=0) ? 0 : round($rating,1);
            })
        ];
    }
}
