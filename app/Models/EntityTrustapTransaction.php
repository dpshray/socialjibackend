<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTrustapTransaction extends Model
{
    /**
     * The payment is released to the seller only 24 hours after the handover API is called.
     * We are manually setting a 48-hour complaint period for the transaction.
     * However, the COMPLAIN_PERIOD_DEADLINE is set to 1 day (24 hours) because:
     * After this 24-hour deadline, we automatically trigger the handover API via a scheduled cron job.
     * Then, 24 hours after the handover is confirmed, Trustap automatically releases the funds to the seller.
    */
    const COMPLAIN_PERIOD_DEADLINE = 1;#IN DAYS
    
    protected $guarded = [];
    
    protected function casts(): array
    {
        return [
            'complaintPeriodDeadline' => 'datetime'
        ];
    }

    public function gig(){
        return $this->belongsTo(Gig::class);
    }

    public function pricing(){
        return $this->belongsTo(PricingTier::class, 'gig_pricing_id');
    }
    
    public function buyer(){
        return $this->hasOneThrough(
            User::class,                    
            UserTrustapMetadata::class,     
            'trustapGuestUserId',           
            'id',                           
            'buyerId',                      
            'user_id'                       
        );
    }    
    
    public function seller(){
        return $this->hasOneThrough(
            User::class,                    
            UserTrustapMetadata::class,     
            'trustapGuestUserId',           
            'id',                           
            'sellerId',                      
            'user_id'                       
        );
    }
}
