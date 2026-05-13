<?php

use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/* Login */
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [TwoFactorController::class, 'login']);

/* 2FA */
Route::get('/2fa', [TwoFactorController::class, 'show']);

Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);

/* Dashboard */
Route::get('/dashboard', [TwoFactorController::class, 'dashboard'])
    ->middleware('auth');

/* Delete */
Route::delete('/users/{user}', [TwoFactorController::class, 'destroy'])
    ->middleware('auth');

/* Logout */
Route::post('/logout', function () {
    auth()->logout();

    request()->session()->invalidate();

    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

/* Home */
Route::get('/', function () {
    return redirect('/login');
});