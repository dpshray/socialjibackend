<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use App\Http\Resources\Social\SocialProfileResource;
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
                return count($SP) ? SocialProfileResource::collection($SP) : [];
            })
        ];
    }
}


// "social_site_id": 1,
// "profile_url": "https://gutmann.com/cumque-adipisci-sint-excepturi.html",
// "follower_count": 23069,
// "following_count": 21427,
// "post_count": 41968,
// "avg_like_per_post_count": 35,
// "avg_comment_per_post_count": 30,
// "follower_growth_rate_per_week": 47,
// "highest_like": 835008,
// "lowest_like": 1577