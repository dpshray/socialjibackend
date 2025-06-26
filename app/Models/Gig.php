<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia, MediaCollections\Models\Media};

class Gig extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $perPage = 12;

    protected $guarded = [];

    public function gig_pricing()
    {
        return $this->belongsToMany(PricingTier::class, 'gig_pricing', 'gig_id', 'pricing_tier_id')
            ->withPivot('price', 'delivery_time', 'description', 'requirement', 'currency_id')->withTimestamps();
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(Constants::MEDIA_GIG)
            ->useFallbackUrl(asset('assets/img/default.jpg'))
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(200)
                    ->height(200)
                    ->nonQueued(); #included this since we are not queueing conversions
            });
    }
}
