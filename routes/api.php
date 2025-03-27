<?php

use App\Http\Controllers\Api\v1\Influencer\GigController;
use App\Http\Controllers\Api\v1\Influencer\InfluencerController;
use App\Http\Controllers\Api\v1\Influencer\PricingTierController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\InfluencerRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware([JwtMiddleware::class, VerifyEmail::class])->group(function () {
    Route::middleware([BrandRole::class])->group(function () {
        Route::get('influencer/search/{keyword}', [InfluencerController::class, 'findInfluencers'])->name('find');
        Route::get('gig/search/{keyword}', [GigController::class, 'search'])->name('gig.search');
        Route::get('gig/search-by-tag/{keyword}', [GigController::class, 'searchByTag'])->name('gig.searchByTag');
    });

    Route::middleware([InfluencerRole::class])->prefix('influencer')->name('influencer.')->group(function () {
        Route::apiResource('gig', GigController::class);
        Route::apiResource('tag', TagController::class)->except(['update', 'delete']);

        Route::get('pricing-tier', [PricingTierController::class, 'index'])->name('pricing_tier.index');

        Route::get('tag/search/{keyword}', [TagController::class, 'search'])->name('tag.search');

    });
});
