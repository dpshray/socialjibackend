<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pricingTiers = [
            [
                'name' => 'basic',
                'label' => 'Basic',
            ],
            [
                'name' => 'standard',
                'label' => 'Standard',
            ],
            [
                'name' => 'premium',
                'label' => 'Premium',
            ],
        ];

        DB::table('pricing_tiers')->insert($pricingTiers);
    }
}
