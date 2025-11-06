<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandCategories = [
            'Gadgets & Electronics',
            'Sportswear & Activewear',
            'Food & Drinks',
            'Fashion & Apparel',
            'Beauty & Personal Care',
            'Home Appliances',
            'Automotive',
            'Health & Wellness',
            'Finance & Banking',
            'Entertainment & Media',
            'E-commerce & Retail',
            'Technology & Software',
        ];
        foreach ($brandCategories as $brand_category) {            
            $slug = str()->slug($brand_category);
            $exists = DB::table('brand_categories')->where('slug', $slug)->exists();
            if ($exists) {
                continue;
            }
            DB::table('brand_categories')->insert([
                'name' => $brand_category,
                'slug' => $slug
            ]);
        }
    }
}
