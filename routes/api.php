<?php

use App\Http\Controllers\Api\v1\CurrencyController;
use App\Http\Controllers\Api\v1\Influencer\GigController;
use App\Http\Controllers\Api\v1\Influencer\InfluencerController;
use App\Http\Controllers\Api\v1\Influencer\PricingTierController;
use App\Http\Controllers\Api\v1\TrustapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\AdminRole;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\InfluencerRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware([JwtMiddleware::class, VerifyEmail::class])->group(function () {
    Route::get('currency', [CurrencyController::class, 'index']);
    Route::get('pricing-tier', [PricingTierController::class, 'index'])->name('pricing_tier.index');
    Route::middleware([BrandRole::class])->group(function () {
        Route::get('influencer/search/{keyword}', [InfluencerController::class, 'findInfluencers'])->name('find');
        Route::get('gig/search', [GigController::class, 'search'])->name('gig.search');
        // Route::get('gig/search-by-tag/{keyword}', [GigController::class, 'searchByTag'])->name('gig.searchByTag');
    });

    Route::middleware([InfluencerRole::class])->prefix('influencer')->name('influencer.')->group(function () {
        Route::apiResource('gig', GigController::class);
        Route::apiResource('tag', TagController::class)->except(['update', 'delete']);

        
        Route::get('tag/search/{keyword}', [TagController::class, 'search'])->name('tag.search');
        
    });
    Route::middleware([AdminRole::class])->prefix('admin')->group(function(){
        Route::apiResource('currency', CurrencyController::class);
    });
});

Route::post('trustap/create/guest_user', [TrustapAuthController::class, 'createGuestUser'])->name('trustap.create.guest_user');
Route::middleware([TrustapUser::class])->prefix('trustap')->name('trustap.')->controller(TrustapController::class)->group(function () {
    Route::post('create_transaction/{gig}', 'createTransaction')->name('create.transaction');
    Route::get('payment/callback', 'paymentCallback')->name('payment.callback');
    Route::post('seller-accept-deposit/{entityTrustapTransaction}', 'sellerAcceptDeposit')->name('seller_accept_deposit');
    Route::post('buyer-confirms-handover/{entityTrustapTransaction}', 'buyerConfirmsHandover')->name('buyer_confirms_handover');
    Route::post('seller-claims-payout/{entityTrustapTransaction}', 'sellerClaimsPayout')->name('seller_claims_payout');
    Route::post('buyer-submit-complaint/{entityTrustapTransaction}', 'buyerSubmitComplaint')->name('buyer_submit_complaint');
});

// Route::get('/trustap/payment/callback', [PaymentController::class, 'paymentCallback'])->name('trustap.payment.callback');
