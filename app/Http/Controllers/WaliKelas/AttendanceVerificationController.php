<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttendanceVerificationController extends Controller
{
    /**
     * Menampilkan absensi siswa kelas wali
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        if (! $teacher || ! $teacher->classroom_id) {
            abort(403, 'Anda belum memiliki kelas wali.');
        }
        $date = $request->date ?? today()->format('Y-m-d');
        $attendances = Attendance::with([
            'student.user',
            'student.classroom',
        ])
        ->whereHas('student', function ($query) use ($teacher) {
            $query->where('classroom_id', $teacher->classroom_id);
        })
        ->whereDate('date', $date)
        ->latest()
        ->paginate(10);
        return view('wali.attendance.index', compact('attendances', 'date'));
    }

    /**
     * Update status absensi siswa
     */
    public function update(Request $request,Attendance $attendance) {

        $request->validate([
            'status' => ['required','in:hadir,terlambat,izin,sakit,alpha',],
            'teacher_note' => ['nullable','string'],
        ]);
        $teacher = Auth::user()->teacher;
        if (
            $attendance->student->classroom_id != $teacher->classroom_id
        ) {
            abort(403);
        }
        $attendance->update([
            'status' => $request->status,
            'verified_by' => $teacher->user_id,
            'verified_at' => now(),
            'teacher_note' => $request->teacher_note,
        ]);
        return back()->with('success','Status absensi berhasil diperbarui.');
    }

    /**
     * Detail absensi siswa
     */
    public function show(Attendance $attendance) {
        $teacher = Auth::user()->teacher;
        if ($attendance->student->classroom_id != $teacher->classroom_id) {
            abort(403);
        }
        $attendance->load(['student.user',
            'student.classroom',
            'verifier',
        ]);
        return view('wali.attendance.show',compact('attendance'));
    }
}
