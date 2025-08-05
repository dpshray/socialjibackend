<?php

namespace App\Http\Resources\Gig;

use App\Constants\Constants;
use App\Http\Resources\Pricing\PricingCollection;
use App\Http\Resources\Pricing\PricingResource;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\UserResource;
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->when($this->category, $this->category),
            'description' => $this->when($this->description, $this->description),
            'requirements' => $this->when($this->requirements, $this->requirements),
            'features' => $this->when($this->features, $this->features),
            'published_at' => $this->when($this->published_at, $this->published_at),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_GIG)),
            'pricings' => new PricingCollection($this->whenLoaded('gig_pricing')),
            'tags' => new TagCollection($this->whenLoaded('tags')),
            'user' => new UserResource($this->whenLoaded('user')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')) ,
            'total_reviews' => $this->whenCounted('reviews'),
            'item_sold' => $this->whenCounted('noOfGigSold')
        ];
    }
}
