<?php

namespace Database\Seeders;

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
                'name' => 'instagram',
                'label' => 'Instagram',
            ],
            [
                'name' => 'facebook',
                'label' => 'Facebook',
            ],
            [
                'name' => 'tiktok',
                'label' => 'Tiktok',
            ],
        ];

        DB::table('social_sites')->insert($socialSites);
    }
}
