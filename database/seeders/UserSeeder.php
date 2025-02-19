<?php

namespace Database\Seeders;

use App\Constants\Constants;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $users = [
        //     [
        //         'first_name' => 'admin',
        //         'email' => 'admin@gmail.com',
        //         'password' => bcrypt('testing123'),
        //         'email_verified_at' => now(),
        //     ],
        //     [
        //         'first_name' => 'brand',
        //         'email' => 'brand@gmail.com',
        //         'password' => bcrypt('testing123'),
        //         'email_verified_at' => now(),
        //     ],
        //     [
        //         'first_name' => 'influencer',
        //         'email' => 'brand@gmail.com',
        //         'password' => bcrypt('testing123'),
        //         'email_verified_at' => now(),
        //     ]
        // ];

        // DB::table('users')->insert($users);

        $admin = User::where(['first_name' => 'Admin'])->first();
        $admin->assignRole(Constants::ROLE_ADMIN);

        $brand = User::where(['first_name' => 'brand'])->first();
        $brand->assignRole(Constants::ROLE_BRAND);

        $influencer = User::where(['first_name' => 'influencer'])->first();
        $influencer->assignRole(Constants::ROLE_INFLUENCER);

    }
}
