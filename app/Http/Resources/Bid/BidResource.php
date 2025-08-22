<?php

namespace App\Http\Resources\Bid;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "campaign_id" => $this->campaign_id,
            "detail" => $this->detail,
            "bid" => $this->bid,
            "bidder" => new UserResource($this->whenLoaded('bidder'))
        ];
    }
}
