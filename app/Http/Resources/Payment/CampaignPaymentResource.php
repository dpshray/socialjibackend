<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignPaymentResource extends JsonResource
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
            'bid_id' => (int)$this->bid->id,
            'price' => (float) $this->price,
            'campaign_name' => $this->campaign->title,
            'bidder_name' => $this->bid->bidder->first_name.' '. $this->bid->bidder->last_name,
            'bidded_at' => $this->bid->created_at->format('Y/m/d'),
            'status' => $this->status
        ];
    }
}
