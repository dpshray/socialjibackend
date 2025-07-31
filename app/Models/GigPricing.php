<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GigPricing extends Pivot
{
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function pricingTier(){
        return $this->belongsTo(PricingTier::class,'pricing_tier_id');
    }
}
