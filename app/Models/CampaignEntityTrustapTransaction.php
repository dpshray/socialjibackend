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
        'bid_id'
    ];

    function buyer() { #INFLUENCER
        return $this->belongsTo(User::class,'bidder_id');
    }

    function campaign() {
        return $this->belongsTo(Campaign::class,'campaign_id');
    }

    function bid() {
        return $this->belongsTo(Bid::class);
    }
}
