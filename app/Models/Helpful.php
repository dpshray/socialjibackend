<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Helpful extends Model
{
    protected $fillable = ['vote','user_id'];
}
