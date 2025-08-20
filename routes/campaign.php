<?php

use App\Http\Controllers\Api\v1\Brand\CampaignController;
use App\Http\Middleware\BrandRole;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifyEmail;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\BrandInfluencerRole;

Route::middleware([
    JwtMiddleware::class,
    VerifyEmail::class
    ])
    ->group(function(){
        Route::prefix('campaign')->group(function(){
            Route::get('get-brand-tag', [CampaignController::class, 'tagsList']);
        });
});
Route::middleware([
        JwtMiddleware::class,
        VerifyEmail::class,
        BrandRole::class
    ])->group(function(){
        Route::apiResource('campaign', CampaignController::class);
});