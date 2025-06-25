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

        if (Schema::hasTable('currencies')) {
            DB::table('currencies')->insert([
                ['name' => 'nepali rupee', 'code' => 'NPR', 'symbol' => 'रु'],
                ['name' => 'us dollar', 'code' => 'USD', 'symbol' => '$']
            ]);
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
