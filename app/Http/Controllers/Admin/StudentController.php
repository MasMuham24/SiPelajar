<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StudentController extends Controller
{
    /**
     * Download template for bulk import (CSV or XLSX)
     */
    public function downloadTemplate(Request $request)
    {
        $format = strtolower($request->query('format', 'csv'));
        $headers = ['name', 'nis', 'nisn', 'jurusan', 'kelas', 'gender', 'phone', 'address'];
        $sampleRow = [
            'Ahmad Ibnu Khois',
            '26001001',
            '002600000001',
            'Teknik Jaringan Komputer dan Telekomunikasi',
            '11 TJKT 1',
            'Laki-laki',
            '081210000001',
            'Jl Pendidikan No 1, Demak',
        ];

        if ($format === 'xlsx') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Siswa');

            foreach ($headers as $colIndex => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValueExplicit($colLetter . '1', $header, DataType::TYPE_STRING);
                if (in_array($header, ['nis', 'nisn', 'phone'])) {
                    $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                }
            }

            foreach ($sampleRow as $colIndex => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValueExplicit($colLetter . '2', $val, DataType::TYPE_STRING);
            }

            foreach (range(1, count($headers)) as $colIndex) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'student_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="student_template.xlsx"',
            ]);
        }

        $callback = function () use ($headers, $sampleRow) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);
            fputcsv($file, $sampleRow);
            fclose($file);
        };

        return response()->streamDownload($callback, 'student_template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="student_template.csv"',
        ]);
    }

    /**
     * Import students from CSV or XLSX
     */
    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return redirect()->route('admin.students.index')->with('error', 'File wajib diunggah.');
        }

        $file = $request->file('file');

        if ($file->getSize() === 0) {
            return redirect()->route('admin.students.index')->with('error', 'File kosong.');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file tidak didukung. Gunakan file berekstensi .csv atau .xlsx.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.students.index')->with('error', $validator->errors()->first());
        }

        try {
            $rows = $this->parseImportFile($file);
        } catch (\Throwable $e) {
            return redirect()->route('admin.students.index')->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return redirect()->route('admin.students.index')->with('error', 'File kosong.');
        }

        $rawHeader = $rows[0] ?? [];
        if (empty($rawHeader)) {
            return redirect()->route('admin.students.index')->with('error', 'File tidak memiliki baris header.');
        }

        $aliasMap = [
            'name' => 'name',
            'nama' => 'name',
            'nama siswa' => 'name',
            'nama_siswa' => 'name',
            'nis' => 'nis',
            'nisn' => 'nisn',
            'jurusan' => 'jurusan',
            'major' => 'jurusan',
            'kelas' => 'kelas',
            'class' => 'kelas',
            'classroom' => 'kelas',
            'grade' => 'kelas',
            'gender' => 'gender',
            'jenis_kelamin' => 'gender',
            'jenis kelamin' => 'gender',
            'jk' => 'gender',
            'phone' => 'phone',
            'no_hp' => 'phone',
            'no hp' => 'phone',
            'nohp' => 'phone',
            'telepon' => 'phone',
            'no_telepon' => 'phone',
            'no telepon' => 'phone',
            'address' => 'address',
            'alamat' => 'address',
        ];

        $headerMap = [];
        foreach ($rawHeader as $index => $col) {
            $col = preg_replace('/^\xEF\xBB\xBF/', '', (string)$col);
            $clean = mb_strtolower(trim($col));
            $clean = preg_replace('/\s+/', ' ', $clean);
            $canonical = $aliasMap[$clean] ?? $clean;
            if (!isset($headerMap[$canonical])) {
                $headerMap[$canonical] = $index;
            }
        }

        $requiredColumns = ['name', 'nis', 'nisn', 'jurusan', 'kelas', 'gender', 'phone', 'address'];
        $missingColumns = [];
        foreach ($requiredColumns as $req) {
            if (!isset($headerMap[$req])) {
                $missingColumns[] = $req;
            }
        }

        if (!empty($missingColumns)) {
            return redirect()->route('admin.students.index')->with(
                'error',
                'Format header tidak sesuai. Kolom berikut tidak ditemukan: ' . implode(', ', $missingColumns) . '. Pastikan header memuat: ' . implode(', ', $requiredColumns)
            );
        }

        $dataRows = array_slice($rows, 1);
        if (empty($dataRows)) {
            return redirect()->route('admin.students.index')->with('error', 'File tidak memiliki baris data untuk diimpor.');
        }

        $errors = [];
        $validatedRecords = [];

        $seenNis = [];  // nis => rowNumber
        $seenNisn = []; // nisn => rowNumber

        $majors = Major::all();
        $majorMap = [];
        foreach ($majors as $m) {
            $majorMap[$this->normalizeLookup($m->code)] = $m;
            $majorMap[$this->normalizeLookup($m->name)] = $m;
        }

        $classrooms = Classroom::with('major')->get();
        $classroomMap = [];
        $classroomByName = [];
        foreach ($classrooms as $c) {
            $classroomMap[$c->major_id . '_' . $this->normalizeLookup($c->name)] = $c;
            $classroomByName[$this->normalizeLookup($c->name)][] = $c;
        }

        $rowNum = 1;
        foreach ($dataRows as $row) {
            $rowNum++;

            // Skip entirely empty row
            $hasData = false;
            foreach ($row as $val) {
                if (trim((string)$val) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) {
                continue;
            }

            $data = [];
            foreach ($requiredColumns as $col) {
                $colIndex = $headerMap[$col];
                $data[$col] = isset($row[$colIndex]) ? trim((string)$row[$colIndex]) : '';
            }

            $rowHasError = false;

            // 1. Name
            if ($data['name'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'name', 'value' => '-', 'message' => 'Nama wajib diisi.'];
            }

            // 2. NIS
            if ($data['nis'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'nis', 'value' => '-', 'message' => 'NIS wajib diisi.'];
            } else {
                if (isset($seenNis[$data['nis']])) {
                    $rowHasError = true;
                    $errors[] = ['row' => $rowNum, 'field' => 'nis', 'value' => $data['nis'], 'message' => "NIS {$data['nis']} duplicate dengan row {$seenNis[$data['nis']]}."];
                } else {
                    $seenNis[$data['nis']] = $rowNum;

                    if (Student::where('nis', $data['nis'])->exists() || User::where('username', $data['nis'])->exists()) {
                        $rowHasError = true;
                        $errors[] = ['row' => $rowNum, 'field' => 'nis', 'value' => $data['nis'], 'message' => 'NIS sudah digunakan.'];
                    }
                }
            }

            // 3. NISN
            if ($data['nisn'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'nisn', 'value' => '-', 'message' => 'NISN wajib diisi.'];
            } else {
                if (isset($seenNisn[$data['nisn']])) {
                    $rowHasError = true;
                    $errors[] = ['row' => $rowNum, 'field' => 'nisn', 'value' => $data['nisn'], 'message' => "NISN {$data['nisn']} duplicate dengan row {$seenNisn[$data['nisn']]}."];
                } else {
                    $seenNisn[$data['nisn']] = $rowNum;

                    if (Student::where('nisn', $data['nisn'])->exists()) {
                        $rowHasError = true;
                        $errors[] = ['row' => $rowNum, 'field' => 'nisn', 'value' => $data['nisn'], 'message' => 'NISN sudah digunakan.'];
                    }
                }
            }

            // 4. Jurusan
            $matchedMajor = null;
            if ($data['jurusan'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'jurusan', 'value' => '-', 'message' => 'Jurusan wajib diisi.'];
            } else {
                $normJurusan = $this->normalizeLookup($data['jurusan']);
                $matchedMajor = $majorMap[$normJurusan] ?? null;
                if (!$matchedMajor) {
                    $rowHasError = true;
                    $errors[] = ['row' => $rowNum, 'field' => 'jurusan', 'value' => $data['jurusan'], 'message' => "Jurusan '{$data['jurusan']}' tidak ditemukan."];
                }
            }

            // 5. Kelas
            $matchedClassroom = null;
            if ($data['kelas'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'kelas', 'value' => '-', 'message' => 'Kelas wajib diisi.'];
            } else {
                $normKelas = $this->normalizeLookup($data['kelas']);
                if ($matchedMajor) {
                    $matchedClassroom = $classroomMap[$matchedMajor->id . '_' . $normKelas] ?? null;
                    if (!$matchedClassroom) {
                        $rowHasError = true;
                        if (isset($classroomByName[$normKelas])) {
                            $errors[] = ['row' => $rowNum, 'field' => 'kelas', 'value' => $data['kelas'], 'message' => "Kelas '{$data['kelas']}' tidak sesuai dengan jurusan '{$data['jurusan']}'."];
                        } else {
                            $errors[] = ['row' => $rowNum, 'field' => 'kelas', 'value' => $data['kelas'], 'message' => 'Kelas tidak ditemukan.'];
                        }
                    }
                } else {
                    $rowHasError = true;
                    if (!isset($classroomByName[$normKelas])) {
                        $errors[] = ['row' => $rowNum, 'field' => 'kelas', 'value' => $data['kelas'], 'message' => 'Kelas tidak ditemukan.'];
                    }
                }
            }

            // 6. Gender
            $normalizedGender = null;
            if ($data['gender'] === '') {
                $rowHasError = true;
                $errors[] = ['row' => $rowNum, 'field' => 'gender', 'value' => '-', 'message' => 'Gender wajib diisi.'];
            } else {
                if (strcasecmp($data['gender'], 'Laki-laki') === 0) {
                    $normalizedGender = 'Laki-laki';
                } elseif (strcasecmp($data['gender'], 'Perempuan') === 0) {
                    $normalizedGender = 'Perempuan';
                } else {
                    $rowHasError = true;
                    $errors[] = ['row' => $rowNum, 'field' => 'gender', 'value' => $data['gender'], 'message' => 'Gender harus Laki-laki atau Perempuan.'];
                }
            }

            // 7. Phone
            if ($data['phone'] !== '') {
                if (!preg_match('/^[0-9]+$/', $data['phone'])) {
                    $rowHasError = true;
                    $errors[] = ['row' => $rowNum, 'field' => 'phone', 'value' => $data['phone'], 'message' => 'Nomor handphone harus berupa angka.'];
                }
            }

            if (!$rowHasError && $matchedClassroom && $normalizedGender) {
                $validatedRecords[] = [
                    'name' => $data['name'],
                    'nis' => $data['nis'],
                    'nisn' => $data['nisn'],
                    'classroom_id' => $matchedClassroom->id,
                    'gender' => $normalizedGender,
                    'phone' => $data['phone'] !== '' ? $data['phone'] : null,
                    'address' => $data['address'] !== '' ? $data['address'] : null,
                ];
            }
        }

        if (empty($validatedRecords) && empty($errors)) {
            return redirect()->route('admin.students.index')->with('error', 'File tidak memiliki data siswa untuk diimpor.');
        }

        if (!empty($errors)) {
            $errorBlocks = ['<strong>Import gagal.</strong>'];
            foreach ($errors as $err) {
                $errorBlocks[] = "Row {$err['row']}<br>Field: {$err['field']}<br>Value: {$err['value']}<br>Error: {$err['message']}";
            }
            return redirect()->route('admin.students.index')->with('error', implode('<br><br>', $errorBlocks));
        }

        DB::beginTransaction();
        try {
            $importedCount = 0;
            foreach ($validatedRecords as $record) {
                $user = User::create([
                    'name' => $record['name'],
                    'username' => $record['nis'],
                    'password' => bcrypt('123456'),
                    'role' => 'siswa',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'classroom_id' => $record['classroom_id'],
                    'nis' => $record['nis'],
                    'nisn' => $record['nisn'],
                    'gender' => $record['gender'],
                    'phone' => $record['phone'],
                    'address' => $record['address'],
                    'name' => $record['name'],
                ]);

                $importedCount++;
            }

            DB::commit();
            return redirect()->route('admin.students.index')->with('success', "Berhasil mengimpor {$importedCount} data siswa.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin.students.index')->with('error', 'Gagal mengimpor data. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Parse uploaded CSV or Excel file into array of rows
     */
    private function parseImportFile($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $path = $file->getRealPath();

        if (in_array($extension, ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            return $sheet->toArray(null, true, false, false) ?: [];
        }

        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($path, 'r');
            if (!$handle) {
                throw new \Exception('Gagal membuka file CSV.');
            }

            $firstLine = fgets($handle);
            $delimiter = ',';
            if ($firstLine !== false) {
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                }
            }
            rewind($handle);

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
            return $rows;
        }

        throw new \Exception('Format file tidak didukung.');
    }

    /**
     * Normalize lookup strings (trim, lowercase, multiple spaces to single space)
     */
    private function normalizeLookup(string $str): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($str)));
    }

    /**
     * Display a listing of students
     */
    public function index()
    {
        $students = Student::with(['user', 'classroom.major'])->when(request('search'), function ($query) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")->orWhereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', "%{$search}%");
                });
            });
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index',
            compact('students')
        );
    }

    /**
     * Show create form
     */
    public function create()
    {
        $classrooms = Classroom::with('major')->get();
        return view('admin.students.create',compact('classrooms'));
    }

    /**
     * Store student
     */
    public function store(StudentRequest $request)
    {

        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, &$student) {
                $user = User::create([
                    'name' => $data['name'],
                    'username' => $data['nis'],
                    'password' => bcrypt('123456'),
                    'role' => 'siswa',
                ]);

                $data['user_id'] = $user->id;

                if ($request->hasFile('photo')) { $data['photo'] = $request->file('photo')->store('students', 'public'); }
                $student = Student::create($data);
            });

            return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->route('admin.students.create')->with('error', 'Gagal menambahkan siswa. NISN atau username sudah digunakan.');
            }
            throw $e;
        }

    }

    /**
     * Display detail student
     */
    public function show(Student $student)
    {

        $student->load(['user','classroom.major','submissions']);
        return view('admin.students.show',compact('student'));
    }

    /**
     * Show edit form
     */
    public function edit(Student $student)
    {
        $classrooms = Classroom::with('major')->get();
        return view('admin.students.edit',compact('student','classrooms'));
    }

    /**
     * Update student
     */
    public function update(StudentRequest $request, Student $student)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $student) {
                if ($request->hasFile('photo')) {
                    if ($student->photo) {
                        Storage::disk('public')->delete($student->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('students', 'public');
                }

                $student->update($data);

                $student->user()->update([
                    'name' => $data['name'],
                    'username' => $data['nis'],
                ]);
            });

            return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->route('admin.students.edit', $student)->with('error', 'Gagal memperbarui siswa. NIS atau username sudah digunakan.');
            }
            throw $e;
        }
    }

    /**
     * Delete student
     */
    public function destroy(Student $student)
    {

        if ($student->photo) {Storage::disk('public')->delete($student->photo);}

        $student->user()->delete();
        $student->delete();
        return redirect()->route('admin.students.index')->with('success','Data siswa berhasil dihapus');

    }
}
