<?php

namespace App\Http\Resources\Gig;

use App\Http\Resources\Pricing\PricingCollection;
use App\Http\Resources\Pricing\PricingResource;
use App\Http\Resources\Tag\TagCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigResource extends JsonResource
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
        "id": 68,
        "user_id": 3,
        "title": "Updted gigs",
        "category": "An updated gig category",
        "description": "some updated gig description here",
        "requirements": "some updated gigs requirements one",
        "features": "some updated gigs features here",
        "image": null,
        "status": 0,
        "published_at": "2025-06-12 20:00:10",
        "created_at": "2025-06-24T07:34:12.000000Z",
        "updated_at": "2025-06-24T07:51:51.000000Z",
        "deleted_at": null,
        */
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->when($this->category, $this->category),
            'description' => $this->when($this->description, $this->description),
            'requirements' => $this->when($this->requirements, $this->requirements),
            'features' => $this->when($this->features, $this->features),
            'image' => $this->when($this->image, $this->image),
            'published_at' => $this->when($this->published_at, $this->published_at),
            'pricings' => $this->whenLoaded('gig_pricing', new PricingCollection($this->gig_pricing)),
            'tags' => $this->whenLoaded('tags', new TagCollection($this->tags)),
        ];
    }
}
