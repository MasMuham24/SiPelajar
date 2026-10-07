<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

// Get the test user
$user = User::where('username', 'walikelas_test')->first();
if (!$user) {
    echo "Test user not found\n";
    exit(1);
}

$teacher = $user->teacher;
if (!$teacher || !$teacher->classroom_id) {
    echo "Teacher or classroom not found\n";
    exit(1);
}

// Test the getRecapData logic WITH eager loading
$month = now()->month;
$year = now()->year;

$classroom = $teacher->classroom;
$students = $classroom->students()->with('user')->orderBy('name')->get();
$recaps = $students->map(function ($student) use ($month, $year) {
    $attendances = Attendance::query()
        ->where('student_id', $student->id)
        ->where('classroom_id', $student->classroom_id)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->get();
    return [
        'student' => $student,
        'hadir' => $attendances->where('status', 'hadir')->count(),
        'terlambat' => $attendances->where('status', 'terlambat')->count(),
        'izin' => $attendances->where('status', 'izin')->count(),
        'sakit' => $attendances->where('status', 'sakit')->count(),
        'alpha' => $attendances->where('status', 'alpha')->count(),
        'total' => $attendances->count(),
    ];
});

$data = compact('classroom', 'recaps', 'month', 'year');

echo "Data prepared:\n";
echo "Classroom: " . $data['classroom']->name . "\n";
echo "Month: " . $data['month'] . "\n";
echo "Year: " . $data['year'] . "\n";
echo "Students: " . $data['recaps']->count() . "\n";
foreach ($data['recaps'] as $recap) {
    $name = $recap['student']->user->name ?? $recap['student']->name ?? '-';
    echo "  - " . $name . " (NIS: " . $recap['student']->nis . ") H:" . $recap['hadir'] . " T:" . $recap['terlambat'] . " I:" . $recap['izin'] . " S:" . $recap['sakit'] . " A:" . $recap['alpha'] . " Total:" . $recap['total'] . "\n";
}

// Now test PDF generation
try {
    $pdf = Pdf::loadView('wali.attendance.recap-pdf', $data)->setPaper('a4', 'landscape');
    $output = $pdf->output();
    echo "\nPDF generated successfully. Size: " . strlen($output) . " bytes\n";
    
    // Save to file for manual inspection
    file_put_contents('test_output.pdf', $output);
    echo "PDF saved to test_output.pdf\n";
} catch (\Throwable $e) {
    echo "PDF generation failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}