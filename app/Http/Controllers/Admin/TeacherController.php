<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory; // Tambahkan ini

class TeacherController extends Controller
{
    /**
     * Download template for bulk import
     */
    public function downloadTemplate()
    {
        $headers = ['name', 'nip', 'gender', 'phone', 'address'];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->streamDownload($callback, 'teacher_template.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="teacher_template.csv"',
        ]);
    }

    /**
     * Import teachers from CSV or Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = [];

        if ($extension === 'xlsx' || $extension === 'xls') {
            try {
                $spreadsheet = IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
            } catch (\Exception $e) {
                return redirect()->route('admin.teachers.index')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
            }
        } else {
            $handle = fopen($path, 'r');
            if (!$handle) {
                return redirect()->route('admin.teachers.index')->with('error', 'Gagal membuka file CSV.');
            }
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return redirect()->route('admin.teachers.index')->with('error', 'File kosong.');
        }

        $header = array_map(function($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$h)));
        }, $rows[0]);

        $expectedHeaders = ['name', 'nip', 'gender', 'phone', 'address'];
        if (array_diff($expectedHeaders, $header) !== array() || array_diff($header, $expectedHeaders) !== array()) {
            return redirect()->route('admin.teachers.index')->with('error', 'Format header tidak sesuai. Pastikan header adalah: ' . implode(', ', $expectedHeaders));
        }

        $importedCount = 0;
        $errorRows = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $row) {
                $rowNum++;

                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($header) !== count($row)) {
                    $errorRows[] = "Baris {$rowNum}: Jumlah kolom tidak cocok dengan header.";
                    continue;
                }

                $data = array_combine($header, $row);
                $data = array_map(function($val) {
                    return trim((string)$val);
                }, $data);

                if (empty($data['name']) || empty($data['nip']) || empty($data['gender'])) {
                    $errorRows[] = "Baris {$rowNum}: Kolom name, nip, dan gender wajib diisi.";
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

                if (Teacher::query()->where('nip', $data['nip'])->exists()) {
                    $errorRows[] = "Baris {$rowNum}: NIP '{$data['nip']}' sudah terdaftar.";
                    continue;
                }

                $username = $this->generateUsername($data['name']);

                $user = User::create([
                    'name' => $data['name'],
                    'username' => $username,
                    'password' => bcrypt('123456'),
                    'role' => 'guru',
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $data['nip'],
                    'gender' => $data['gender'],
                    'phone' => empty($data['phone']) ? null : $data['phone'],
                    'address' => empty($data['address']) ? null : $data['address'],
                ]);

                $importedCount++;
            }

            if (count($errorRows) > 0) {
                DB::rollBack();
                return redirect()->route('admin.teachers.index')->with('error', 'Gagal mengimpor data. Kesalahan:<br>' . implode('<br>', $errorRows));
            }

            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', "Berhasil mengimpor {$importedCount} data guru.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.teachers.index')->with('error', 'Gagal mengimpor data. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the teachers.
     */
    public function index()
    {
        $teachers = Teacher::with(['user'])->when(request('search'), function ($query) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")->orWhereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', "%{$search}%");
                });
            });
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.teachers.index',
            compact('teachers')
        );
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(TeacherRequest $request)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request) {
                $username = $this->generateUsername($data['name']);

                $user = User::create([
                    'name' => $data['name'],
                    'username' => $username,
                    'password' => bcrypt('123456'),
                    'role' => 'guru',
                ]);

                $data['user_id'] = $user->id;

                if ($request->hasFile('photo')) {
                    $data['photo'] = $request->file('photo')->store('teachers', 'public');
                }
                Teacher::create($data);
            });

            return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->route('admin.teachers.create')->with('error', 'Gagal menambahkan guru. Username sudah digunakan.');
            }
            throw $e;
        }
    }

    protected function generateUsername(string $name, ?int $excludeUserId = null): string
    {
        $words = preg_split('/\s+/', trim($name));
        $firstTwoWords = array_slice($words, 0, 2);
        $username = strtolower(implode('', $firstTwoWords));

        $counter = 1;
        $originalUsername = $username;
        $query = User::where('username', $username);
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }
        while ($query->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
            $query = User::where('username', $username);
            if ($excludeUserId) {
                $query->where('id', '!=', $excludeUserId);
            }
        }

        return $username;
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load('user');
        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(TeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $teacher) {
                if ($request->hasFile('photo')) {
                    if ($teacher->photo) {
                        Storage::disk('public')->delete($teacher->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('teachers', 'public');
                }

                $teacher->update($data);

                $username = $this->generateUsername($data['name'], $teacher->user->id);

                $teacher->user()->update([
                    'name' => $data['name'],
                    'username' => $username,
                ]);
            });

            return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->route('admin.teachers.edit', $teacher)->with('error', 'Gagal memperbarui guru. Username sudah digunakan.');
            }
            throw $e;
        }
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(Teacher $teacher)
    {
        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }

        $teacher->user()->delete();
        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
