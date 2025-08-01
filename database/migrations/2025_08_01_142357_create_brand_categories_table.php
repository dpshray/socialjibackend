<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_categories', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name');
            $table->string('slug');
        });
        $brandCategories = [
            ['slug' => 'gadgets_electronics', 'name' =>'Gadgets & Electronics'],
            ['slug' => 'sportswear_activewear', 'name' =>'Sportswear & Activewear'],
            ['slug' => 'food_drinks', 'name' =>'Food & Drinks'],
            ['slug' => 'fashion_apparel', 'name' =>'Fashion & Apparel'],
            ['slug' => 'beauty_personal_care', 'name' =>'Beauty & Personal Care'],
            ['slug' => 'home_appliances', 'name' =>'Home Appliances'],
            ['slug' => 'automotive', 'name' =>'Automotive'],
            ['slug' => 'health_wellness', 'name' =>'Health & Wellness'],
            ['slug' => 'finance_banking', 'name' =>'Finance & Banking'],
            ['slug' => 'entertainment_media', 'name' =>'Entertainment & Media'],
            ['slug' => 'ecommerce_retail', 'name' =>'E-commerce & Retail'],
            ['slug' => 'technology_software', 'name' =>'Technology & Software'],
        ];
        DB::table('brand_categories')->insert($brandCategories);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_categories');
    }
};
