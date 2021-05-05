<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('menu', MenuController::class);
    Route::resource('user', UserController::class);
});

Route::post('auth', [AuthController::class, 'login'])->name('auth.login');