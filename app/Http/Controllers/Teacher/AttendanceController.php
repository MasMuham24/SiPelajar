<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Office;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $attendances = Attendance::with(['student','classroom',])->whereDate('date', today())->latest()->paginate(10);
        return view('teacher.attendance.index', compact('attendances'));
    }

    public function checkin(Request $request)
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
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

        if (!$office) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Lokasi sekolah belum dikonfigurasi oleh admin.');
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (!$latitude || !$longitude) {
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
                ->with('error', 'Anda berada di luar area sekolah. Jarak: ' . round($distance) . 'm (batas: ' . $office->radius . 'm)');
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
            ? 'Absensi masuk tercatat terlambat. Keterlambatan: ' . $lateMinutes . ' menit.'
            : 'Absensi masuk berhasil.';

        return redirect()->route('guru.attendance.index')->with('success', $message);
    }

    public function checkout(Request $request)
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return redirect()->route('guru.attendance.index')
                ->with('error', 'Data guru tidak ditemukan.');
        }

        $today = today()->toDateString();

        $attendance = Attendance::where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
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

    public function create(Request $request)
    {
        $classrooms = Classroom::with('students.user')->get();
        $selectedClassroom = $request->query('kelas');
        $selectedDate = $request->query('date', now()->format('Y-m-d'));

        $students = collect();
        foreach ($classrooms as $classroom) {
            foreach ($classroom->students as $student) {
                if ($selectedClassroom && (string) $selectedClassroom !== (string) $classroom->id) {
                    continue;
                }
                $students->push((object) [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name ?: ($student->user->name ?? null),
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                ]);
            }
        }

        return view('teacher.attendance.create', compact('classrooms', 'students', 'selectedClassroom', 'selectedDate'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'date' => ['required', 'date',],
            'attendance' => ['required', 'array',],
        ]);

        foreach ($request->attendance as $studentId => $status) {
            $student = Student::find($studentId);
            if (!$student) {
                continue;
            }
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date,],
                ['classroom_id' => $student->classroom_id, 'status' => $status,]
            );
        }

        return redirect()->route('attendance.index')->with('success','Absensi berhasil disimpan');

    }

    public function history()
    {

        $attendances = Attendance::with(['student','classroom',])->latest()->paginate(15);
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
