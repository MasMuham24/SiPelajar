@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Tugas</h2>
        <p class="text-sm text-gray-500 mt-1">Informasi lengkap tugas.</p>
    </div>
    <a href="{{ route('siswa.assignments.index') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $assignment->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Guru: {{ $assignment->teacher->user->name ?? '-' }}</p>
                </div>
                @if (!$assignment->is_active)
                    <span class="px-3 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Tugas Ditutup</span>
                @else
                    <span class="px-3 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                @endif
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

            <div>
                <h4 class="text-sm font-bold text-gray-700 mb-2">Deadline</h4>
                <p class="text-gray-800">{{ $assignment->deadline->format('d M Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Jawaban Anda</h4>

            @if ($submission)
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                    <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-2"></i>Jawaban sudah dikirim</p>
                    <p class="text-xs text-green-600 mt-1">{{ $submission->submitted_at->format('d M Y H:i') }}</p>
                </div>

                @if ($submission->score !== null)
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <p class="text-sm font-bold text-blue-700">Nilai: {{ $submission->score }}</p>
                    </div>
                @endif

                @if ($submission->feedback)
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-4">
                        <p class="text-xs font-bold text-gray-700 mb-2">Feedback Guru:</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $submission->feedback }}</p>
                    </div>
                @endif

                <div class="mb-4 space-y-2">
                    @if ($submission->file)
                        <a href="{{ asset('storage/' . $submission->file) }}" target="_blank" class="block text-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition">
                            <i class="fas fa-download mr-2"></i>Lihat File Jawaban
                        </a>
                    @endif
                    @if ($submission->link)
                        <a href="{{ $submission->link }}" target="_blank" class="block text-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition">
                            <i class="fas fa-external-link-alt mr-2"></i>Buka Link Jawaban
                        </a>
                    @endif
                </div>

                @if ($assignment->is_active)
                    <button onclick="document.getElementById('editForm').style.display = 'block'" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition">
                        <i class="fas fa-edit mr-2"></i>Edit Jawaban
                    </button>

                    <div id="editForm" style="display: none;" class="mt-4 p-4 border-2 border-yellow-300 rounded-md bg-yellow-50">
                        <form action="{{ route('siswa.assignments.updateSubmission', $assignment) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">File Baru (Opsional)</label>
                                <input type="file" name="file" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('file') border-red-500 @enderror">
                                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Link Baru (Opsional)</label>
                                <input type="url" name="link" value="{{ old('link', $submission->link) }}" placeholder="https://..." class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('link') border-red-500 @enderror">
                                @error('link') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded text-sm transition">Simpan</button>
                                <button type="button" onclick="document.getElementById('editForm').style.display = 'none'" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-2 rounded text-sm transition">Batal</button>
                            </div>
                        </form>
                    </div>
                @endif
            @else
                @if ($assignment->is_active)
                    <form action="{{ route('siswa.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Unggah File (Opsional)</label>
                            <input type="file" name="file" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('file') border-red-500 @enderror">
                            @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">atau Link Jawaban (Opsional)</label>
                            <input type="url" name="link" placeholder="https://drive.google.com/... atau https://repl.it/..." class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('link') border-red-500 @enderror">
                            @error('link') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-xs text-gray-500 mb-4">Pilih salah satu: Unggah file atau isi link jawaban</p>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Jawaban
                        </button>
                    </form>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <p class="text-sm text-red-700"><i class="fas fa-lock mr-2"></i>Tugas telah ditutup. Anda tidak dapat lagi mengirim jawaban.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
