<?php

use App\Http\Controllers\Api\v1\Admin\AdminAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\v1\Admin\AdminDataListController;
use App\Http\Controllers\Api\v1\CurrencyController;
use App\Http\Middleware\AdminRole;


Route::prefix('admin')->group(function(){
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware([AdminRole::class])->group(function () {
        Route::apiResource('currency', CurrencyController::class);
        Route::get('dashboard', AdminDashboardController::class);
    });
    Route::controller(AdminDataListController::class)->group(function(){
        Route::get('gig/list', 'gigRecord');
        Route::get('brand/list', 'brandRecord');
        Route::get('influencer/list', 'influencerRecord');
        Route::get('payment/list', 'paymentRecord');
    });
});