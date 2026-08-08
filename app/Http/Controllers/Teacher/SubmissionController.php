<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    private function ensureAssignmentOwnership(Assignment $assignment): void
    {
        $teacher = Teacher::query()->where('user_id', Auth::id())->first();
        if (! $teacher || $assignment->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke submission tugas ini.');
        }
    }

    private function ensureSubmissionOwnership(Submission $submission): void
    {
        $this->ensureAssignmentOwnership($submission->assignment);
    }

    public function index(Assignment $assignment)
    {
        $this->ensureAssignmentOwnership($assignment);
        $submissions = Submission::with('student')->where('assignment_id', $assignment->id)->latest()->get();
        return view('teacher.submissions.index', compact('assignment', 'submissions'));
    }

    public function show(Submission $submission)
    {
        $this->ensureSubmissionOwnership($submission);
        $submission->load([
            'student',
            'assignment',
        ]);
        return view('teacher.submissions.show', compact('submission'));
    }

    public function update(GradeSubmissionRequest $request, Submission $submission)
    {
        $this->ensureSubmissionOwnership($submission);
        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Nilai berhasil disimpan');
    }
}
