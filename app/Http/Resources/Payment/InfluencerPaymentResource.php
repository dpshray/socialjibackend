<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfluencerPaymentResource extends JsonResource
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
            "status" => $this->status,
            "price" => $this->price,
            "charge" => $this->charge,
            "chargeSeller" => $this->chargeSeller,
            "currency" => $this->currency,
            "claimedBySeller" => $this->claimedBySeller,
            "claimedByBuyer" => $this->claimedByBuyer,
            "complaintPeriodDeadline" => $this->complaintPeriodDeadline,
            'gig' => $this->whenLoaded('gig', new BrandGigPaymentResource($this->gig)),
            'pricing_tier' => $this->whenLoaded('pricing'),
            'buyer' => $this->whenLoaded('buyer')
        ];
    }
}
