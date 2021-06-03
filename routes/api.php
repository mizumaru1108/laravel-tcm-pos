<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('auth.profile');
    Route::resource('user', UserController::class);
    Route::resource('category', CategoryController::class);
});

Route::post('auth', [AuthController::class, 'login'])->name('auth.login');