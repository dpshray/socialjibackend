<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;

class CampaignEntityTrustapTransaction extends Model
{
    const COMPLAIN_PERIOD_DEADLINE = 2; #IN DAYS
    const COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY = 1; #IN DAY
    
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
    
    public function getComplaintAllowedAttribute()
    {
        return $this->delivered_at && $this->complaintPeriodDeadline
            && $this->status == PaymentStatusEnum::HANDOVERED->value
            && $this->delivered_at->addHours(self::COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY)->isPast()
            && $this->complaintPeriodDeadline->gte(now());
    }
}
