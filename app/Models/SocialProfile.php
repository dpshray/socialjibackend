<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialProfile extends Model
{
    public function socialSite(){
        return $this->belongsTo(SocialSites::class);
    }
}
