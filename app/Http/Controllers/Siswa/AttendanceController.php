<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $attendances = Attendance::with('classroom')
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view('siswa.attendance.index', compact('attendances'));
    }

    public function checkin(Request $request)
    {
        $student = Auth::user()->student;
        $today = today()->toDateString();

        $existing = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }

        $office = Office::latest()->first();

        if (!$office) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Lokasi sekolah belum dikonfigurasi oleh admin.');
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$latitude || !$longitude) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Lokasi tidak ditemukan. Pastikan GPS aktif.');
        }

        $distance = $this->calculateDistance(
            $office->latitude,
            $office->longitude,
            $latitude,
            $longitude
        );

        if ($distance > $office->radius) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Anda berada di luar area sekolah. Jarak: ' . round($distance) . 'm (batas: ' . $office->radius . 'm)');
        }

        $now = now();
        $checkinLimit = $now->copy()->setTime(8, 0, 0);
        $lateMinutes = 0;
        $status = 'hadir';

        if ($now->gt($checkinLimit)) {
            $lateMinutes = (int) ceil($checkinLimit->diffInSeconds($now) / 60);
            $status = 'terlambat';
        }

        Attendance::create([
            'student_id' => $student->id,
            'classroom_id' => $student->classroom_id,
            'date' => $today,
            'check_in' => $now->toTimeString(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'distance' => round($distance),
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ]);

        $message = $status === 'terlambat'
            ? 'Absensi masuk tercatat terlambat. Keterlambatan: ' . $lateMinutes . ' menit.'
            : 'Absensi masuk berhasil.';

        return redirect()->route('siswa.attendance.index')->with('success', $message);
    }

    public function checkout(Request $request)
    {
        $student = Auth::user()->student;
        $today = today()->toDateString();

        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Tidak ada absensi masuk hari ini atau sudah checkout.');
        }

        $now = now();
        $checkoutMin = now()->setTime(15, 30, 0);

        if ($now->lt($checkoutMin)) {
            return redirect()->route('siswa.attendance.index')
                ->with('error', 'Checkout baru bisa dilakukan setelah pukul 15:30.');
        }

        $attendance->update([
            'check_out' => $now->toTimeString(),
        ]);

        return redirect()->route('siswa.attendance.index')->with('success', 'Absensi pulang berhasil.');
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
