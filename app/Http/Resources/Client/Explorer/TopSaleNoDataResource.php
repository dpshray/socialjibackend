<?php

namespace App\Http\Resources\Client\Explorer;

use App\Http\Resources\Gig\GigResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopSaleNoDataResource extends JsonResource
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
            'total_sold' => 0,
            'gig_name' => new GigResource($this)
        ];
    }
}
