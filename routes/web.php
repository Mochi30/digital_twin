<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::view('/ruang-kendali-ews', 'app')->name('admin.login');
Route::post('/ruang-kendali-ews/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('admin.login.submit');

Route::middleware('admin.session')->prefix('admin')->group(function () {
    Route::view('/', 'app')->name('admin.dashboard');
    Route::get('/session', [AdminAuthController::class, 'session']);
    Route::get('/settings', [DashboardController::class, 'settings']);
    Route::put('/settings', [DashboardController::class, 'updateSettings']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
});
