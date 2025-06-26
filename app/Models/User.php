<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Constants\Constants;
use App\Notifications\Auth\EmailVerify;
use App\Traits\AuthTrait;
use App\Traits\DateOnlyTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPUnit\TextUI\Configuration\Constant;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia, MediaCollections\Models\Media};

class User extends Authenticatable implements JWTSubject, MustVerifyEmail, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use AuthTrait, DateOnlyTrait, HasFactory, HasRoles, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['first_name', 'middle_name', 'last_name', 'nick_name', 'email', 'password', 'email_verified_at', 'provider', 'provider_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerify);
    }

    public function scopeVerifiedEmail($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('status');
    }

    public function userTrustapMetadata()
    {
        return $this->hasOne(UserTrustapMetadata::class);
    }

    public function isBrand()
    {
        return $this->hasRole(Constants::ROLE_BRAND);
    }

    public function isInfluencer()
    {
        return $this->hasRole(Constants::ROLE_INFLUENCER);
    }

    public function tags(){
        return $this->hasMany(Tag::class);
    }
    /**
     * 
     */
    public function influencerRatings(){
        return $this->hasMany(Rating::class,'influencer_id');
    }
    /**
     * if used by influencer, gets all ratings of their gigs
    */
    public function brandRatings(){
        return $this->hasMany(Rating::class,'brand_id','id');
    }

    public function socialProfiles(){
        return $this->hasMany(SocialProfile::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(Constants::MEDIA_USER)
            ->useFallbackUrl(asset('assets/img/user-default.png'))
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
