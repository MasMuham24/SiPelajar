<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Office;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->data($request);
        }
        $date = $request->query('date', today()->toDateString());
        $attendances = Attendance::with(['student.user', 'classroom'])
            ->whereNotNull('student_id')
            ->whereDate('date', $date)
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('teacher.attendance.index', compact('attendances', 'date'));
    }

    public function data(Request $request)
    {
        $date = $request->query('date', today()->toDateString());

        $attendances = Attendance::with(['student.user', 'classroom'])
            ->whereNotNull('student_id')
            ->whereDate('date', $date)
            ->latest()
            ->paginate(10);

        $items = collect($attendances->items())->map(function ($attendance, $index) use ($attendances) {
            $studentName = $attendance->student?->user?->name ?? $attendance->student?->name ?? '-';
            $classroomName = $attendance->classroom?->name ?? '-';
            $dateFormatted = $attendance->date ? $attendance->date->format('d M Y') : '-';
            $checkIn = $attendance->check_in ? $attendance->check_in->format('H:i') : '-';
            $checkOut = $attendance->check_out ? $attendance->check_out->format('H:i') : '-';

            $statusStr = ucfirst(strtolower($attendance->status ?? ''));
            $badgeClass = match ($statusStr) {
                'Hadir' => 'bg-green-100 text-green-800',
                'Terlambat' => 'bg-yellow-100 text-yellow-800',
                'Izin' => 'bg-blue-100 text-blue-800',
                'Sakit' => 'bg-orange-100 text-orange-800',
                'Alfa', 'Alpha' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-800',
            };

            $lateMinutes = (int) $attendance->late_minutes;
            $lateText = $lateMinutes > 0 ? "{$lateMinutes} menit" : '-';

            return [
                'id' => $attendance->id,
                'index' => $attendances->firstItem() ? ($attendances->firstItem() + $index) : ($index + 1),
                'student_name' => $studentName,
                'classroom_name' => $classroomName,
                'date' => $dateFormatted,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $attendance->status,
                'status_label' => $statusStr,
                'badge_class' => $badgeClass,
                'late_minutes' => $lateMinutes,
                'late_text' => $lateText,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'total' => $attendances->total(),
            'date' => $date,
        ]);
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
        $setting = AttendanceSetting::getSettings();
        $checkinLimit = $setting->getStartLimit($now);
        $lateMinutes = 0;
        $status = 'hadir';

        if ($now->gt($checkinLimit)) {
            $lateMinutes = (int) ceil($checkinLimit->diffInSeconds($now) / 60);
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
        $setting = AttendanceSetting::getSettings();
        $checkoutMin = $setting->getEndLimit($now);

        if ($now->lt($checkoutMin)) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Checkout baru bisa dilakukan setelah pukul '.$setting->getFormattedEndTime().'.');
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
