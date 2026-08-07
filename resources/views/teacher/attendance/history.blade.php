@extends('layouts.app')

@section('title', 'Riwayat Absensi Guru')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Riwayat Absensi Guru</h1>
        <p class="text-sm text-gray-600">Seluruh catatan kehadiran Anda</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('guru.attendance.index') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-calendar-day mr-2"></i> Absensi Hari Ini
        </a>
        <a href="{{ route('guru.attendance.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-user-graduate mr-2"></i> Lihat Absensi Murid
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendances->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->teacher->user->name ?? $attendance->teacher->nip ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $badgeColor = match($attendance->status) {
                                'hadir' => 'bg-green-100 text-green-800',
                                'terlambat' => 'bg-yellow-100 text-yellow-800',
                                'izin' => 'bg-blue-100 text-blue-800',
                                'sakit' => 'bg-orange-100 text-orange-800',
                                'alpha' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">{{ ucfirst($attendance->status) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300"></i>
                        <p>Belum ada riwayat absensi guru.</p>
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
