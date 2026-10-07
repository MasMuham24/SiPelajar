<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    private function ensureOwnership(Assignment $assignment): void
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        if (! $teacherId || $assignment->teacher_id !== $teacherId) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = Assignment::with(['classroom', 'teacher.user'])->where('teacher_id', Teacher::where('user_id', Auth::id())->value('id'))->latest()->paginate(10);
        return view('teacher.assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classrooms = Classroom::all();
        return view('teacher.assignments.create', compact('classrooms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssignmentRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('assignments', 'public');
        }
        $data['teacher_id'] = Teacher::where('user_id', Auth::id())->value('id');
        Assignment::create($data);
        return redirect()->route('guru.assignments.index')->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Assignment $assignment)
    {
        $this->ensureOwnership($assignment);
        return view('teacher.assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Assignment $assignment)
    {
        $this->ensureOwnership($assignment);
        $classrooms = Classroom::all();
        return view('teacher.assignments.edit', compact('assignment', 'classrooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssignmentRequest $request, Assignment $assignment)
    {
        $this->ensureOwnership($assignment);
        $data = $request->validated();
        if ($request->hasFile('attachment')) {
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('assignments', 'public');
        }
        $assignment->update($data);
        return redirect()->route('guru.assignments.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assignment $assignment)
    {
        $this->ensureOwnership($assignment);
        if ($assignment->attachment) {
            Storage::disk('public')->delete($assignment->attachment);
        }
        $assignment->delete();
        return redirect()->route('guru.assignments.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function bulkDestroy()
    {
        $rawIds = request()->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_filter(array_map('trim', $ids));
        if (empty($ids)) {
            return redirect()->route('guru.assignments.index')->with('error', 'Pilih minimal 1 tugas untuk dihapus.');
        }
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        $assignments = Assignment::whereIn('id', $ids)->where('teacher_id', $teacherId)->get();
        foreach ($assignments as $assignment) {
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            $assignment->delete();
        }
        return redirect()->route('guru.assignments.index')->with('success', 'Berhasil menghapus ' . $assignments->count() . ' tugas.');
    }

    public function end(Assignment $assignment)
    {
        $this->ensureOwnership($assignment);
        $assignment->update(['is_active' => false]);
        return redirect()->route('guru.assignments.index')->with('success', 'Tugas telah diakhiri. Siswa tidak dapat lagi mengirim atau mengedit jawaban.');
    }
}
