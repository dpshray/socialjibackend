<?php

use App\Http\Controllers\Api\v1\TrustapController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class, VerifyEmail::class, BrandRole::class])->group(function () {
    Route::post('trustap/create/guest_user', [TrustapAuthController::class, 'createGuestUser'])->name('trustap.create.guest_user');
});

Route::middleware([TrustapUser::class])
    ->prefix('trustap')
    ->name('trustap.')
    ->controller(TrustapController::class)->group(function () {
        Route::post('create_transaction/{gig}', 'createTransaction')->name('create.transaction');
        Route::get('payment/callback', 'paymentCallback')->name('payment.callback');
        Route::post('seller-accept-deposit/{entityTrustapTransaction}', 'sellerAcceptDeposit')->name('seller_accept_deposit');
        Route::post('buyer-confirms-handover/{entityTrustapTransaction}', 'buyerConfirmsHandover')->name('buyer_confirms_handover');
        Route::post('seller-claims-payout/{entityTrustapTransaction}', 'sellerClaimsPayout')->name('seller_claims_payout');
        Route::post('buyer-submit-complaint/{entityTrustapTransaction}', 'buyerSubmitComplaint')->name('buyer_submit_complaint');
});

Route::get('payment-success', [TrustapController::class, 'testResponse']);