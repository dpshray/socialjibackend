<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTrustapTransaction extends Model
{
    protected $guarded = [];

    public function gig(){
        return $this->belongsTo(Gig::class);
    }

    public function pricing(){
        return $this->belongsTo(PricingTier::class, 'gig_pricing_id');
    }
    
    public function buyer(){
        return $this->hasManyThrough(
            User::class,                    
            UserTrustapMetadata::class,     
            'trustapGuestUserId',           
            'id',                           
            'buyerId',                      
            'user_id'                       
        );
    }
}
