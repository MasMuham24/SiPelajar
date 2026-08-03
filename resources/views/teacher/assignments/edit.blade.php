@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Edit Tugas</h2>
        <p class="text-sm text-gray-500 mt-1">Perbarui data tugas.</p>
    </div>
    <a href="{{ route('guru.assignments.index') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
    <form action="{{ route('guru.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $assignment->title) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('title') border-red-500 @enderror" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Kelas <span class="text-red-500">*</span></label>
                <select name="classroom_id" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('classroom_id') border-red-500 @enderror" required>
                    <option value="">Pilih Kelas</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ old('classroom_id', $assignment->classroom_id) == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                    @endforeach
                </select>
                @error('classroom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Deadline <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="deadline" value="{{ old('deadline', $assignment->deadline->format('Y-m-d\\TH:i')) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('deadline') border-red-500 @enderror" required>
                @error('deadline') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Lampiran</label>
                @if ($assignment->attachment)
                    <p class="text-xs text-gray-500 mb-2">Kosongkan jika tidak ingin mengubah lampiran. <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Lihat file</a></p>
                @endif
                <input type="file" name="attachment" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('attachment') border-red-500 @enderror">
                @error('attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi <span class="text-red-500">*</span></label>
            <textarea name="description" rows="5" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('description') border-red-500 @enderror" required>{{ old('description', $assignment->description) }}</textarea>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('guru.assignments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Perbarui Tugas</button>
        </div>
    </form>
</div>
@endsection
