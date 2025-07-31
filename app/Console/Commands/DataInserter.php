<?php

namespace App\Console\Commands;

use App\Constants\Constants;
use App\Models\Gig;
use App\Models\User;
use App\Services\GigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DataInserter extends Command
{
    protected $signature = 'data:inserter';
    protected $description = 'Insert fake influencer and brand users with related data';

    public function handle()
    {
        $this->insertUser(true);   // Insert influencers
        $this->insertUser(false);  // Insert brands
    }

    private function insertUser(bool $is_influencer = true)
    {
        $total_user = 10;

        for ($x = 1; $x <= $total_user; $x++) {
            try {
                // Create user
                $user = User::create([
                    'first_name' => fake()->firstName(),
                    'middle_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'nick_name' => fake()->userName() . rand(1, 1000),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => str()->random(10),
                    'about' => fake()->text()
                ]);

                $this->info("[$x/$total_user] ✔ User created: {$user->email}");

                if ($is_influencer) {
                    // Insert social profiles
                    $socialProfiles = [];
                    for ($k = 1; $k <= 3; $k++) {
                        $socialProfiles[] = [
                            "user_id" => $user->id,
                            "social_site_id" => $k,
                            "profile_url" => fake()->url(),
                            "follower_count" => fake()->numberBetween(10000, 50000),
                            "following_count" => fake()->numberBetween(10000, 50000),
                            "post_count" => fake()->numberBetween(10000, 50000),
                            "avg_like_per_post_count" => fake()->numberBetween(25, 85),
                            "avg_comment_per_post_count" => fake()->numberBetween(25, 85),
                            "follower_growth_rate_per_week" => fake()->numberBetween(25, 85),
                            "highest_like" => fake()->numberBetween(25000, 1000000),
                            "lowest_like" => fake()->numberBetween(1000, 5000),
                        ];
                    }
                    DB::table('social_profiles')->insert($socialProfiles);
                    $this->info("✔ Social profiles inserted for user ID: {$user->id}");

                    // Insert Gigs
                    $total_gigs = 10;
                    $gigService = new GigService;

                    for ($i = 1; $i <= $total_gigs; $i++) {
                        // Insert tags
                        $tags = collect(fake()->words(rand(5, 10)))
                            ->map(fn($tag) => [
                                'user_id' => $user->id,
                                'name' => $tag . rand(1, 10)
                            ])->all();

                        DB::table('tags')->insert($tags);
                        $this->info("✔ Inserted " . count($tags) . " tags");

                        // Create gig
                        $gig = Gig::create([
                            "user_id" => $user->id,
                            "title" => fake()->catchPhrase(),
                            "category" => fake()->streetName(),
                            "description" => fake()->paragraph(),
                            "requirements" => fake()->sentence(),
                            "features" => fake()->sentence(),
                            "status" => 1,
                            "published_at" => now(),
                        ]);

                        // Assign pricing tiers using service
                        $gigService->form_data = [
                            "pricing_tier_id" => ["1", "2", "3"],
                            "price" => [
                                fake()->numberBetween(500, 1000),
                                fake()->numberBetween(5000, 10000),
                                fake()->numberBetween(100000, 300000),
                            ],
                            "delivery_time" => [now(), now(), now()],
                            "tier_description" => [fake()->text(), fake()->text(), fake()->text()],
                            "currency_id" => [rand(1,33), rand(1,33), rand(1,33)],
                            "tier_requirement" => [fake()->text(), fake()->text(), fake()->text()],
                        ];

                        $gigService->upsertPricingAndTag($gig);
                        $this->info("[$i/$total_gigs] ✔ Gig created: {$gig->title}");
                    }

                    // Assign role
                    $user->assignRole(Constants::ROLE_INFLUENCER);
                } else {
                    $user->assignRole(Constants::ROLE_BRAND);
                }
            } catch (\Throwable $e) {
                Log::error("User Insert Error: " . $e->getMessage());
                $this->error("❌ Error inserting user: " . $e->getMessage());
            }
        }

        $this->info("✔ All " . ($is_influencer ? 'influencers' : 'brands') . " inserted successfully.");
    }
}
