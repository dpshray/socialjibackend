<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['rating','comment','gig_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function helpfuls(){
        return $this->hasMany(Helpful::class);
    }

    public function subReviews(){
        return $this->hasMany(SubReview::class);
    }
}
