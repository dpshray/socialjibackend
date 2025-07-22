<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTrustapTransaction extends Model
{
    
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
