<?php

use App\Http\Controllers\Siswa\AttendanceController;
use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\Siswa\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->as('siswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::patch('/assignments/{assignment}/submission/update', [AssignmentController::class, 'updateSubmission'])->name('assignments.updateSubmission');
});
