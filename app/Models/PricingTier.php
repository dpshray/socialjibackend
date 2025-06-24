<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model {
    protected $fillable = ['pricing_tier_id'];

    public function currency(){
        return $this->belongsTo(Currency::class);
    }
}
