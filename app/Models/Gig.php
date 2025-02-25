<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $guarded = [];

    public function gig_pricing()
    {
        return $this->belongsToMany(PricingTier::class, 'gig_pricing', 'gig_id', 'pricing_tier_id')
            ->withPivot('price', 'delivery_time', 'description')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function isCreator()
    {
        return $this->user_id == auth()->id();
    }

}
