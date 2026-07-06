<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwoFactorController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [TwoFactorController::class, 'showLogin'])->name('login');
Route::post('/login', [TwoFactorController::class, 'login']);
Route::get('/2fa/verify', [TwoFactorController::class, 'showNotice'])->name('2fa.notice');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
Route::get('/2fa/recovery-codes/download', [TwoFactorController::class, 'downloadRecoveryCodes'])->name('2fa.recovery.download');
Route::get('/dashboard', [TwoFactorController::class, 'dashboard'])->name('dashboard')->middleware('auth');
Route::post('/logout', [TwoFactorController::class, 'logout'])->name('logout');