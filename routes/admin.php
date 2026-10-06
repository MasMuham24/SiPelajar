<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\AttendanceSettingController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('majors', MajorController::class);
    Route::post('classrooms/bulk-delete', [ClassroomController::class, 'bulkDestroy'])->name('classrooms.bulkDestroy');
    Route::resource('classrooms', ClassroomController::class);
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::resource('students', StudentController::class);
    Route::get('teachers/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.template');
    Route::post('teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
    Route::resource('teachers', TeacherController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('offices', OfficeController::class)->except(['show']);
    Route::get('attendance-settings', [AttendanceSettingController::class, 'index'])->name('attendance-settings.index');
    Route::match(['put', 'post'], 'attendance-settings', [AttendanceSettingController::class, 'update'])->name('attendance-settings.update');
});
