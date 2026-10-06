<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendancePollingTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;
    protected Teacher $teacher;
    protected User $studentUser;
    protected Student $student;
    protected Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $major = Major::create([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $this->classroom = Classroom::create([
            'major_id' => $major->id,
            'grade' => 11,
            'name' => '11 TJKT 1',
        ]);

        $this->teacherUser = User::create([
            'name' => 'Guru Pengampu',
            'username' => 'guru_test',
            'email' => 'guru@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198501012010011001',
            'gender' => 'Laki-laki',
        ]);

        $this->studentUser = User::create([
            'name' => 'Ahmad Ibnu Khois',
            'username' => '26001001',
            'email' => 'ahmad@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Ahmad Ibnu Khois',
            'nis' => '26001001',
            'nisn' => '002600000001',
            'gender' => 'Laki-laki',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 1. Authorization: Tamu / Unauthenticated dilarang mengakses endpoint
    public function test_guest_cannot_access_attendance_data(): void
    {
        $response = $this->getJson(route('guru.attendance.data'));
        $response->assertUnauthorized();
    }

    // 2. Authorization: Siswa tidak boleh mengakses endpoint guru
    public function test_student_cannot_access_teacher_attendance_data(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('guru.attendance.data'));
        $response->assertForbidden();
    }

    // 3. Guru dapat mengambil data attendance JSON (kondisi kosong)
    public function test_teacher_can_fetch_empty_attendance_data(): void
    {
        $response = $this->actingAs($this->teacherUser)->getJson(route('guru.attendance.data'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [],
            'total' => 0,
        ]);
    }

    // 4. Data baru otomatis muncul di endpoint ketika siswa melakukan absensi
    public function test_attendance_data_reflects_new_student_checkin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 07:55:00', 'Asia/Jakarta'));

        // Buat absensi siswa
        Attendance::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-10-06',
            'check_in' => '07:55:00',
            'latitude' => -6.8951,
            'longitude' => 110.6177,
            'distance' => 25,
            'status' => 'hadir',
            'late_minutes' => 0,
        ]);

        $response = $this->actingAs($this->teacherUser)->getJson(route('guru.attendance.data'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total' => 1,
        ]);

        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('Ahmad Ibnu Khois', $responseData[0]['student_name']);
        $this->assertEquals('11 TJKT 1', $responseData[0]['classroom_name']);
        $this->assertEquals('07:55', $responseData[0]['check_in']);
        $this->assertEquals('Hadir', $responseData[0]['status_label']);
        $this->assertEquals('-', $responseData[0]['late_text']);
    }

    // 5. Data absensi terlambat menampilkan status_label dan late_text yang tepat
    public function test_attendance_data_reflects_late_student_checkin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 08:15:00', 'Asia/Jakarta'));

        Attendance::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-10-06',
            'check_in' => '08:15:00',
            'latitude' => -6.8951,
            'longitude' => 110.6177,
            'distance' => 30,
            'status' => 'terlambat',
            'late_minutes' => 15,
        ]);

        $response = $this->actingAs($this->teacherUser)->getJson(route('guru.attendance.data'));

        $response->assertOk();
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('Terlambat', $responseData[0]['status_label']);
        $this->assertEquals('15 menit', $responseData[0]['late_text']);
        $this->assertStringContainsString('bg-yellow-100', $responseData[0]['badge_class']);
    }

    // 6. Polling menghormati filter tanggal jika ada parameter ?date=
    public function test_attendance_data_respects_date_filter(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-10-05',
            'check_in' => '07:50:00',
            'status' => 'hadir',
            'late_minutes' => 0,
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-10-06',
            'check_in' => '07:58:00',
            'status' => 'hadir',
            'late_minutes' => 0,
        ]);

        // Request untuk tanggal 2026-10-05
        $response = $this->actingAs($this->teacherUser)->getJson(route('guru.attendance.data', ['date' => '2026-10-05']));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total' => 1,
            'date' => '2026-10-05',
        ]);
        $this->assertEquals('07:50', $response->json('data.0.check_in'));
    }

    // 7. Halaman Blade guru memuat tabel dengan ID attendanceTableBody dan script polling
    public function test_teacher_attendance_blade_contains_polling_mechanism(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('guru.attendance.index'));

        $response->assertOk();
        $response->assertSee('id="attendanceTableBody"', false);
        $response->assertSee('pollAttendance', false);
        $response->assertSee('5000', false); // interval 5 detik
    }
}
