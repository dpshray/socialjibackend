<?php

namespace Database\Seeders;

use App\Constants\Constants;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandCampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::role(Constants::ROLE_BRAND)
            ->limit(10)
            ->get()
            ->map(function($user){
                $i = 0;
                $campaigns = [];
                while ($i < 10) {
                    $campaigns[] = [
                        "title" => fake()->catchPhrase(),
                        "description" => fake()->text(500),
                        "categories" => fake()->title(20),
                        "eligibility" => fake()->text(500),
                        "requirement" => fake()->text(500),
                        "price" => fake()->numberBetween(5000,25000),
                        "currency_id" => 1
                    ];
                    $i++;
                }
                $user->brandCampaigns()->createMany($campaigns);
            });
    }
}
