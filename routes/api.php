<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Brand\BrandController;
use App\Http\Controllers\Api\v1\Client\ClientDashboardController;
use App\Http\Controllers\Api\v1\CurrencyController;
use App\Http\Controllers\Api\v1\Influencer\GigController;
use App\Http\Controllers\Api\v1\Influencer\InfluencerController;
use App\Http\Controllers\Api\v1\Influencer\PricingTierController;
use App\Http\Controllers\Api\v1\Influencer\InfluencerCampaignController;
use App\Http\Controllers\Api\v1\Review\GigReviewController;
use App\Http\Controllers\Api\v1\Review\SubReviewController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\BrandInfluencerRole;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\InfluencerRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

require __DIR__.'/auth.php';
require __DIR__.'/payment.php';
require __DIR__.'/admin.php';
require __DIR__.'/social_status_fetcher.php';
require __DIR__.'/campaign.php';

Route::prefix('client')->group(function(){
    Route::controller(ClientDashboardController::class)->group(function(){
        Route::prefix('explorer')->group(function(){
            Route::get('influencer', 'influencerExplorer');
            Route::get('brand', 'brandExplorer');
            Route::get('top_sales', 'topSalesExplorer');
        });
        Route::prefix('insights')->group(function(){
            Route::get('brands-by-category', 'fetchBrandsByCategory');
            Route::get('top-brands', 'fetchTopBrands');
            Route::get('top-influencers', 'fetchTopInfluencers');
            Route::get('new-influencers-monthly', 'fetchNewInfluencerRegistrations');
            Route::get('new-brands-monthly', 'fetchNewBrandRegistrations');
            Route::get('gig-by-month', 'newGigsByMonth');
            Route::get('insight-card', 'insightWidgetInfo');
        });
    });
});

Route::middleware([JwtMiddleware::class, VerifyEmail::class])->group(function () {
    // Route::get('currency', [CurrencyController::class, 'index']);
    Route::apiResource('currency', CurrencyController::class);
    Route::get('pricing-tier', [PricingTierController::class, 'index'])->name('pricing_tier.index');
    Route::middleware([BrandRole::class])->group(function () {
        Route::get('influencer/search/{keyword}', [InfluencerController::class, 'findInfluencers'])->name('find');
        Route::get('search/influencer', [BrandController::class, 'creatorSearch']);
        Route::get('search/tag', [BrandController::class, 'searchTag']);
        Route::get('fetch-auth-user/{user}', [AuthController::class, 'fetchUserProfile']);
        // Route::get('gig/search-by-tag/{keyword}', [GigController::class, 'searchByTag'])->name('gig.searchByTag');
    });
    Route::middleware([BrandInfluencerRole::class])->group(function(){
        Route::get('campaign-list', [InfluencerCampaignController::class, 'campaignList']);
        Route::get('gig/search', [GigController::class, 'search'])->name('gig.search');
        Route::get('influencer/gig/{gig}', [GigController::class, 'show']);
        Route::controller(GigReviewController::class)->group(function(){
            Route::post('save-review/{gig}', 'storeGigReview'); #influencer brand
            Route::post('update-gig-review/{review}', 'gigReviewUpdater'); #influencer brand
            Route::get('list-gig-review/{gig}', 'fetchGigReviews'); #influencer brand
            Route::delete('delete-review/{review}', 'gigReviewRemover');#influencer brand
            Route::post('review-helpful/{review}', 'markReviewHelpful'); #influencer brand
        });
        Route::apiResource('review.sub-review', SubReviewController::class)->shallow()->except('show');
    });

    Route::middleware([BrandInfluencerRole::class])->prefix('influencer')->name('influencer.')->group(function () {
        Route::apiResource('gig', GigController::class)->except(['show']);
        Route::get('tag/search', [TagController::class, 'search'])->name('tag.search');
        Route::apiResource('tag', TagController::class)->except(['show']);
    });
});
