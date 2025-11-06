<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEntityTrustapTransaction extends Model
{
    protected $fillable = [
        'campaign_id',
        'bidder_id',
        'campaign_title',
        'description',
        'transactionId',
        'transactionType',
        'sellerId',
        'buyerId',
        'status',
        'price',
        'charge',
        'chargeSeller',
        'currency',
        'claimedBySeller',
        'claimedByBuyer',
        'complaintPeriodDeadline',
        'bid_id',
        'delivered_at'
    ];

    protected function casts(): array
    {
        return [
            'complaintPeriodDeadline' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    function bid() {
        return $this->belongsTo(Bid::class);
    }

    public function campaign()
    {
        return $this->hasOneThrough(Campaign::class,Bid::class,'id','id','bid_id','campaign_id');
    }
}
