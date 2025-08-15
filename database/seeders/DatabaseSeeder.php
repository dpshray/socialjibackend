<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SocialSiteSeeder::class,
            PricingTierSeeder::class,
        ]);

        $currencies = [
            [
                'code'   => 'USD',
                'symbol' => '$',
                'name'   => 'United States Dollar',
            ],
            [
                'code'   => 'GBP',
                'symbol' => '£',
                'name'   => 'British Pound Sterling',
            ],
            [
                'code'   => 'EUR',
                'symbol' => '€',
                'name'   => 'Euro',
            ],
        ];
        
        if (Schema::hasTable('currencies')) {
            DB::table('currencies')->insert($currencies);
        }else{
            Log::info("currencies table does not exists");
        }
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
