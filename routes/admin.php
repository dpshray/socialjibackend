<?php

use App\Http\Controllers\Api\v1\Admin\AdminAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\v1\Admin\AdminDataListController;
use App\Http\Controllers\Api\v1\CurrencyController;
use App\Http\Middleware\AdminRole;
use App\Http\Middleware\JwtMiddleware;

Route::middleware(JwtMiddleware::class)->prefix('admin')->group(function(){
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware([AdminRole::class])->group(function () {
        Route::get('dashboard', AdminDashboardController::class);
    });
    Route::controller(AdminDataListController::class)->group(function(){
        Route::get('gig/list', 'gigRecord');
        Route::get('brand/list', 'brandRecord');
        Route::get('influencer/list', 'influencerRecord');
        Route::get('payment/list', 'paymentRecord');

        Route::get('gig/detail/{gig}', 'gigListDetail');
        Route::get('brand/detail/{user:nick_name}', 'brandListDetail');
        Route::get('influencer/detail/{user:nick_name}', 'influencerListDetail');
        Route::get('payment/detail/{payment}', 'paymentListDetail');
    });
});