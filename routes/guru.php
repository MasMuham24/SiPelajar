<?php

use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::resource('assignments', AssignmentController::class);
    Route::patch('/assignments/{assignment}/end', [AssignmentController::class, 'end'])->name('assignments.end');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
});
