<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $attendances = Attendance::with(['student', 'classroom'])->whereNotNull('student_id')->whereDate('date', today())->latest()->paginate(10);

        return view('teacher.attendance.index', compact('attendances'));
    }

    public function checkin(Request $request)
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (! $teacher) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Data guru tidak ditemukan.');
        }

        $today = today()->toDateString();

        $existing = Attendance::where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->first();

        if ($existing) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Anda sudah melakukan absensi masuk hari ini.');
        }

        $office = Office::latest()->first();

        if (! $office) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Lokasi sekolah belum dikonfigurasi oleh admin.');
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (! $latitude || ! $longitude) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Lokasi tidak ditemukan. Pastikan GPS aktif.');
        }

        $distance = $this->calculateDistance(
            $office->latitude,
            $office->longitude,
            $latitude,
            $longitude
        );

        if ($distance > $office->radius) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Anda berada di luar area sekolah. Jarak: '.round($distance).'m (batas: '.$office->radius.'m)');
        }

        $now = now();
        $checkinLimit = now()->setTime(8, 0, 0);
        $lateMinutes = 0;
        $status = 'hadir';

        if ($now->gt($checkinLimit)) {
            $lateMinutes = $now->diffInMinutes($checkinLimit);
            $status = 'terlambat';
        }

        Attendance::create([
            'teacher_id' => $teacher->id,
            'date' => $today,
            'check_in' => $now->toTimeString(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'distance' => round($distance),
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ]);

        $message = $status === 'terlambat'
            ? 'Absensi masuk tercatat terlambat. Keterlambatan: '.$lateMinutes.' menit.'
            : 'Absensi masuk berhasil.';

        return redirect()->route('guru.attendance.index')->with('success', $message);
    }

    public function checkout(Request $request)
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (! $teacher) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Data guru tidak ditemukan.');
        }

        $today = today()->toDateString();

        $attendance = Attendance::where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->first();

        if (! $attendance) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Tidak ada absensi masuk hari ini atau sudah checkout.');
        }

        $now = now();
        $checkoutMin = now()->setTime(16, 0, 0);

        if ($now->lt($checkoutMin)) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Checkout baru bisa dilakukan setelah pukul 16:00.');
        }

        $attendance->update([
            'check_out' => $now->toTimeString(),
        ]);

        return redirect()->route('guru.attendance.index')->with('success', 'Absensi pulang berhasil.');
    }

    public function history()
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        $attendances = Attendance::with(['teacher', 'classroom'])
            ->whereNotNull('teacher_id')
            ->where('teacher_id', $teacher?->id)
            ->latest()
            ->paginate(15);

        return view('teacher.attendance.history', compact('attendances'));
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
