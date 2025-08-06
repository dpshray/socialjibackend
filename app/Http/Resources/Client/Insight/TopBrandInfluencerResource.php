<?php

namespace App\Http\Resources\Client\Insight;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopBrandInfluencerResource extends JsonResource
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
            'address' => $this->address,
            'email_verified_at' => $this->email_verified_at,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            'brand' => $this->whenLoaded('brandCategory'),
            'socialProfiles' => $this->whenLoaded('socialProfiles'),
            "influencer_rating" => $this->whenLoaded('gigReviews', function () {
                $rating = $this->gigReviews->avg('rating');
                return ($rating <= 0) ? 0 : round($rating, 1);
            }),
        ];
    }
}
