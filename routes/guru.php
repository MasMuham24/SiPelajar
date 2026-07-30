<?php

use App\Http\Controllers\Guru\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
