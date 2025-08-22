<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia, MediaCollections\Models\Media};
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        "title",
        "description",
        "categories",
        "eligibility",
        "requirement",
        "price",
        "image"
    ];

    public function bids(){
        return $this->hasMany(Bid::class)->latest();
    }

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function brand(){
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(Constants::MEDIA_CAMPAIGN)
            ->useFallbackUrl(asset('assets/img/campaign-default.png'))
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
