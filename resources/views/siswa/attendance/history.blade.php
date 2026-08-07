@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Riwayat Absensi</h1>
        <p class="text-sm text-gray-600">Seluruh catatan kehadiran Anda</p>
    </div>
    <a href="{{ route('siswa.attendance.index') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        <i class="fas fa-calendar-day mr-2"></i> Absensi Hari Ini
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->classroom->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $statusLabel = match($attendance->status) {
                                'izin', 'Izin' => 'Izin',
                                'sakit', 'Sakit' => 'Sakit',
                                'alpha', 'alfa', 'Alfa' => 'Alfa',
                                default => $attendance->late_minutes > 0 ? 'Terlambat' : 'Tepat Waktu',
                            };
                            $badgeColor = match($statusLabel) {
                                'Tepat Waktu' => 'bg-green-100 text-green-800',
                                'Terlambat' => 'bg-yellow-100 text-yellow-800',
                                'Izin' => 'bg-blue-100 text-blue-800',
                                'Sakit' => 'bg-orange-100 text-orange-800',
                                'Alfa' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300"></i>
                        <p>Belum ada riwayat absensi.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($attendances->hasPages())
<div class="mt-4">
    {{ $attendances->links() }}
</div>
@endif
@endsection