<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Office;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Student $student;
    protected Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $major = Major::create([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $classroom = Classroom::create([
            'major_id' => $major->id,
            'grade' => 11,
            'name' => '11 TJKT 1',
        ]);

        $this->studentUser = User::create([
            'name' => 'Siswa Test',
            'username' => '26001999',
            'email' => 'siswa@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $classroom->id,
            'nis' => '26001999',
            'nisn' => '002600001999',
            'gender' => 'Laki-laki',
        ]);

        $this->office = Office::create([
            'name' => 'SMK SiPelajar',
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
            'radius' => 500,
        ]);

        AttendanceSetting::updateOrCreate([], [
            'school_start_time' => '08:00:00',
            'school_end_time' => '15:30:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 1. Absen sebelum jam masuk (07:55) -> Tepat Waktu (hadir, late_minutes = 0)
    public function test_1_checkin_before_start_time_is_on_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 07:55:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));
        $response->assertSessionHas('success', 'Absensi masuk berhasil.');

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('hadir', $attendance->status);
        $this->assertEquals(0, $attendance->late_minutes);

        // Verifikasi di Dashboard & Index
        $dashboardResponse = $this->actingAs($this->studentUser)->get(route('siswa.dashboard'));
        $dashboardResponse->assertSee('Tepat Waktu');
        $dashboardResponse->assertDontSee('Terlambat');

        $indexResponse = $this->actingAs($this->studentUser)->get(route('siswa.attendance.index'));
        $indexResponse->assertSee('Tepat Waktu');
    }

    // 2. Absen tepat pada jam masuk (08:00:00) -> Tepat Waktu (hadir, late_minutes = 0)
    public function test_2_checkin_exactly_at_start_time_is_on_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));
        $response->assertSessionHas('success', 'Absensi masuk berhasil.');

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('hadir', $attendance->status);
        $this->assertEquals(0, $attendance->late_minutes);

        $dashboardResponse = $this->actingAs($this->studentUser)->get(route('siswa.dashboard'));
        $dashboardResponse->assertSee('Tepat Waktu');

        $indexResponse = $this->actingAs($this->studentUser)->get(route('siswa.attendance.index'));
        $indexResponse->assertSee('Tepat Waktu');
    }

    // 3. Absen 1 menit setelah jam masuk (08:01:00) -> Terlambat 1 menit
    public function test_3_checkin_one_minute_after_start_time_is_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:01:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));
        $response->assertSessionHas('success', 'Absensi masuk tercatat terlambat. Keterlambatan: 1 menit.');

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('terlambat', $attendance->status);
        $this->assertEquals(1, $attendance->late_minutes);

        // Verifikasi di Dashboard & Index
        $dashboardResponse = $this->actingAs($this->studentUser)->get(route('siswa.dashboard'));
        $dashboardResponse->assertSee('Terlambat');

        $indexResponse = $this->actingAs($this->studentUser)->get(route('siswa.attendance.index'));
        $indexResponse->assertSee('Terlambat (1 menit)');
    }

    // 4. Absen jauh setelah jam masuk (08:30:00) -> Terlambat 30 menit
    public function test_4_checkin_well_after_start_time_is_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:30:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));
        $response->assertSessionHas('success', 'Absensi masuk tercatat terlambat. Keterlambatan: 30 menit.');

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('terlambat', $attendance->status);
        $this->assertEquals(30, $attendance->late_minutes);

        $dashboardResponse = $this->actingAs($this->studentUser)->get(route('siswa.dashboard'));
        $dashboardResponse->assertSee('Terlambat');

        $indexResponse = $this->actingAs($this->studentUser)->get(route('siswa.attendance.index'));
        $indexResponse->assertSee('Terlambat (30 menit)');
    }

    // 5. Test timezone consistency
    public function test_5_timezone_consistency(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));

        // 08:15 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:15:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertEquals('terlambat', $attendance->status);
        $this->assertEquals(15, $attendance->late_minutes);
        $this->assertGreaterThan(0, $attendance->late_minutes);
    }

    // 6. Test date berbeda
    public function test_6_checkin_different_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 08:20:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->studentUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $response->assertRedirect(route('siswa.attendance.index'));

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertEquals('2026-11-15', $attendance->date->toDateString());
        $this->assertEquals('terlambat', $attendance->status);
        $this->assertEquals(20, $attendance->late_minutes);
    }

    // 7. Test history blade status display
    public function test_7_history_displays_terlambat_correctly(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->student->classroom_id,
            'date' => '2026-10-05',
            'check_in' => '08:25:00',
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
            'distance' => 20,
            'status' => 'terlambat',
            'late_minutes' => 25,
        ]);

        $historyResponse = $this->actingAs($this->studentUser)->get(route('siswa.attendance.index'));
        $historyResponse->assertOk();
        $historyResponse->assertSee('Terlambat');
        $historyResponse->assertSee('25 menit');
    }
}
