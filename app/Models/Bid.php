<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = ['detail','bid','bidder_id'];

    public function bidder(){
        return $this->belongsTo(User::class,'bidder_id');
    }
}
