<?php

namespace App\Http\Resources\Payment\Campaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignInfluencerPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $bid = $this->bid;
        $campaign = $bid->campaign;
        $brand = $campaign->brand;
        return [
            'payment_id' => (int) $this->id,
            'bid_id' => (int)$bid->id,
            'price' => (float) $this->price,
            'campaign_name' => $campaign->title,
            'campaign_brand_name' => $brand->first_name.' '.$brand->last_name,
            'bidded_at' => $this->bid->created_at->format('Y/m/d'),
            'status' => $this->status
        ];
    }
}
