<?php

use App\Http\Controllers\Api\v1\PaymentController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use App\Models\EntityTrustapTransaction;
use App\Services\v1\Payment\TrustAppException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('facebook')->scopes(['pages_show_list', 'pages_read_engagement'])
        ->redirect();
});

Route::get('/auth/callback', function () {
    // $user = Socialite::driver('facebook')->stateless()->user();
    $facebookUser = Socialite::driver('facebook')->stateless()->user();

    dd($facebookUser);
    // $user->token
});

// Route::get('/payment', [PaymentController::class, 'store']);
// Route::get('/payment', [PaymentController::class, 'store']);
// Route::get('/trustap/payment/callback', [PaymentController::class, 'paymentCallback'])->name('trustap.payment.callback');
