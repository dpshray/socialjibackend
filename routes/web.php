<?php

use App\Http\Controllers\Api\v1\PaymentController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('test', function(){
    $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
        ->withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->get(config('services.trustap.url') . 'p2p/me/transactions/29928/create_full_user');
    if ($response->failed() && $response->status() == 400) {
        dd($response);
    }
    return $response;
});

Route::get('/', function () {
    $token = '943489be-750d-40de-9bbc-52b551429274'; // could be static or user-based
    $transactionId = 29185;

    $response = Http::withToken($token)
        ->post("https://api.trustap.com/v1/transactions/{$transactionId}/remainder", [
            'amount' => 200,
            'currency' => 'eur',
        ]);
        dd($response);
    // return view('welcome');
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

Route::middleware([JwtMiddleware::class, TrustapUser::class])->group(function () {
    Route::get('/trustap/auth/redirect', [TrustapAuthController::class, 'redirectToTrustap'])->name('trustap.auth.redirect');
    Route::get('/trustap/auth/callback', [TrustapAuthController::class, 'handleProviderCallback'])->name('trustap.auth.callback');
});
