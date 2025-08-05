<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* {
            "id": 162,
            "gig_id": 706,
            "gig_pricing_id": 2115,
            "gig_title": "Automated eco-centric knowledgebase",
            "description": "some buying description",
            "transactionId": 30509,
            "transactionType": "f2f",
            "sellerId": "1-29aa848b-7a0b-4095-9973-13041dfee2d7",
            "buyerId": "1-4ad47ebf-a286-44c9-bf60-6fc005e28243",
            "status": "handovered",
            "price": "714.00",
            "charge": "32.22",
            "chargeSeller": "0.00",
            "currency": "usd",
            "claimedBySeller": 0,
            "claimedByBuyer": 0,
            "delivered_at": "2025-07-24T11:36:10.000000Z",
            "complaintPeriodDeadline": "2025-07-25T11:36:10.000000Z",
            "created_at": "2025-08-01T11:33:14.000000Z",
            "updated_at": "2025-08-01T11:36:18.000000Z",
            "deleted_at": null,
            "gig": {
                "id": 706,
                "user_id": 4,
                "title": "Automated eco-centric knowledgebase",
                "category": "Nathaniel Gardens",
                "description": "Odit omnis pariatur ea rem. A molestiae possimus eveniet vitae. Autem culpa et tenetur sit itaque error.",
                "requirements": "Optio non esse laboriosam at ut.",
                "features": "Est aspernatur natus et ut explicabo.",
                "image": null,
                "status": 1,
                "verified_at": null,
                "published_at": "2025-07-31 14:43:47",
                "created_at": "2025-07-31T08:58:47.000000Z",
                "updated_at": "2025-07-31T08:58:47.000000Z",
                "deleted_at": null
            },
            "pricing": {
                "id": 2115,
                "gig_id": 706,
                "pricing_tier_id": 1,
                "price": "714.00",
                "currency_id": 33,
                "delivery_time": "2025-07-31 14:43:47",
                "description": "A voluptas aut aut maiores omnis. Nihil rerum libero commodi veritatis labore. Iusto ut veniam cumque nam perferendis reiciendis ipsum illo. Earum facilis ratione odit explicabo consectetur quia.",
                "requirement": "Eos fugiat facilis blanditiis et. Qui accusamus dolorem vel aliquam quae iure error qui.",
                "created_at": "2025-07-31T08:58:47.000000Z",
                "updated_at": "2025-07-31T08:58:47.000000Z",
                "deleted_at": null,
                "pricing_tier": {
                    "id": 1,
                    "name": "basic",
                    "label": "Basic",
                    "created_at": null,
                    "updated_at": null,
                    "deleted_at": null
                }
            },
            "buyer": {
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
                "created_at": "2025-08-05",
                "updated_at": "2025-08-01",
                "deleted_at": null,
                "laravel_through_key": "1-4ad47ebf-a286-44c9-bf60-6fc005e28243"
            },
            "seller": {
                "id": 4,
                "brand_category_id": null,
                "first_name": "Influencer",
                "middle_name": "",
                "last_name": "Goldin",
                "nick_name": "oshanahan696",
                "email": "influencer@gmail.com",
                "about": "Doloremque doloribus quos fuga voluptatibus reprehenderit harum. Aperiam in labore deserunt consectetur aperiam impedit velit. Distinctio molestiae sunt minima qui cumque voluptas enim.",
                "email_verified_at": "2025-06-30T09:05:18.000000Z",
                "provider": null,
                "image": null,
                "phone": null,
                "address": null,
                "status": null,
                "created_at": "2025-06-30",
                "updated_at": "2025-06-30",
                "deleted_at": null,
                "laravel_through_key": "1-29aa848b-7a0b-4095-9973-13041dfee2d7"
            } */
        // gig name
        // price 
        // price tier
        // buyer 
        // seller
        // gig image
        // transaction date
        // trustap charge
        // currency
        // status
        // return parent::toArray($request);
        return [
            'price' => $this->price,
            'trustap_charge' => $this->charge,
            'currency' => $this->currency,
            'gig' => new GigResource($this->whenLoaded('gig')),
            'price_tier' => new AdminPaymentPricingTierResource($this->whenLoaded('pricing')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' =>  new UserResource($this->whenLoaded('seller')),
            'status' => $this->status,
            'transaction_date' => $this->created_at,
        ];
    }
}
