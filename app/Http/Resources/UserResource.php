<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\Social\SocialProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\TextUI\Configuration\Constant;

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
            "first_name" => $this->when(!is_null($this->first_name), $this->first_name),
            "middle_name" => $this->when(!is_null($this->middle_name), $this->middle_name),
            "last_name" => $this->when(!is_null($this->last_name), $this->last_name),
            "email" => $this->when(!is_null($this->email), $this->email),
            'about' => $this->when(!is_null($this->about), $this->about),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Constants::MEDIA_USER)),
            "roles" => $this->whenLoaded('roles', fn() => $this->getRoleNames()->first()),
            "influencer_rating" => $this->whenLoaded('brandReviews', function(){
                $rating = $this->brandReviews->avg('rating');
                return ($rating <=0) ? 0 : round($rating,1);
            }),
            'social_profiles' => $this->whenLoaded('socialProfiles', function(){
                $SP = $this->socialProfiles;
                return count($SP) ? SocialProfileResource::collection($SP) : [];
            }),
            'gig' => $this->whenLoaded('gigs', function(){
                return [
                    'total' => $this->gigs->count(),
                    'published' => $this->gigs->where('status',1)->count(),
                    'gigs_sold_count' => rand(100,1000),
                    'top_selling_gig' => new GigResource($this->gigs->first()),
                ];
            }),
            'is_full_user' => $this->whenLoaded('userTrustapMetadata', function(){
                return ($this->userTrustapMetadata->trustapUserType == Constants::TRUSTAP_FULL_USER) ? 1 : 0;
            }) 
        ];
    }
}