<?php

use App\Http\Controllers\Siswa\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->as('siswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
