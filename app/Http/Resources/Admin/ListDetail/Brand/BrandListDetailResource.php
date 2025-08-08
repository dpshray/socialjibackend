<?php

namespace App\Http\Resources\Admin\ListDetail\Brand;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandListDetailResource extends JsonResource
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
            'about' => $this->about,
            'total_reviews_from_brand_count' => $this->user_reviews_count,
            'brand_category' => $this->whenLoaded('brandCategory'),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            'social_profiles' => $this->whenLoaded('socialProfiles'),
            'brand_rating' => $this->whenLoaded('brandRatings', function(){
                return round($this->brandRatings->avg('rating'),1);
            }),
            'total_gigs_brought' => $this->userTrustapMetadata ? $this->userTrustapMetadata->buyerTransactions->count() : 0,
            'total_transaction_amount' => $this->userTrustapMetadata ? $this->userTrustapMetadata->buyerTransactions->sum(fn($transaction) => $transaction->price + $transaction->charge) : 0
        ];
    }
}
