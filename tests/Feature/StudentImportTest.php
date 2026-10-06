<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportTest extends TestCase
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
            'grade' => 11,
            'name' => '11 TJKT 1',
        ]);
    }

    private function createCsvFile(string $content, string $filename = 'import.csv'): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'csv_') . '.csv';
        file_put_contents($tempPath, $content);
        return new UploadedFile($tempPath, $filename, 'text/csv', null, true);
    }

    private function createXlsxFile(array $rows, string $filename = 'import.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValueExplicit($colLetter . ($rowIndex + 1), (string) $val, DataType::TYPE_STRING);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    // 1. CSV valid
    public function test_1_import_valid_csv(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Ahmad Ibnu Khois",26001001,"002600000001","Teknik Jaringan Komputer dan Telekomunikasi","11 TJKT 1","Laki-laki","081210000001","Jl Pendidikan No 1, Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'Ahmad Ibnu Khois',
            'nis' => '26001001',
            'nisn' => '002600000001',
            'classroom_id' => $this->classroom->id,
            'gender' => 'Laki-laki',
            'phone' => '081210000001',
            'address' => 'Jl Pendidikan No 1, Demak',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Ahmad Ibnu Khois',
            'username' => '26001001',
            'role' => 'siswa',
        ]);
    }

    // 2. XLSX valid
    public function test_2_import_valid_xlsx(): void
    {
        $rows = [
            ['name', 'nis', 'nisn', 'jurusan', 'kelas', 'gender', 'phone', 'address'],
            ['Siti Fatimah', '26001002', '002600000002', 'TJKT', '11 TJKT 1', 'Perempuan', '081210000002', 'Jl Merdeka No 2'],
        ];

        $file = $this->createXlsxFile($rows);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'Siti Fatimah',
            'nis' => '26001002',
            'nisn' => '002600000002',
            'classroom_id' => $this->classroom->id,
            'gender' => 'Perempuan',
            'phone' => '081210000002',
            'address' => 'Jl Merdeka No 2',
        ]);
    }

    // 3. CSV dengan comma dalam address
    public function test_3_csv_with_comma_in_address(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Budi Santoso",26001003,"002600000003","TJKT","11 TJKT 1","Laki-laki","081210000003","Gang Kelinci, RT 01, RW 02, Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '26001003',
            'address' => 'Gang Kelinci, RT 01, RW 02, Demak',
        ]);
    }

    // 4. UTF-8 BOM
    public function test_4_utf8_bom(): void
    {
        $csv = "\xEF\xBB\xBFname,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Citra Lestari",26001004,"002600000004","TJKT","11 TJKT 1","Perempuan","081210000004","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '26001004',
            'name' => 'Citra Lestari',
        ]);
    }

    // 5. Header uppercase
    public function test_5_header_uppercase(): void
    {
        $csv = "NAME,NIS,NISN,JURUSAN,KELAS,GENDER,PHONE,ADDRESS\n" .
            '"Dewi Ayu",26001005,"002600000005","TJKT","11 TJKT 1","Perempuan","081210000005","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '26001005',
            'name' => 'Dewi Ayu',
        ]);
    }

    // 6. Header dengan whitespace
    public function test_6_header_with_whitespace(): void
    {
        $csv = "  name  ,  nis  ,  nisn  ,  jurusan  ,  kelas  ,  gender  ,  phone  ,  address  \n" .
            '"Eko Prasetyo",26001006,"002600000006","TJKT","11 TJKT 1","Laki-laki","081210000006","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '26001006',
            'name' => 'Eko Prasetyo',
        ]);
    }

    // 7. Column order berbeda
    public function test_7_different_column_order(): void
    {
        $csv = "nisn,address,name,gender,kelas,phone,jurusan,nis\n" .
            '"002600000007","Alamat Berbeda","Fajar Pratama","Laki-laki","11 TJKT 1","081210000007","TJKT",26001007';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'name' => 'Fajar Pratama',
            'nis' => '26001007',
            'nisn' => '002600000007',
            'classroom_id' => $this->classroom->id,
            'address' => 'Alamat Berbeda',
        ]);
    }

    // 8. Missing header
    public function test_8_missing_header(): void
    {
        $csv = "name,nis,jurusan,kelas,gender,phone,address\n" .
            '"Gilang",26001008,"TJKT","11 TJKT 1","Laki-laki","081210000008","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('nisn', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 9. Invalid gender
    public function test_9_invalid_gender(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Hani",26001009,"002600000009","TJKT","11 TJKT 1","L","081210000009","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Gender harus Laki-laki atau Perempuan', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 10. Jurusan tidak ditemukan
    public function test_10_jurusan_not_found(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Indah",26001010,"002600000010","TATA BOGA","11 TJKT 1","Perempuan","081210000010","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Jurusan', session('error'));
        $this->assertStringContainsString('tidak ditemukan', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 11. Kelas tidak ditemukan
    public function test_11_kelas_not_found(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Joko",26001011,"002600000011","TJKT","11 TJKT 9","Laki-laki","081210000011","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Kelas tidak ditemukan', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 12. Duplicate NIS di database
    public function test_12_duplicate_nis_in_database(): void
    {
        $existingUser = User::create([
            'name' => 'Existing Student',
            'username' => '26001012',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $existingUser->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Existing Student',
            'nis' => '26001012',
            'nisn' => '999999999999',
            'gender' => 'Laki-laki',
        ]);

        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Kiki",26001012,"002600000012","TJKT","11 TJKT 1","Laki-laki","081210000012","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('NIS sudah digunakan', session('error'));
        $this->assertEquals(1, Student::count());
    }

    // 13. Duplicate NISN di database
    public function test_13_duplicate_nisn_in_database(): void
    {
        $existingUser = User::create([
            'name' => 'Existing Student',
            'username' => '99999999',
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $existingUser->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Existing Student',
            'nis' => '99999999',
            'nisn' => '002600000013',
            'gender' => 'Perempuan',
        ]);

        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Lina",26001013,"002600000013","TJKT","11 TJKT 1","Perempuan","081210000013","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('NISN sudah digunakan', session('error'));
        $this->assertEquals(1, Student::count());
    }

    // 14. Duplicate dalam file yang sama
    public function test_14_duplicate_within_same_file(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Mira",26001014,"002600000014","TJKT","11 TJKT 1","Perempuan","081210000014","Demak"' . "\n" .
            '"Nanda",26001014,"002600000015","TJKT","11 TJKT 1","Perempuan","081210000015","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('duplicate dengan row 2', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 15. NIS leading zero
    public function test_15_nis_leading_zero_preserved(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Oki","026001","002600000015","TJKT","11 TJKT 1","Laki-laki","081210000015","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '026001',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => '026001',
        ]);
    }

    // 16. NISN leading zero
    public function test_16_nisn_leading_zero_preserved(): void
    {
        $rows = [
            ['name', 'nis', 'nisn', 'jurusan', 'kelas', 'gender', 'phone', 'address'],
            ['Putri', '26001016', '002600000016', 'TJKT', '11 TJKT 1', 'Perempuan', '081210000016', 'Demak'],
        ];

        $file = $this->createXlsxFile($rows);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nisn' => '002600000016',
        ]);
    }

    // 17. Phone leading zero
    public function test_17_phone_leading_zero_preserved(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Qori",26001017,"002600000017","TJKT","11 TJKT 1","Perempuan","081210000017","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'phone' => '081210000017',
        ]);
    }

    // 18. Empty file
    public function test_18_empty_file(): void
    {
        $file = $this->createCsvFile('');

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('File kosong', session('error'));
        $this->assertEquals(0, Student::count());
    }

    // 19. Invalid extension
    public function test_19_invalid_extension(): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        file_put_contents($tempPath, '%PDF-1.4 dummy');
        $file = new UploadedFile($tempPath, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(0, Student::count());
    }

    // 20. Rollback ketika salah satu row invalid (Atomic)
    public function test_20_atomic_rollback_on_any_invalid_row(): void
    {
        $csv = "name,nis,nisn,jurusan,kelas,gender,phone,address\n" .
            '"Rian",26001020,"002600000020","TJKT","11 TJKT 1","Laki-laki","081210000020","Demak"' . "\n" .
            '"Salsa",26001021,"002600000021","TJKT","11 TJKT 99","Perempuan","081210000021","Demak"';

        $file = $this->createCsvFile($csv);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Kelas tidak ditemukan', session('error'));

        // Neither Rian nor Salsa should be in the database
        $this->assertEquals(0, Student::count());
        $this->assertDatabaseMissing('users', ['username' => '26001020']);
    }

    // 21. Download Template CSV
    public function test_21_download_template_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.template', ['format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('name,nis,nisn,jurusan,kelas,gender,phone,address', $response->streamedContent());
    }

    // 22. Download Template XLSX
    public function test_22_download_template_xlsx(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.template', ['format' => 'xlsx']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
