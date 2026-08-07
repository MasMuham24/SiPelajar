<?php

use App\Http\Controllers\WaliKelas\AttendanceVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru', 'wali-kelas'])->prefix('wali-kelas')->name('wali-kelas.')->group(function () {
    Route::get('/dashboard',[AttendanceVerificationController::class, 'index'])->name('dashboard');
    Route::get('/attendance',[AttendanceVerificationController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{attendance}',[AttendanceVerificationController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{attendance}',[AttendanceVerificationController::class, 'update'])->name('attendance.update');
});
