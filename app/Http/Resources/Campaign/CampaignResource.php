<?php

namespace App\Http\Resources\Campaign;

use App\Constants\Constants;
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
        // return parent::toArray($request);
        // $tags = [];
        // foreach ($this->tags as $tag) {
        //     $tags[] = [
        //         'id' => $tag['id'],
        //         'name' => $tag['name']
        //     ];
        // }
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "categories" => $this->categories,
            "eligibility" => $this->eligibility,
            "requirement" => $this->requirement,
            "price" => $this->price,
            "tags" => $this->whenLoaded('tags', fn() => $this->tags->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_CAMPAIGN)),
        ];
    }
}
