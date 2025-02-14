<?php

use App\Http\Controllers\Api\v1\Influencer\InfluencerController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::controller(InfluencerController::class)->middleware([JwtMiddleware::class, VerifyEmail::class, BrandRole::class])->prefix('influencer')->name('influencer.')->group(function () {
    Route::get('search/{keyword}', 'findInfluencers')->name('find');
});