<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubReview extends Model
{
    protected $fillable = ['comment', 'user_id'];

    public function reviewer(){
        return $this->belongsTo(User::class,'user_id');
    }
}
