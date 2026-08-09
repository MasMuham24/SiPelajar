<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRecapController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        if (!$teacher) {
            return view('wali.attendance.recap', [
                'classroom' => null,
                'recaps' => collect(),
                'month' => (int) ($request->month ?? now()->month),
                'year' => (int) ($request->year ?? now()->year),
            ]);
        }
        $classroom = $teacher->classroom;
        if (!$classroom) {
            return view('wali.attendance.recap', [
                'classroom' => null,
                'recaps' => collect(),
                'month' => (int) ($request->month ?? now()->month),
                'year' => (int) ($request->year ?? now()->year),
            ]);
        }
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        $students = $classroom->students()->orderBy('name')->get();
        $recaps = $students->map(function ($student) use ($month, $year) {
            $attendances = Attendance::query()->where('student_id', $student->id)
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

        return view('wali.attendance.recap', compact('classroom','recaps','month','year'));
    }
    public function pdf(Request $request)
    {
    }
    public function excel(Request $request)
    {
        // Akan kita isi setelah PDF selesai.
    }
}
