<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '$2y$10$pVcZge4Pp4mpFIDFKHF96uJZFGU04nKL0L6kBX4wwWjtuHB4mZULi',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'brand',
                'email' => 'brand@gmail.com',
                'password' => '$2y$10$pVcZge4Pp4mpFIDFKHF96uJZFGU04nKL0L6kBX4wwWjtuHB4mZULi',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'influencer',
                'email' => 'brand@gmail.com',
                'password' => '$2y$10$pVcZge4Pp4mpFIDFKHF96uJZFGU04nKL0L6kBX4wwWjtuHB4mZULi',
                'email_verified_at' => now(),
            ]
        ];

        DB::table('users')->insert($users);
    }
}
