<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $activeAssignments = Assignment::where('teacher_id', Auth::user()?->teacher?->id)->count();
        $todayAttendance = Attendance::whereDate('created_at', today())->count();
        $lowestScore = 0; // Contoh

        return view('guru.dashboard', compact('activeAssignments', 'todayAttendance', 'lowestScore'));
    }
}
