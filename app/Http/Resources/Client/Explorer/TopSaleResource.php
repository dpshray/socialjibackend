<?php

namespace App\Http\Resources\Client\Explorer;

use App\Http\Resources\Gig\GigResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopSaleResource extends JsonResource
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
            'total_sold' => $this->total_sold,
            'gig_name' => $this->whenLoaded('gig', new GigResource($this->gig))
        ];
    }
}
