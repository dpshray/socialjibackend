<?php

namespace App\Http\Resources\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        /* 
                    {
                "id": 1,
                "name": "basic",
                "label": "Basic",
                "created_at": null,
                "updated_at": null,
                "deleted_at": null,
                "pivot": {
                    "gig_id": 68,
                    "pricing_tier_id": 1,
                    "price": "5000.00",
                    "delivery_time": "2025-06-22 21:00:00",
                    "description": "tier updated description for price tier id 3",
                    "requirement": "some updated  requirememnts for tier id",
                    "currency_id": 1,
                    "created_at": "2025-06-24T07:47:37.000000Z",
                    "updated_at": "2025-06-24T07:47:56.000000Z"
                }
            }, */
        return [
            "id" => $this->id,
            "label" => $this->label,
            "price" => $this->pivot->price,
            "delivery_time" => $this->pivot->delivery_time,
            "description" => $this->pivot->description,
            "requirement" => $this->pivot->requirement,
            "currency" => cache('currencies')->firstWhere('id',$this->pivot->currency_id)
        ];
    }
}
