<?php

use App\Http\Controllers\Api\v1\TrustapController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\InfluencerRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/**
 * BrandRole::class middleware commented because even influencer also have to register in order to receive payment
*/
Route::middleware([JwtMiddleware::class, VerifyEmail::class/* , BrandRole::class */])->group(function () {
    Route::post('trustap/create/guest_user', [TrustapAuthController::class, 'createGuestUser'])->name('trustap.create.guest_user');
});

Route::prefix('trustap')->name('trustap.')->group(function(){
    Route::middleware([JwtMiddleware::class])->group(function(){
        Route::get('get-country-codes', [TrustapController::class, 'trustapCountryCodes']);
        Route::middleware([TrustapUser::class])->group(function(){
            Route::controller(TrustapController::class)->group(function () {
                Route::post('create_transaction/{gig}', 'createTransaction')->name('create.transaction');
                Route::post('buyer-submit-complaint/{entityTrustapTransaction}', 'buyerSubmitComplaint')->name('buyer_submit_complaint');
                Route::post('buyer-confirms-handover/{entityTrustapTransaction}', 'buyerConfirmsHandover')->name('buyer_confirms_handover');
                Route::post('seller-accept-deposit/{entityTrustapTransaction}', 'sellerAcceptDeposit')->name('seller_accept_deposit');
                Route::post('seller-claims-payout/{entityTrustapTransaction}', 'sellerClaimsPayout')->name('seller_claims_payout');
                Route::middleware(BrandRole::class)->group(function () {
                    Route::get('get-brand-transaction-lists', 'fetchBrandTransaction');
                    Route::get('payment/callback', 'paymentCallback')->name('payment.callback');
                    Route::post('buyer-confirm-delivery/{entityTrustapTransaction}', 'buyerReceivedConfirmation');
                });
                Route::middleware(InfluencerRole::class)->group(function () {
                    Route::get('item-delivery-confirmation/{entityTrustapTransaction}', 'confirmDelivery');
                    Route::get('get-influencer-transaction-lists', 'fetchInfluencerTransaction');
                });
                Route::get('auth/redirect', [TrustapAuthController::class, 'redirectToTrustap'])->name('trustap.auth.redirect');
                Route::get('auth/callback', [TrustapAuthController::class, 'handleProviderCallback'])->name('trustap.auth.callback');
            });
        });
    });
/*     Route::controller(TrustapAuthController::class)->group(function(){
        Route::get('auth/redirect', 'redirectToTrustap');
        // Route::get('auth/callback', 'handleProviderCallback');
    }); */
});