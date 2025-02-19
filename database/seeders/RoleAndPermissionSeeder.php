<?php

namespace Database\Seeders;

use App\Constants\Constants;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => Constants::ROLE_ADMIN]);
        $brandRole = Role::create(['name' => Constants::ROLE_BRAND]);
        $influencerRole = Role::create(['name' => Constants::ROLE_INFLUENCER]);

    }
}
