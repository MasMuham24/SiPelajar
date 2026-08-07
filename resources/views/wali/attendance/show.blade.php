@extends('layouts.app')

@section('title', 'Verifikasi Absensi')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Verifikasi Absensi</h1>
        <p class="text-sm text-gray-600">Ubah status absensi siswa</p>
    </div>
    <a href="{{ route('wali-kelas.attendance.index') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Absensi</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Nama Siswa</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->student->user->name ?? $attendance->student->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">NIS</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->student->nis ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Kelas</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->student->classroom->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Tanggal Absen</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Status Saat Ini</p>
            <p class="text-base font-medium text-gray-900">
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
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Catatan Siswa</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->note ?? '-' }}</p>
        </div>
        @if($attendance->verified_at)
        <div>
            <p class="text-sm text-gray-500">Terverifikasi Oleh</p>
            <p class="text-base font-medium text-gray-900">{{ $attendance->verifier->name ?? '-' }} pada {{ $attendance->verified_at->format('d M Y H:i') }}</p>
        </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Ubah Status</h2>

    <form action="{{ route('wali-kelas.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="hadir" {{ $attendance->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="terlambat" {{ $attendance->status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="izin" {{ $attendance->status == 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ $attendance->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="alpha" {{ $attendance->status == 'alpha' ? 'selected' : '' }}>Alfa</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="teacher_note" class="block text-sm font-medium text-gray-700 mb-1">Catatan Wali Kelas (Opsional)</label>
            <textarea name="teacher_note" id="teacher_note" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Tambahkan catatan untuk absensi ini...">{{ old('teacher_note', $attendance->teacher_note) }}</textarea>
            @error('teacher_note')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('wali-kelas.attendance.index') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection