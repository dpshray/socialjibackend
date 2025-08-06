<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandCategory extends Model
{
    public function brand(){
        return $this->hasMany(User::class);
    }
}
