<?php

namespace App\Http\Resources\Client\Explorer;

use App\Constants\Constants;
use App\Http\Resources\Social\SocialProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            'banner' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_BRAND_BANNER)),
            'about' => $this->about,
            'category_name' => $this->whenLoaded('brandCategory'),
            'rating' => $this->whenLoaded('brandRatings', function($item){
                $rating = $this->brandRatings->avg('rating');
                if ($rating) {
                    return round($rating, 1);
                }
                return 0;
            }),
            'social_profiles' => $this->whenLoaded('socialProfiles', SocialProfileResource::collection($this->socialProfiles)),
        ];
    }
}
