<?php

use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\SubmissionController;
use App\Http\Controllers\Teacher\GradeRecapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('assignments', AssignmentController::class);
    Route::patch('/assignments/{assignment}/end', [AssignmentController::class, 'end'])->name('assignments.end');
    Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'index'])->name('assignments.submissions.index');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::put('/submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
    Route::get('/grades', [GradeRecapController::class, 'index'])->name('grades.index');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
});
