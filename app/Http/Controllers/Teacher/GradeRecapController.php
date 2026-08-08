<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GradeRecapController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        $search = $request->input('search');
        $classroomId = $request->input('classroom_id');
        $classrooms = Classroom::orderBy('grade')->orderBy('name')->get();
        $recaps = $this->buildRecaps($teacher->id, $search, $classroomId);

        return view(
            'teacher.grades.index',
            compact(
                'recaps',
                'classrooms',
                'search',
                'classroomId'
            )
        );
    }

    public function exportPdf(Request $request)
    {
        $teacher = Auth::user()->teacher;
        $search = $request->input('search');
        $classroomId = $request->input('classroom_id');
        $recaps = $this->buildRecaps($teacher->id, $search, $classroomId);

        $classroom = null;

        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
        }

        $pdf = Pdf::loadView('teacher.grades.pdf', [
            'recaps' => $recaps,
            'teacher' => $teacher,
            'classroom' => $classroom,
            'search' => $search,
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download(
            'rekap-nilai-'.now()->format('Y-m-d').'.pdf'
        );
    }

    public function exportExcel(Request $request)
    {
        $teacher = Auth::user()->teacher;
        $search = $request->input('search');
        $classroomId = $request->input('classroom_id');
        $recaps = $this->buildRecaps($teacher->id, $search, $classroomId);

        $classroom = null;

        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'REKAP NILAI SISWA');
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A2', 'Guru: ' . ($teacher->user->name ?? '-') . ' (' . ($teacher->nip ?? '-') . ')');
        $sheet->mergeCells('A2:H2');

        if ($classroom) {
            $sheet->setCellValue('A3', 'Kelas: ' . $classroom->grade . ' ' . $classroom->name);
        } else {
            $sheet->setCellValue('A3', 'Kelas: Semua Kelas');
        }
        $sheet->mergeCells('A3:H3');

        $sheet->setCellValue('A4', 'Tanggal Export: ' . now()->format('d-m-Y H:i'));
        $sheet->mergeCells('A4:H4');

        $headers = ['No', 'Siswa', 'NIS', 'Kelas', 'Jumlah Dinilai', 'Rata-rata', 'Nilai Tertinggi', 'Nilai Terendah'];
        $headerRow = 6;

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $row = $headerRow + 1;
        foreach ($recaps as $recap) {
            $student = $recap['student'];
            $scores = $recap['scores'];
            $count = count($scores);

            $sheet->setCellValue('A' . $row, $row - $headerRow);
            $sheet->setCellValue('B' . $row, $student->name ?? '-');
            $sheet->setCellValue('C' . $row, $student->nis ?? '-');
            $sheet->setCellValue('D' . $row, ($student->classroom->grade ?? '') . ' ' . ($student->classroom->name ?? '-'));
            $sheet->setCellValue('E' . $row, $count);
            $sheet->setCellValue('F' . $row, number_format($recap['average'], 2));
            $sheet->setCellValue('G' . $row, $count > 0 ? max($scores) : '-');
            $sheet->setCellValue('H' . $row, $count > 0 ? min($scores) : '-');
            $row++;
        }

        $lastRow = $row - 1;

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A6:H6')->applyFromArray($headerStyle);

        $sheet->getStyle('A1:H4')->getFont()->setBold(true);

        $sheet->getStyle('A' . $headerRow . ':H' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $headerRow . ':H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'rekap-nilai-' . now()->format('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildRecaps($teacherId, $search, $classroomId)
    {
        $assignments = Assignment::with([
            'classroom',
            'submissions.student.classroom',
        ])
            ->where('teacher_id', $teacherId)
            ->when($classroomId, function ($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
            })
            ->when($search, function ($query) use ($search) {
                $tokens = preg_split('/\s+/', trim($search));
                $query->whereHas('classroom', function ($query) use ($tokens) {
                    foreach ($tokens as $token) {
                        $query->where(function ($query) use ($token) {
                            $query->where('grade', 'like', '%'.$token.'%')
                                ->orWhere('name', 'like', '%'.$token.'%');
                        });
                    }
                });
            })
            ->get();

        $recaps = [];

        foreach ($assignments as $assignment) {
            foreach ($assignment->submissions as $submission) {
                $studentId = $submission->student_id;
                if (! isset($recaps[$studentId])) {
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
            $recap['average'] = count($scores) > 0
                ? array_sum($scores) / count($scores)
                : 0;
        }

        return $recaps;
    }
}
