@extends('layouts.app')

@section('title', 'Detail Submission')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Submission</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $submission->assignment->title }}</p>
    </div>
    <a href="{{ route('guru.assignments.submissions.index', $submission->assignment) }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-sm font-bold text-gray-700 mb-4">Informasi Siswa</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama</p>
                    <p class="text-gray-800 font-medium">{{ $submission->student->name ?? $submission->student->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">NIS</p>
                    <p class="text-gray-800 font-medium">{{ $submission->student->nis ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Kelas</p>
                    <p class="text-gray-800 font-medium">{{ $submission->student->classroom->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Waktu Kirim</p>
                    <p class="text-gray-800 font-medium">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-sm font-bold text-gray-700 mb-4">Jawaban Siswa</h4>
            <div class="space-y-4">
                @if ($submission->file)
                    <a href="{{ asset('storage/' . $submission->file) }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition">
                        <i class="fas fa-download"></i> Lihat File Jawaban
                    </a>
                @endif
                @if ($submission->link)
                    <a href="{{ $submission->link }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition">
                        <i class="fas fa-external-link-alt"></i> Buka Link Jawaban
                    </a>
                @endif
                @if (!$submission->file && !$submission->link)
                    <p class="text-gray-500">Siswa mengirim jawaban kosong.</p>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Penilaian</h4>

            @if ($submission->score !== null)
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                    <p class="text-sm font-bold text-blue-700">Nilai saat ini: {{ $submission->score }}</p>
                    <p class="text-xs text-blue-600 mt-1">Dinilai {{ $submission->graded_at ? $submission->graded_at->format('d M Y H:i') : '-' }}</p>
                </div>
            @endif

            <form action="{{ route('guru.submissions.update', $submission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nilai (0-100)</label>
                    <input type="number" name="score" min="0" max="100"
                           value="{{ old('score', $submission->score) }}" required
                           class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('score') border-red-500 @enderror">
                    @error('score') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Feedback</label>
                    <textarea name="feedback" rows="4" required
                              class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('feedback') border-red-500 @enderror">{{ old('feedback', $submission->feedback) }}</textarea>
                    @error('feedback') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                    <i class="fas fa-save mr-2"></i> Simpan Nilai
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
