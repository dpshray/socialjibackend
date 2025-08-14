<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialProfile extends Model
{
    protected $fillable = [
        'user_id',
        'metadata',
        'social_email',
        'social_site_id'
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function socialSite(){
        return $this->belongsTo(SocialSites::class);
    }
}
