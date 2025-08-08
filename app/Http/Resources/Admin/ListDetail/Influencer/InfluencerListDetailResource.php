<?php

namespace App\Http\Resources\Admin\ListDetail\Influencer;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfluencerListDetailResource extends JsonResource
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
            'joined_date' => $this->email_verified_at,
            'total_reviews_from_influencer_count' => $this->gig_reviews_count,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            'social_profiles' => $this->whenLoaded('socialProfiles'),
            "influencer_rating" => $this->whenLoaded('gigReviews', function () {
                $rating = $this->gigReviews->avg('rating');
                return ($rating <= 0) ? 0 : round($rating, 1);
            }),
            'total_gigs_count' => $this->gigs_count,
            'total_gigs_sold' => $this->userTrustapMetadata ? $this->userTrustapMetadata->sellerTransactions->count() : 0,
            'total_transaction_amount' => $this->userTrustapMetadata ? ($this->userTrustapMetadata->sellerTransactions->sum(fn($transaction) => $transaction->price + $transaction->charge)) : 0
        ];
    }
}
