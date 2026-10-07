<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

// Setup: create a wali kelas with classroom
$major = Major::first();
$classroom = Classroom::first();
$classroom->update(['name' => '10 TJKT 1']);

$user = User::create([
    'name' => 'Wali Kelas Test',
    'username' => 'walikelas_test',
    'email' => 'walikelas@test.com',
    'password' => bcrypt('password'),
    'role' => 'guru',
]);

$teacher = Teacher::create([
    'user_id' => $user->id,
    'nip' => '19900001',
    'gender' => 'Laki-laki',
    'phone' => '08123456789',
    'address' => 'Demak',
    'classroom_id' => $classroom->id,
]);

// Add some students to the classroom
for ($i = 1; $i <= 3; $i++) {
    $sUser = User::create([
        'name' => 'Siswa ' . $i,
        'username' => 'siswa' . $i,
        'email' => 'siswa' . $i . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'siswa',
    ]);
    Student::create([
        'user_id' => $sUser->id,
        'classroom_id' => $classroom->id,
        'nis' => '2500' . $i,
        'nisn' => '002500000' . $i,
        'gender' => 'Laki-laki',
    ]);
}

// Add attendance records
$students = Student::where('classroom_id', $classroom->id)->get();
foreach ($students as $student) {
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        'check_in' => '07:00:00',
        'latitude' => -6.8951427,
        'longitude' => 110.6177293,
        'distance' => 10,
        'status' => 'hadir',
        'late_minutes' => 0,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'date' => Carbon::now()->subDays(3)->format('Y-m-d'),
        'check_in' => '07:15:00',
        'latitude' => -6.8951427,
        'longitude' => 110.6177293,
        'distance' => 10,
        'status' => 'terlambat',
        'late_minutes' => 15,
    ]);
    Attendance::create([
        'student_id' => $student->id,
        'classroom_id' => $classroom->id,
        'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
        'check_in' => '07:00:00',
        'latitude' => -6.8951427,
        'longitude' => 110.6177293,
        'distance' => 10,
        'status' => 'izin',
        'late_minutes' => 0,
    ]);
}

echo "Setup done. Teacher ID: " . $teacher->id . ", Classroom: " . $classroom->name . PHP_EOL;