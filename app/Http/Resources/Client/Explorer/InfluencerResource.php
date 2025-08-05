<?php

namespace App\Http\Resources\Client\Explorer;

use App\Constants\Constants;
use App\Http\Resources\Social\SocialProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfluencerResource extends JsonResource
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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'nick_name' => $this->nick_name,
            'email' => $this->email,
            'total_gigs' => $this->whenCounted('gigs'),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            'rating' => $this->whenLoaded('reviews', function(){
                $rating = $this->reviews->avg('rating');
                if ($rating) {
                    return round($rating, 1);
                }
                return 0;
            }),
            'social_profiles' => SocialProfileResource::collection($this->whenLoaded('socialProfiles')),
            'highest_price_gig' => $this->whenLoaded('gigs', function(){
                return $this->gigs->pluck('gig_pricing')->flatten()->max('pivot.price');
            }),
            'lowest_price_gig' => $this->whenLoaded('gigs', function(){
                return $this->gigs->pluck('gig_pricing')->flatten()->min('pivot.price');
            })
        ];
    }
}
