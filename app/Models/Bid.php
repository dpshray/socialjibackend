<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = ['is_selected','detail','bid','bidder_id'];

    public function bidder(){
        return $this->belongsTo(User::class,'bidder_id');
    }

    function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    function trustapTransaction() {
        return $this->hasOne(CampaignEntityTrustapTransaction::class);
    }
}
