<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
        } else {
            Log::info("currencies table does not exists");
        }
    }
}
