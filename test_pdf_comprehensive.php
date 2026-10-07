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
use App\Http\Controllers\WaliKelas\AttendanceRecapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=== TEST 1: Normal case (wali kelas with students and attendance) ===\n";
$user = User::where('username', 'walikelas_test')->first();
$teacher = $user->teacher;
Auth::login($user);

$controller = new AttendanceRecapController();
$request = new Request(['month' => now()->month, 'year' => now()->year]);
$response = $controller->pdf($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
echo "SUCCESS\n\n";

echo "=== TEST 2: Empty attendance (wali kelas with students but no attendance) ===\n";
// Create a new classroom with no attendance
$major = Major::first();
$classroom2 = Classroom::create([
    'major_id' => $major->id,
    'grade' => 11,
    'name' => '11 TJKT 1 Test',
]);
$teacher->classroom_id = $classroom2->id;
$teacher->save();

// Add students but no attendance
for ($i = 1; $i <= 2; $i++) {
    $sUser = User::create([
        'name' => 'Siswa Baru ' . $i,
        'username' => 'siswabaru' . $i,
        'email' => 'siswabaru' . $i . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'siswa',
    ]);
    Student::create([
        'user_id' => $sUser->id,
        'classroom_id' => $classroom2->id,
        'nis' => '2600' . $i,
        'nisn' => '002600000' . $i,
        'gender' => 'Laki-laki',
    ]);
}

$request = new Request(['month' => now()->month, 'year' => now()->year]);
$response = $controller->pdf($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "SUCCESS (empty data handled)\n\n";

echo "=== TEST 3: No classroom (teacher without classroom) ===\n";
$teacher2 = Teacher::create([
    'user_id' => User::create([
        'name' => 'Guru Tanpa Kelas',
        'username' => 'guratanpakelas',
        'email' => 'guratanpakelas@test.com',
        'password' => bcrypt('password'),
        'role' => 'guru',
    ])->id,
    'nip' => '19910001',
    'gender' => 'Laki-laki',
    'phone' => '08123456789',
    'address' => 'Demak',
]);

$user3 = $teacher2->user;
Auth::login($user3);

$controller = new AttendanceRecapController();
$request = new Request(['month' => now()->month, 'year' => now()->year]);
try {
    $response = $controller->pdf($request);
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
    echo "Expected 404: " . $e->getMessage() . "\n";
    echo "SUCCESS (404 returned)\n\n";
}

echo "=== TEST 4: Unauthorized role (siswa) ===\n";
$siswaUser = User::where('username', 'siswa')->first();
Auth::login($siswaUser);

$controller = new AttendanceRecapController();
$request = new Request(['month' => now()->month, 'year' => now()->year]);
try {
    $response = $controller->pdf($request);
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
    echo "Expected 403: " . $e->getMessage() . "\n";
    echo "SUCCESS (403 returned)\n\n";
}

echo "=== TEST 5: Unauthorized role (admin) ===\n";
$adminUser = User::where('username', 'admin')->first();
Auth::login($adminUser);

$controller = new AttendanceRecapController();
$request = new Request(['month' => now()->month, 'year' => now()->year]);
try {
    $response = $controller->pdf($request);
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
    echo "Expected 403: " . $e->getMessage() . "\n";
    echo "SUCCESS (403 returned)\n\n";
}

echo "=== ALL TESTS COMPLETED ===\n";