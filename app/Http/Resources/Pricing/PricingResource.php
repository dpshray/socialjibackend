<?php

namespace App\Http\Resources\Pricing;

use Carbon\Carbon;
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
        return [
            "id" => $this->id,
            "label" => $this->label,
            "price" => $this->pivot->price,
            "delivery_time" => Carbon::parse($this->pivot->delivery_time)->format('d/m/Y'),
            "description" => $this->pivot->description,
            "requirement" => $this->pivot->requirement,
            "currency" => cache('currencies')->firstWhere('id',$this->pivot->currency_id)
        ];
    }
}
