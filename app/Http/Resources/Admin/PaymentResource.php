<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'price' => $this->price,
            'trustap_charge' => $this->charge,
            'currency' => $this->currency,
            'gig' => new GigResource($this->whenLoaded('gig')),
            'price_tier' => new AdminPaymentPricingTierResource($this->whenLoaded('pricing')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' =>  new UserResource($this->whenLoaded('seller')),
            'status' => $this->status,
            'transaction_date' => $this->created_at,
        ];
    }
}
