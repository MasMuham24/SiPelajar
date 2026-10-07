<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi - {{ $classroom->name }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #1a1a2e;
        }
        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin: 0;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info div {
            display: flex;
            flex-direction: column;
        }
        .info label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info span {
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        td.name-col {
            text-align: left;
        }
        td.nis-col {
            text-align: center;
        }
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .empty-row td {
            text-align: center;
            color: #9ca3af;
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SiPelajar</h1>
        <p class="subtitle">Rekap Kehadiran Siswa</p>
    </div>

    @if($classroom)
        <div class="info">
            <div>
                <label>Kelas</label>
                <span>{{ $classroom->name }}</span>
            </div>
            <div>
                <label>Periode</label>
                <span>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</span>
            </div>
            <div>
                <label>Wali Kelas</label>
                <span>{{ $classroom->homeroomTeacher->user->name ?? 'Belum ditentukan' }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th style="width: 80px;">NIS</th>
                    <th style="width: 180px;" class="name-col">Nama Siswa</th>
                    <th style="width: 55px;">Hadir</th>
                    <th style="width: 65px;">Terlambat</th>
                    <th style="width: 50px;">Izin</th>
                    <th style="width: 50px;">Sakit</th>
                    <th style="width: 50px;">Alpha</th>
                    <th style="width: 50px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recaps as $index => $recap)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="nis-col">{{ $recap['student']->nis }}</td>
                        <td class="name-col">{{ $recap['student']->user->name ?? $recap['student']->name ?? '-' }}</td>
                        <td>{{ $recap['hadir'] }}</td>
                        <td>{{ $recap['terlambat'] }}</td>
                        <td>{{ $recap['izin'] }}</td>
                        <td>{{ $recap['sakit'] }}</td>
                        <td>{{ $recap['alpha'] }}</td>
                        <td><strong>{{ $recap['total'] }}</strong></td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="9">Tidak ada data kehadiran pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Dicetak pada {{ now()->format('d F Y H:i') }}
        </div>
    @else
        <div class="empty-row">
            <p>Kelas wali tidak ditemukan.</p>
        </div>
    @endif
</body>
</html>