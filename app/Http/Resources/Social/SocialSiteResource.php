<?php

namespace App\Http\Resources\Social;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialSiteResource extends JsonResource
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
            // 'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'logo' => $this->whenLoaded('media',  fn() => $this->getFirstMediaUrl(Constants::MEDIA_SOCIAL))
        ];
    }
}
