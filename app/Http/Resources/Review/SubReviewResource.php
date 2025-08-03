<?php

namespace App\Http\Resources\Review;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* 
                    {
                "id": 2,
                "review_id": 20,
                "user_id": 2,
                "comment": "this is some sub comment",
                "created_at": "2025-08-03T14:23:48.000000Z",
                "updated_at": "2025-08-03T14:23:48.000000Z",
                "deleted_at": null,
                "reviewer": {
                    "id": 2,
                    "brand_category_id": 10,
                    "first_name": "brand",
                    "middle_name": null,
                    "last_name": null,
                    "nick_name": "brandon",
                    "email": "brand@gmail.com",
                    "about": null,
                    "email_verified_at": "2025-06-30T09:05:13.000000Z",
                    "provider": null,
                    "image": null,
                    "phone": null,
                    "address": null,
                    "status": null,
                    "created_at": "2025-08-03",
                    "updated_at": "2025-08-01",
                    "deleted_at": null
                }
            }, */
        // return parent::toArray($request);
        return [
            'comment' => $this->comment,
            'commented_at' => $this->updated_at->diffForHumans(),
            'reviewer' => $this->whenLoaded('reviewer', function(){
                $user = $this->reviewer;
                return [
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'nick_name' => $user->nick_name,
                    'image' => $user->getFirstMediaUrl(Constants::MEDIA_USER),
                ];
            })
        ];
    }
}
