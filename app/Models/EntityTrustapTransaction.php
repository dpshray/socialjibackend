<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTrustapTransaction extends Model
{
    // protected $fillable = ['delivered_at'];

    /**
     * The payment is released to the seller only 24 hours after the handover API is called.
     * We are manually setting a 48-hour complaint period for the transaction.
     * However, the COMPLAIN_PERIOD_DEADLINE is set to 1 day (24 hours) because:
     * After this 24-hour deadline, we automatically trigger the handover API via a scheduled cron job.
     * Then, 24 hours after the handover is confirmed, Trustap automatically releases the funds to the seller.
     */
    const COMPLAIN_PERIOD_DEADLINE = 2;#IN DAYS
    const COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY = 1;#IN DAY
    
    protected $guarded = [];
    
    protected function casts(): array
    {
        return [
            'complaintPeriodDeadline' => 'datetime',
            'delivered_at' => 'datetime',
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

    public function getComplaintAllowedAttribute(){
        // return $this->delivered_at->addHours(24)->isPast();
        return $this->delivered_at && $this->complaintPeriodDeadline
            && $this->delivered_at->addHours(self::COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY)->isPast()
            && $this->complaintPeriodDeadline->gte(now());
    }
}
