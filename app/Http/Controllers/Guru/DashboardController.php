<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $activeAssignments = \App\Models\Assignment::where('teacher_id', auth()->user()->teacher?->id)->count();
        $todayAttendance = \App\Models\Attendance::whereDate('created_at', today())->count();
        $lowestScore = 0; // Contoh

        return view('guru.dashboard', compact('activeAssignments', 'todayAttendance', 'lowestScore'));
    }
}
