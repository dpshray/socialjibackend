<?php

namespace Database\Seeders;

use App\Constants\Constants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socialSites = [
            [
                'name' => strtolower(Constants::INSTAGRAM_LABEL),
                'label' => Constants::INSTAGRAM_LABEL,
            ],
            [
                'name' => strtolower(Constants::FACEBOOK_LABEL),
                'label' => Constants::FACEBOOK_LABEL,
            ],
            [
                'name' => strtolower(Constants::TIKTOK_LABEL),
                'label' => Constants::TIKTOK_LABEL,
            ],
        ];

        DB::table('social_sites')->insert($socialSites);
    }
}
