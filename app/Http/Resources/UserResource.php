<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use App\Http\Resources\Social\SocialProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "id" => $this->id,
            "nick_name" => $this->nick_name,
            "first_name" => $this->when(property_exists($this, 'first_name'), $this->first_name),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            "middle_name" => $this->when(property_exists($this, 'middle_name'), $this->middle_name),
            "last_name" => $this->when(property_exists($this, 'last_name'), $this->last_name),
            "email" => $this->when(property_exists($this, 'email'), $this->email),
            "roles" => $this->whenLoaded('roles', fn() => $this->getRoleNames()->first()),
            'about' => $this->when(property_exists($this, 'email'), $this->about),
            "influencer_rating" => $this->whenLoaded('brandReviews', function(){
                $rating = $this->brandReviews->avg('rating');
                return ($rating <=0) ? 0 : round($rating,1);
            }),
            'social_profiles' => $this->whenLoaded('socialProfiles', function(){
                $SP = $this->socialProfiles;
                return count($SP) ? SocialProfileResource::collection($SP) : [];
            })
        ];
    }
}