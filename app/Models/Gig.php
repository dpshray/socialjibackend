<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gig extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function gig_pricing()
    {
        return $this->belongsToMany(PricingTier::class, 'gig_pricing', 'gig_id', 'pricing_tier_id')
            ->withPivot('price', 'delivery_time', 'description')->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeCreator($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function isCreator()
    {
        return $this->user_id == auth()->id() || auth()->user()->hasRole(Constants::ROLE_ADMIN);
    }
}
