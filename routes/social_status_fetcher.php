<?php

use App\Http\Controllers\Api\v1\Social\SocialDataFetcherController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

Route::prefix('social-data-fetcher')
    ->controller(SocialDataFetcherController::class)
    ->group(function () {
        Route::middleware([JwtMiddleware::class, VerifyEmail::class])->group(function(){
            Route::get('fb', 'redirectToFacebook');
        });
        Route::get('fb-callback', 'facebookCallback')->name('facebook.callback');
        Route::get('facebook/pages/{token}/{access_token}', 'getFacebookPages')->name('facebook.pages');
});