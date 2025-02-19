<?php

use App\Http\Controllers\Api\v1\Influencer\GigController;
use App\Http\Controllers\Api\v1\Influencer\InfluencerController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\InfluencerRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware([JwtMiddleware::class, VerifyEmail::class])->group(function () {    
    Route::middleware([BrandRole::class])->group(function () {
        Route::get('search/{keyword}', [InfluencerController::class, 'findInfluencers'])->name('find');
    });

    Route::middleware([InfluencerRole::class])->prefix('influencer')->name('influencer.')->group(function () {
        Route::apiResource('gig', GigController::class);

    });
});
