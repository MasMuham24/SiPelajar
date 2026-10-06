<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Major $major;
    protected Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->major = Major::create([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $this->classroom = Classroom::create([
            'major_id' => $this->major->id,
            'grade' => 10,
            'name' => '10 TJKT 1',
        ]);
    }

    public function test_admin_can_view_students_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.index'));
        $response->assertOk();
        $response->assertViewIs('admin.students.index');
    }

    public function test_admin_can_search_students(): void
    {
        $user = User::create([
            'name' => 'Budi Hermanto',
            'username' => '240099',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $user->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '240099',
            'nisn' => '9999888877',
            'name' => 'Budi Hermanto',
            'gender' => 'Laki-laki',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.students.index', ['search' => 'Budi']));
        $response->assertOk();
        $response->assertSee('Budi Hermanto');
    }

    public function test_admin_can_create_student(): void
    {
        $data = [
            'name' => 'Dewi Sartika',
            'nis' => '240100',
            'nisn' => '9999888800',
            'classroom_id' => $this->classroom->id,
            'gender' => 'Perempuan',
            'phone' => '081234567890',
            'address' => 'Demak Kota',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), $data);
        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '240100',
            'name' => 'Dewi Sartika',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => '240100',
            'name' => 'Dewi Sartika',
        ]);
    }

    public function test_admin_can_update_student(): void
    {
        $user = User::create([
            'name' => 'Doni Ramadhan',
            'username' => '240101',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '240101',
            'nisn' => '9999888801',
            'name' => 'Doni Ramadhan',
            'gender' => 'Laki-laki',
        ]);

        $updatedData = [
            'name' => 'Doni Ramadhan Updated',
            'nis' => '240101',
            'nisn' => '9999888801',
            'classroom_id' => $this->classroom->id,
            'gender' => 'Laki-laki',
            'phone' => '081299999999',
            'address' => 'Semarang',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.students.update', $student), $updatedData);
        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Doni Ramadhan Updated',
            'address' => 'Semarang',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Doni Ramadhan Updated',
        ]);
    }

    public function test_admin_can_delete_student(): void
    {
        $user = User::create([
            'name' => 'Hapus Siswa',
            'username' => '240102',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '240102',
            'nisn' => '9999888802',
            'name' => 'Hapus Siswa',
            'gender' => 'Laki-laki',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student));
        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
