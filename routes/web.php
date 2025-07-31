<?php

use App\Http\Controllers\Api\v1\PaymentController;
use App\Http\Controllers\TrustapAuthController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TrustapUser;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Services\v1\Payment\TrustAppException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('check', function(){
    dd(EntityTrustapTransaction::get());
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('facebook')->scopes(['pages_show_list', 'pages_read_engagement'])
        ->redirect();
});

Route::get('currency-table-updater', function(){
    $countryCurrencyData = [
        'at' => ['id' => 1, 'country' => 'Austria',        'symbol' => '€',   'currency' => 'EUR'],
        'au' => ['id' => 2, 'country' => 'Australia',      'symbol' => '$',   'currency' => 'AUD'],
        'be' => ['id' => 3, 'country' => 'Belgium',        'symbol' => '€',   'currency' => 'EUR'],
        'bg' => ['id' => 4, 'country' => 'Bulgaria',       'symbol' => 'лв',  'currency' => 'BGN'],
        'ca' => ['id' => 5, 'country' => 'Canada',         'symbol' => '$',   'currency' => 'CAD'],
        'ch' => ['id' => 6, 'country' => 'Switzerland',    'symbol' => 'CHF', 'currency' => 'CHF'],
        'cy' => ['id' => 7, 'country' => 'Cyprus',         'symbol' => '€',   'currency' => 'EUR'],
        'cz' => ['id' => 8, 'country' => 'Czech Republic', 'symbol' => 'Kč',  'currency' => 'CZK'],
        'de' => ['id' => 9, 'country' => 'Germany',        'symbol' => '€',   'currency' => 'EUR'],
        'dk' => ['id' => 10, 'country' => 'Denmark',        'symbol' => 'kr',  'currency' => 'DKK'],
        'ee' => ['id' => 11, 'country' => 'Estonia',        'symbol' => '€',   'currency' => 'EUR'],
        'es' => ['id' => 12, 'country' => 'Spain',          'symbol' => '€',   'currency' => 'EUR'],
        'fi' => ['id' => 13, 'country' => 'Finland',        'symbol' => '€',   'currency' => 'EUR'],
        'fr' => ['id' => 14, 'country' => 'France',         'symbol' => '€',   'currency' => 'EUR'],
        'gb' => ['id' => 15, 'country' => 'United Kingdom', 'symbol' => '£',   'currency' => 'GBP'],
        'gr' => ['id' => 16, 'country' => 'Greece',         'symbol' => '€',   'currency' => 'EUR'],
        'hr' => ['id' => 17, 'country' => 'Croatia',        'symbol' => '€',   'currency' => 'EUR'],
        'hu' => ['id' => 18, 'country' => 'Hungary',        'symbol' => 'Ft',  'currency' => 'HUF'],
        'ie' => ['id' => 19, 'country' => 'Ireland',        'symbol' => '€',   'currency' => 'EUR'],
        'it' => ['id' => 20, 'country' => 'Italy',          'symbol' => '€',   'currency' => 'EUR'],
        'lt' => ['id' => 21, 'country' => 'Lithuania',      'symbol' => '€',   'currency' => 'EUR'],
        'lu' => ['id' => 22, 'country' => 'Luxembourg',     'symbol' => '€',   'currency' => 'EUR'],
        'lv' => ['id' => 23, 'country' => 'Latvia',         'symbol' => '€',   'currency' => 'EUR'],
        'mt' => ['id' => 24, 'country' => 'Malta',          'symbol' => '€',   'currency' => 'EUR'],
        'nl' => ['id' => 25, 'country' => 'Netherlands',    'symbol' => '€',   'currency' => 'EUR'],
        'no' => ['id' => 26, 'country' => 'Norway',         'symbol' => 'kr',  'currency' => 'NOK'],
        'pl' => ['id' => 27, 'country' => 'Poland',         'symbol' => 'zł',  'currency' => 'PLN'],
        'pt' => ['id' => 28, 'country' => 'Portugal',       'symbol' => '€',   'currency' => 'EUR'],
        'ro' => ['id' => 29, 'country' => 'Romania',        'symbol' => 'lei', 'currency' => 'RON'],
        'se' => ['id' => 30, 'country' => 'Sweden',         'symbol' => 'kr',  'currency' => 'SEK'],
        'si' => ['id' => 31, 'country' => 'Slovenia',       'symbol' => '€',   'currency' => 'EUR'],
        'sk' => ['id' => 32, 'country' => 'Slovakia',       'symbol' => '€',   'currency' => 'EUR'],
        'us' => ['id' => 33, 'country' => 'United States',  'symbol' => '$',   'currency' => 'USD'],
    ];
    foreach ($countryCurrencyData as $key => $value) {
        DB::table('currencies')->updateOrInsert([
            'id' => $value['id']                                                                                                                                                       
        ],[
            'name' => $key,
            'code' => strtolower($value['currency']),
            'symbol' => $value['symbol']
        ]);
    }
    echo 'DONE';
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
