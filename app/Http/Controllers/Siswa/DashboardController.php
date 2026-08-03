<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $myAssignments = \App\Models\Assignment::where('classroom_id', auth()->user()->student?->classroom_id)
            ->where('is_active', true)
            ->count();
        $todayAttendance = \App\Models\Attendance::where('student_id', auth()->user()->student?->id)->whereDate('created_at', today())->count();
        $lastGrade = \App\Models\Submission::where('student_id', auth()->user()->student?->id)->latest()->value('score') ?? 0;

        return view('siswa.dashboard', compact('myAssignments', 'todayAttendance', 'lastGrade'));
    }
}
