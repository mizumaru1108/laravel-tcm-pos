<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('auth.profile');

    Route::resource('user', UserController::class);

    Route::resource('product', ProductController::class);
    Route::post('product/{id}', [ProductController::class, 'update'])->name('products.update');

    Route::resource('category', CategoryController::class);
    Route::get('categories', [CategoryController::class, 'categorylist']);


    Route::resource('order', OrderController::class);
    Route::get('getcount', [OrderController::class, 'getUnfinishedTransactionCount']);
});

Route::post('auth', [AuthController::class, 'login'])->name('auth.login');