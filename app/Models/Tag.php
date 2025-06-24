<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tag extends Model
{
    protected $guarded = [];

    public function gigs()
    {
        return $this->belongsToMany(Gig::class);
    }

    public function scopeCreator($query)
    {
        return $query->where('user_id', Auth::id());
    }
}
