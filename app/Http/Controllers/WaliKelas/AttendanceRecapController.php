<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AttendanceRecapController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getRecapData($request);
        return view('wali.attendance.recap', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->getRecapData($request);
        if (! $data['classroom']) {
            abort(404, 'Kelas wali tidak ditemukan.');
        }
        $pdf = Pdf::loadView('wali.attendance.recap-pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download(
            'rekap-absensi-'.str_replace(' ', '-', strtolower($data['classroom']->name)).'-'.$data['month'].'-'.$data['year'].'.pdf'
        );
    }

    public function excel(Request $request)
    {
        $data = $this->getRecapData($request);
        if (! $data['classroom']) {
            abort(404, 'Kelas wali tidak ditemukan.');
        }
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi');
        $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA');
        $sheet->setCellValue('A2', 'Kelas');
        $sheet->setCellValue('B2', $data['classroom']->name);
        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue(
            'B3',
            sprintf('%02d/%d', $data['month'], $data['year'])
        );
        $headers = [
            'No',
            'Nama Siswa',
            'Hadir',
            'Terlambat',
            'Izin',
            'Sakit',
            'Alpha',
            'Total',
        ];
        $headerRow = 5;
        foreach ($headers as $column => $header) {
            $sheet->setCellValue(
                chr(65 + $column).$headerRow,
                $header
            );
        }
        $row = 6;
        foreach ($data['recaps'] as $index => $recap) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValue('B'.$row, $recap['student']->name);
            $sheet->setCellValue('C'.$row, $recap['hadir']);
            $sheet->setCellValue('D'.$row, $recap['terlambat']);
            $sheet->setCellValue('E'.$row, $recap['izin']);
            $sheet->setCellValue('F'.$row, $recap['sakit']);
            $sheet->setCellValue('G'.$row, $recap['alpha']);
            $sheet->setCellValue('H'.$row, $recap['total']);
            $row++;
        }
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A5:H5')->getAlignment()->setHorizontal('center');
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $filename = 'rekap-absensi-'.str_replace(' ', '-', strtolower($data['classroom']->name)).'-'.$data['month'].'-'.$data['year'].'.xlsx';
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    private function getRecapData(Request $request): array
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        if (! $teacher || ! $teacher->classroom) {
            return ['classroom' => null, 'recaps' => collect(), 'month' => $month, 'year' => $year];
        }
        $classroom = $teacher->classroom;
        $students = $classroom->students()->orderBy('name')->get();
        $recaps = $students->map(function ($student) use ($month, $year) {
            $attendances = Attendance::query()->where('student_id', $student->id)->where('classroom_id', $student->classroom_id)->whereMonth('date', $month)->whereYear('date', $year)->get();
            return [
                'student' => $student,
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
                'total' => $attendances->count(),
            ];
        });
        return compact('classroom','recaps','month','year');
    }
}
