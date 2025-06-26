<?php

namespace App\Http\Resources;

use App\Constants\Constants;
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
            "first_name" => $this->when($this->first_name, $this->first_name),
            "middle_name" => $this->when($this->middle_name, $this->middle_name),
            "last_name" => $this->when($this->last_name, $this->last_name),
            "nick_name" => $this->nick_name,
            "email" => $this->when($this->email, $this->email),
            "roles" => $this->whenLoaded('roles', fn() => $this->getRoleNames()->first()),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            "influencer_rating" => $this->whenLoaded('influencerRatings', function(){
                $rating = $this->influencerRatings->avg('rating');
                return ($rating <=0) ? 0 : round($rating,1);
            }),
            'social_profiles' => $this->whenLoaded('socialProfiles', function(){
                $SP = $this->socialProfiles;
                return count($SP) ? $SP : [];
            })
        ];
    }
}
