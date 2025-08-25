<?php

namespace App\Http\Resources\Campaign;

use App\Constants\Constants;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            "title" => $this->title,
            "description" => $this->description,
            "categories" => $this->categories,
            "eligibility" => $this->eligibility,
            "requirement" => $this->requirement,
            "price" => $this->price,
            "brand" => new UserResource($this->whenLoaded('brand')),
            "tags" => $this->whenLoaded('tags', fn() => $this->tags->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_CAMPAIGN)),
        ];
    }
}
