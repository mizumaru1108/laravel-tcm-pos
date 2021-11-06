<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Report\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('auth.profile');

    Route::resource('user', UserController::class);

    Route::get('roles', [PermissionController::class, 'rolelist'])->name("permission.rolelist");

    Route::resource('product', ProductController::class);
    Route::post('product/{id}', [ProductController::class, 'update'])->name('products.update');

    Route::resource('category', CategoryController::class);
    Route::get('categories', [CategoryController::class, 'categorylist']);


    Route::resource('order', OrderController::class);
    Route::get('getcount', [OrderController::class, 'getUnfinishedTransactionCount']);

    Route::get('/report/dashboarddaily', [ReportController::class, 'dashboardDailyReport'])->name("report.dashboardDailyReport");
    Route::get('/report/dashboardweekly', [ReportController::class, 'dashboardWeeklyReport'])->name("report.dashboardWeeklyReport");
    Route::get('/report/dashboardyearly', [ReportController::class, 'dasboardYearlyReport'])->name("report.dasboardYearlyReport");
    Route::get('/report/dashboardrecenttransaction', [ReportController::class, 'dashboardRecentTransaction'])->name("report.dashboardRecentTransaction");

    Route::get('/report/daily', [ReportController::class, 'dailyReport'])->name("report.dailyReport");
    Route::get('/report/weekly', [ReportController::class, 'weeklyReport'])->name("report.weeklyReport");
    Route::get('/report/monthly', [ReportController::class, 'monthlyReport'])->name("report.monthlyReport");
    Route::get('/report/yearly', [ReportController::class, 'yearlyReport'])->name("report.yearlyReport");
    Route::get('/report/alltransaction', [ReportController::class, 'allTransactionReport'])->name("report.allTransactionReport");
    Route::get('/report/exportreport', [ReportController::class, 'exportReport'])->name("report.exportReport");
});

Route::post('auth', [AuthController::class, 'login'])->name('auth.login');