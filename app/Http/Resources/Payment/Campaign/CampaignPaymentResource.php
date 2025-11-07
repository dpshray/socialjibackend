<?php

namespace App\Http\Resources\Payment\Campaign;

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
        $trustap_transaction = $this->trustapTransaction;
        return [
            'payment_id' => $trustap_transaction ? $trustap_transaction->id : null,
            'bid_id' => (int)$this->id,
            'price' => (float) $this->bid,
            'campaign_name' => $this->campaign->title,
            'bidder_name' => $this->bidder->first_name.' '. $this->bidder->last_name,
            'bidded_at' => $this->created_at->format('Y/m/d'),
            'status' => $trustap_transaction ? $trustap_transaction->status : null,
            'complaint_allowed' => $trustap_transaction ? $trustap_transaction->complaint_allowed : null
        ];
    }
}
