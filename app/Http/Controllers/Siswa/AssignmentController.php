<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        if (!$student) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }
        $assignments = Assignment::with(['teacher.user'])->where('classroom_id', $student->classroom_id)->latest()->paginate(10);
        return view('siswa.assignments.index', compact('assignments'));
    }

    public function show(Assignment $assignment)
    {
        $student = Auth::user()->student;
        if ($assignment->classroom_id !== $student->classroom_id) {
            abort(403);
        }
        $submission = Submission::where('assignment_id', $assignment->id)->where('student_id', $student->id)->first();
        return view('siswa.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(SubmissionRequest $request, Assignment $assignment)
    {
        $student = Auth::user()->student;
        if ($assignment->classroom_id !== $student->classroom_id) {
            abort(403);
        }
        if (!$assignment->is_active) {
            return back()->with('error', 'Tugas sudah ditutup, tidak dapat mengirim jawaban.');
        }
        if (!$request->file('file') && !$request->input('link')) {
            return back()->with('error', 'Pilih salah satu: Unggah file atau isi link jawaban.');
        }
        $existing = Submission::where('assignment_id', $assignment->id)->where('student_id', $student->id)->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah mengirim jawaban. Gunakan fitur edit untuk mengubahnya');
        }
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('submissions', 'public');
        }
        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'file'          => $filePath,
            'link'          => $request->input('link'),
            'submitted_at'  => now(),
        ]);
        return redirect()->route('siswa.assignments.show', $assignment)->with('success', 'Jawaban berhasil dikirim.');
    }

    public function updateSubmission(SubmissionRequest $request, Assignment $assignment)
    {
        $student = Auth::user()->student;
        if ($assignment->classroom_id !== $student->classroom_id) {
            abort(403);
        }
        if (!$assignment->is_active) {
            return back()->with('error', 'Tugas sudah ditutup, tidak dapat mengedit jawaban.');
        }
        if (!$request->file('file') && !$request->input('link')) {
            return back()->with('error', 'Pilih salah satu: Unggah file atau isi link jawaban.');
        }
        $submission = Submission::where('assignment_id', $assignment->id)->where('student_id', $student->id)->firstOrFail();
        $data = [
            'submitted_at' => now(),
            'link' => $request->input('link'),
        ];

        if ($request->hasFile('file')) {
            if ($submission->file) {
                Storage::disk('public')->delete($submission->file);
            }
            $data['file'] = $request->file('file')->store('submissions', 'public');
        }
        $submission->update($data);
        return redirect()->route('siswa.assignments.show', $assignment)->with('success', 'Jawaban berhasil diperbarui.');
    }
}
