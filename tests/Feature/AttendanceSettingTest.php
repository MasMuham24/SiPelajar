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

class AttendanceSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $guruUser;
    protected User $siswaUser;
    protected Student $student;
    protected Office $office;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin SiPelajar',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->guruUser = User::create([
            'name' => 'Guru Test',
            'username' => 'guru_test',
            'email' => 'guru@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->siswaUser = User::create([
            'name' => 'Siswa Test',
            'username' => 'siswa_test',
            'email' => 'siswa@test.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $major = Major::create([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $classroom = Classroom::create([
            'major_id' => $major->id,
            'grade' => 11,
            'name' => '11 TJKT 1',
        ]);

        $this->student = Student::create([
            'user_id' => $this->siswaUser->id,
            'classroom_id' => $classroom->id,
            'name' => 'Siswa Test',
            'nis' => '26009999',
            'nisn' => '002600009999',
            'gender' => 'Laki-laki',
        ]);

        $this->office = Office::create([
            'name' => 'SMK SiPelajar',
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
            'radius' => 500,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 1. Halaman setting dapat dibuka oleh role yang diizinkan (admin)
    public function test_admin_can_open_attendance_settings_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendance-settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Jam Absensi');
        $response->assertSee('Jam Masuk');
        $response->assertSee('Jam Pulang');
    }

    // 2. Role yang tidak berhak tidak bisa membuka atau mengubah setting (guru & siswa 403, guest redirect)
    public function test_unauthorized_roles_cannot_access_or_update_settings(): void
    {
        // Guest
        $guestResponse = $this->get(route('admin.attendance-settings.index'));
        $guestResponse->assertRedirect(route('login'));

        // Guru
        $guruGetResponse = $this->actingAs($this->guruUser)->get(route('admin.attendance-settings.index'));
        $guruGetResponse->assertForbidden();

        $guruPutResponse = $this->actingAs($this->guruUser)->put(route('admin.attendance-settings.update'), [
            'school_start_time' => '07:30',
            'school_end_time' => '15:30',
        ]);
        $guruPutResponse->assertForbidden();

        // Siswa
        $siswaGetResponse = $this->actingAs($this->siswaUser)->get(route('admin.attendance-settings.index'));
        $siswaGetResponse->assertForbidden();

        $siswaPutResponse = $this->actingAs($this->siswaUser)->put(route('admin.attendance-settings.update'), [
            'school_start_time' => '07:30',
            'school_end_time' => '15:30',
        ]);
        $siswaPutResponse->assertForbidden();
    }

    // 3. Setting dapat disimpan dan perubahan jam pulang tersimpan
    public function test_admin_can_save_and_update_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('admin.attendance-settings.update'), [
            'school_start_time' => '07:15',
            'school_end_time' => '15:45',
        ]);

        $response->assertRedirect(route('admin.attendance-settings.index'));
        $response->assertSessionHas('success', 'Pengaturan jam absensi berhasil diperbarui.');

        $setting = AttendanceSetting::getSettings();
        $this->assertEquals('07:15', $setting->getFormattedStartTime());
        $this->assertEquals('15:45', $setting->getFormattedEndTime());
    }

    // 4. Validasi gagal jika jam pulang <= jam masuk
    public function test_validation_fails_when_school_end_time_is_less_than_or_equal_to_start_time(): void
    {
        // Jam pulang lebih kecil daripada jam masuk
        $response1 = $this->actingAs($this->adminUser)->put(route('admin.attendance-settings.update'), [
            'school_start_time' => '08:00',
            'school_end_time' => '07:00',
        ]);
        $response1->assertSessionHasErrors('school_end_time');

        // Jam pulang sama dengan jam masuk
        $response2 = $this->actingAs($this->adminUser)->put(route('admin.attendance-settings.update'), [
            'school_start_time' => '07:00',
            'school_end_time' => '07:00',
        ]);
        $response2->assertSessionHasErrors('school_end_time');
    }

    // 5. Perubahan jam masuk mempengaruhi status absensi siswa (tepat waktu vs terlambat)
    public function test_changing_start_time_affects_student_attendance_status(): void
    {
        // Set jam masuk ke 07:00
        $setting = AttendanceSetting::getSettings();
        $setting->update([
            'school_start_time' => '07:00:00',
            'school_end_time' => '15:00:00',
        ]);

        // Siswa absen pada 07:10 (setelah 07:00 -> Terlambat)
        Carbon::setTestNow(Carbon::parse('2026-10-06 07:10:00', 'Asia/Jakarta'));

        $this->actingAs($this->siswaUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $attendance1 = Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-06')->first();
        $this->assertNotNull($attendance1);
        $this->assertEquals('terlambat', $attendance1->status);
        $this->assertEquals(10, $attendance1->late_minutes);

        // Sekarang ubah jam masuk sekolah menjadi 07:30
        $setting->update([
            'school_start_time' => '07:30:00',
        ]);

        // Siswa absen di hari berikutnya pada 07:10 (sebelum 07:30 -> Tepat Waktu / Hadir)
        Carbon::setTestNow(Carbon::parse('2026-10-07 07:10:00', 'Asia/Jakarta'));

        $this->actingAs($this->siswaUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        $attendance2 = Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-07')->first();
        $this->assertNotNull($attendance2);
        $this->assertEquals('hadir', $attendance2->status);
        $this->assertEquals(0, $attendance2->late_minutes);
    }

    // 6. Perubahan jam pulang mempengaruhi validasi checkout absensi siswa
    public function test_changing_end_time_affects_student_checkout_validation(): void
    {
        // Set jam pulang sekolah ke 14:00
        $setting = AttendanceSetting::getSettings();
        $setting->update([
            'school_start_time' => '07:00:00',
            'school_end_time' => '14:00:00',
        ]);

        // Buat absensi masuk hari ini
        Carbon::setTestNow(Carbon::parse('2026-10-06 06:50:00', 'Asia/Jakarta'));
        $this->actingAs($this->siswaUser)->post(route('siswa.attendance.checkin'), [
            'latitude' => -6.8951427,
            'longitude' => 110.6177293,
        ]);

        // Siswa checkout pada 13:30 (belum jam 14:00 -> Ditolak)
        Carbon::setTestNow(Carbon::parse('2026-10-06 13:30:00', 'Asia/Jakarta'));
        $checkoutFailResponse = $this->actingAs($this->siswaUser)->post(route('siswa.attendance.checkout'));
        $checkoutFailResponse->assertSessionHas('error');

        // Siswa checkout pada 14:05 (sudah lewat 14:00 -> Berhasil)
        Carbon::setTestNow(Carbon::parse('2026-10-06 14:05:00', 'Asia/Jakarta'));
        $checkoutSuccessResponse = $this->actingAs($this->siswaUser)->post(route('siswa.attendance.checkout'));
        $checkoutSuccessResponse->assertSessionHas('success', 'Absensi pulang berhasil.');

        $attendance = Attendance::where('student_id', $this->student->id)->whereDate('date', '2026-10-06')->first();
        $this->assertNotNull($attendance->check_out);
    }
}
