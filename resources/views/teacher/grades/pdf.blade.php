<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 2px 0;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.data th,
        table.data td {
            border: 1px solid #999;
            padding: 5px 6px;
            text-align: left;
        }
        table.data th {
            background: #e5e7eb;
            font-weight: bold;
        }
        table.data td.no,
        table.data td.center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Nilai Siswa</h1>
        <p>Guru: {{ $teacher->user->name ?? '-' }} ({{ $teacher->nip ?? '-' }})</p>
        <p>Tanggal Export: {{ now()->format('d-m-Y H:i') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="120"><strong>Kelas</strong></td>
                <td>: {{ $classroom ? $classroom->grade . ' ' . $classroom->name : 'Semua Kelas' }}</td>
            </tr>
            @if ($search)
                <tr>
                    <td><strong>Pencarian</strong></td>
                    <td>: {{ $search }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if (empty($recaps))
        <p style="text-align:center; padding: 20px;">Belum ada data nilai.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th class="no" width="30">No</th>
                    <th>Siswa</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th class="center">Jumlah Dinilai</th>
                    <th class="center">Rata-rata</th>
                    <th class="center">Nilai Tertinggi</th>
                    <th class="center">Nilai Terendah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recaps as $recap)
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ $recap['student']->name ?? '-' }}</td>
                        <td>{{ $recap['student']->nis ?? '-' }}</td>
                        <td>{{ $recap['student']->classroom->grade ?? '' }} {{ $recap['student']->classroom->name ?? '-' }}</td>
                        <td class="center">{{ count($recap['scores']) }}</td>
                        <td class="center">{{ number_format($recap['average'], 2) }}</td>
                        <td class="center">{{ count($recap['scores']) > 0 ? max($recap['scores']) : '-' }}</td>
                        <td class="center">{{ count($recap['scores']) > 0 ? min($recap['scores']) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>
    </div>
</body>
</html>
