<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Database\Eloquent\Model;

class UserTrustapMetadata extends Model
{
    public const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'trustapGuestUserId',
        'trustapFullUserId',
        'trustapFullUserEmail',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTrustapUserIdAttribute()
    {
        return !empty($this->trustapFullUserId) ? $this->trustapFullUserId : $this->trustapGuestUserId;
    }

    public function getTrustapUserTypeAttribute()
    {
        return $this->trustapFullUserId
            ? Constants::TRUSTAP_FULL_USER
            : Constants::TRUSTAP_GUEST_USER;
    }

    #used in buyer query is user type is guest_user
    public function guestUserTransactions(){
        return $this->hasMany(EntityTrustapTransaction::class, 'buyerId', 'trustapGuestUserId');
    }

    #used in buyer query is user type is full_user
    public function fullUserTransactions(){
        return $this->hasMany(EntityTrustapTransaction::class, 'buyerId', 'trustapFullUserId');
    }
}
