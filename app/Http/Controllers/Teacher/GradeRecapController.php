<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeRecapController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        $search = $request->input('search');
        $classroomId = $request->input('classroom_id');

        $classrooms = Classroom::orderBy('grade')->orderBy('name')->get();

        $assignments = Assignment::with([
            'classroom',
            'submissions.student.classroom'
        ])
            ->where('teacher_id', $teacher->id)
            ->when($classroomId, function ($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
            })
            ->when($search, function ($query) use ($search) {
                $tokens = preg_split('/\s+/', trim($search));

                $query->whereHas('classroom', function ($query) use ($tokens) {
                    foreach ($tokens as $token) {
                        $query->where(function ($query) use ($token) {
                            $query->where('grade', 'like', '%' . $token . '%')
                                ->orWhere('name', 'like', '%' . $token . '%');
                        });
                    }
                });
            })
            ->get();

        $recaps = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->submissions as $submission) {
                $studentId = $submission->student_id;
                if (!isset($recaps[$studentId])) {
                    $recaps[$studentId] = [
                        'student' => $submission->student,
                        'scores' => [],
                    ];
                }

                if ($submission->score !== null) {
                    $recaps[$studentId]['scores'][] = $submission->score;
                }
            }
        }

        foreach ($recaps as &$recap) {
            $scores = $recap['scores'];
            $recap['average'] = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
        }

        return view('teacher.grades.index', compact('recaps', 'classrooms', 'search', 'classroomId'));
    }
}
