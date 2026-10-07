<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user()?->student;
        $myAssignments = Assignment::where('classroom_id', $student?->classroom_id)->where('is_active', true)->count();
        $todayAttendance = Attendance::where('student_id', $student?->id)->whereDate('date', today())->first();
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
        $lastGrade = Submission::where('student_id', $student?->id)->latest()->value('score') ?? 0;
        return view('siswa.dashboard', compact('myAssignments', 'todayAttendance', 'todayStatus', 'lastGrade'));
    }
}
