<?php

namespace App\Http\Resources\Social;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinimalSocialProfileResource extends JsonResource
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
            "follower_count" => $this->follower_count,
            "social_site" => [
                "name" => $this->socialSite->name,
                "label" => $this->socialSite->label,
            ]
        ];
    }
}
