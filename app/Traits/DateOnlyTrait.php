<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateOnlyTrait
{
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }
}
