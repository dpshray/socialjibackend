<?php

namespace App\Http\Resources\Social;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $profile_url = match ($this->social_site_id) {
            1 => asset('assets/img/social/instagram.png'),
            2 => asset('assets/img/social/facebook.png'),
            3 => asset('assets/img/social/tik-tok.png'),
        };
        return [
            // 'id' => $this->id,
            // 'social_site_id' => $this->social_site_id,
            // 'profile_url' => $this->profile_url,
            'profile_url' => $profile_url,
            'follower_count' => $this->follower_count,
            'following_count' => $this->following_count,
            'post_count' => $this->post_count,
            'avg_like_per_post_count' => $this->avg_like_per_post_count,
            'avg_comment_per_post_count' => $this->avg_comment_per_post_count,
            'follower_growth_rate_per_week' => $this->follower_growth_rate_per_week,
            'highest_like' => $this->highest_like,
            'lowest_like' => $this->lowest_like,
            'social' => new SocialSiteResource($this->socialSite)
        ];
    }
}
