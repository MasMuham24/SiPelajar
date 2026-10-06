<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $myAssignments = \App\Models\Assignment::where('classroom_id', $student?->classroom_id)
            ->where('is_active', true)
            ->count();

        $todayAttendance = \App\Models\Attendance::where('student_id', $student?->id)
            ->whereDate('date', today())
            ->first();

        $todayStatus = '-';
        if ($todayAttendance) {
            $todayStatus = match ($todayAttendance->status) {
                'terlambat', 'Terlambat' => 'Terlambat',
                'izin', 'Izin' => 'Izin',
                'sakit', 'Sakit' => 'Sakit',
                'alpha', 'alfa', 'Alfa' => 'Alfa',
                'hadir', 'Hadir' => ($todayAttendance->late_minutes > 0 ? 'Terlambat' : 'Tepat Waktu'),
                default => ($todayAttendance->late_minutes > 0 ? 'Terlambat' : 'Tepat Waktu'),
            };
        }

        $lastGrade = \App\Models\Submission::where('student_id', $student?->id)->latest()->value('score') ?? 0;

        return view('siswa.dashboard', compact('myAssignments', 'todayAttendance', 'todayStatus', 'lastGrade'));
    }
}
