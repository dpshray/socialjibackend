<?php

namespace App\Http\Resources\Search;

use App\Constants\Constants;
use App\Http\Resources\Pricing\PricingCollection;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigSearchResource extends JsonResource
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
            'pricings' => $this->whenLoaded('gig_pricing', new PricingCollection($this->gig_pricing)),
            'tags' => $this->whenLoaded('tags', new TagCollection($this->tags)),
            'user' => $this->whenLoaded('user', new UserResource($this->user)),
        ];
    }
}
