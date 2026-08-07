@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Tugas</h2>
        <p class="text-sm text-gray-500 mt-1">Informasi lengkap tugas.</p>
    </div>
    <a href="{{ route('guru.assignments.index') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800">{{ $assignment->title }}</h3>
        <p class="text-sm text-gray-500 mt-1">Kelas {{ $assignment->classroom->name ?? '-' }} · Deadline {{ $assignment->deadline->format('d M Y H:i') }}</p>
        <div class="mt-2">
            @if ($assignment->is_active)
                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
            @else
                <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Diakhiri</span>
            @endif
        </div>
    </div>

    <div class="mb-6">
        <h4 class="text-sm font-bold text-gray-700 mb-2">Deskripsi</h4>
        <div class="text-gray-800 whitespace-pre-line border border-gray-200 rounded-md p-4 bg-gray-50">{{ $assignment->description }}</div>
    </div>

    <div class="mb-6">
        <h4 class="text-sm font-bold text-gray-700 mb-2">Lampiran</h4>
        @if ($assignment->attachment)
            <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                <i class="fas fa-download"></i> Unduh Lampiran
            </a>
        @else
            <p class="text-gray-500">Tidak ada lampiran.</p>
        @endif
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('guru.assignments.submissions.index', $assignment) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
            <i class="fas fa-clipboard-list mr-2"></i> Submission Siswa
        </a>
        <a href="{{ route('guru.assignments.edit', $assignment) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition">Edit Tugas</a>
        <a href="{{ route('guru.assignments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Kembali</a>
    </div>
</div>
@endsection
