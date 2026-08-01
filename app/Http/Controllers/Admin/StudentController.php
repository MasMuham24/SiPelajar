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

class StudentController extends Controller
{
    /**
     * Download template for bulk import
     */
    public function downloadTemplate()
    {
        $headers = ['name', 'nis', 'nisn', 'jurusan', 'grade', 'gender', 'phone', 'address'];
        
        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->streamDownload($callback, 'student_template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="student_template.csv"',
        ]);
    }

    /**
     * Import students from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        
        if (!$header) {
            fclose($handle);
            return redirect()->route('admin.students.index')->with('error', 'File CSV kosong.');
        }

        // Clean headers from UTF-8 BOM or non-printable chars
        $header = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $header);

        $expectedHeaders = ['name', 'nis', 'nisn', 'jurusan', 'grade', 'gender', 'phone', 'address'];
        if (array_diff($expectedHeaders, $header) !== array_diff($header, $expectedHeaders)) {
            fclose($handle);
            return redirect()->route('admin.students.index')->with('error', 'Format header CSV tidak sesuai. Pastikan header adalah: ' . implode(', ', $expectedHeaders));
        }

        $importedCount = 0;
        $errorRows = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($header) !== count($row)) {
                    $errorRows[] = "Baris {$rowNum}: Jumlah kolom tidak cocok dengan header.";
                    continue;
                }

                $data = array_combine($header, $row);
                $data = array_map('trim', $data);

                // Validation
                if (empty($data['name']) || empty($data['nis']) || empty($data['nisn']) || empty($data['jurusan']) || empty($data['grade']) || empty($data['gender'])) {
                    $errorRows[] = "Baris {$rowNum}: Kolom name, nis, nisn, jurusan, grade, dan gender wajib diisi.";
                    continue;
                }

                if (!in_array($data['gender'], ['Laki-laki', 'Perempuan'])) {
                    $errorRows[] = "Baris {$rowNum}: Gender harus 'Laki-laki' atau 'Perempuan'.";
                    continue;
                }

                if (!empty($data['phone']) && !is_numeric($data['phone'])) {
                    $errorRows[] = "Baris {$rowNum}: Nomor handphone harus berupa angka.";
                    continue;
                }

// Check classroom
                $major = Major::where('code', $data['jurusan'])->orWhere('name', $data['jurusan'])->first();
                if (!$major) {
                    $errorRows[] = "Baris {$rowNum}: Jurusan '{$data['jurusan']}' tidak ditemukan.";
                    continue;
                }

                $classroom = Classroom::where('major_id', $major->id)
                    ->where('grade', $data['grade'])
                    ->first();

                if (!$classroom) {
                    $errorRows[] = "Baris {$rowNum}: Kelas untuk Jurusan '{$data['jurusan']}' Tingkat '{$data['grade']}' tidak ditemukan.";
                    continue;
                }

                // Check uniques
                if (Student::where('nis', $data['nis'])->exists()) {
                    $errorRows[] = "Baris {$rowNum}: NIS '{$data['nis']}' sudah terdaftar.";
                    continue;
                }

                if (Student::where('nisn', $data['nisn'])->exists()) {
                    $errorRows[] = "Baris {$rowNum}: NISN '{$data['nisn']}' sudah terdaftar.";
                    continue;
                }

                if (User::where('username', $data['nis'])->exists()) {
                    $errorRows[] = "Baris {$rowNum}: Username '{$data['nis']}' sudah terdaftar.";
                    continue;
                }

                // Create User
                $user = User::create([
                    'name' => $data['name'],
                    'username' => $data['nis'],
                    'password' => bcrypt('123456'), // default password 123456
                    'role' => 'siswa',
                ]);

                // Create Student
                Student::create([
                    'user_id' => $user->id,
                    'classroom_id' => $classroom->id,
                    'nis' => $data['nis'],
                    'nisn' => $data['nisn'],
                    'gender' => $data['gender'],
                    'phone' => empty($data['phone']) ? null : $data['phone'],
                    'address' => empty($data['address']) ? null : $data['address'],
                    'name' => $data['name'],
                ]);

                $importedCount++;
            }

            if (count($errorRows) > 0) {
                DB::rollBack();
                fclose($handle);
                return redirect()->route('admin.students.index')->with('error', 'Gagal mengimpor data. Kesalahan:<br>' . implode('<br>', $errorRows));
            }

            DB::commit();
            fclose($handle);
            return redirect()->route('admin.students.index')->with('success', "Berhasil mengimpor {$importedCount} data siswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('admin.students.index')->with('error', 'Gagal mengimpor data. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
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
