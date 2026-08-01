@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Edit Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Perbarui data siswa.</p>
    </div>
    <a href="{{ route('admin.students.index') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl">
    <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Siswa <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $student->name ?? $student->user->name) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Kelas <span class="text-red-500">*</span></label>
                <select name="classroom_id" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('classroom_id') border-red-500 @enderror" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ old('classroom_id', $student->classroom_id) == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->name }} {{ $classroom->grade }} - {{ $classroom->major->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('classroom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">NIS <span class="text-red-500">*</span></label>
                <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('nis') border-red-500 @enderror" required>
                @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">NISN <span class="text-red-500">*</span></label>
                <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('nisn') border-red-500 @enderror" required>
                @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="Laki-laki" {{ old('gender', $student->gender) == 'Laki-laki' ? 'checked' : '' }} class="form-radio text-blue-600" required>
                    <span class="ml-2">Laki-laki</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="gender" value="Perempuan" {{ old('gender', $student->gender) == 'Perempuan' ? 'checked' : '' }} class="form-radio text-pink-600" required>
                    <span class="ml-2">Perempuan</span>
                </label>
            </div>
            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('phone') border-red-500 @enderror">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto</label>
                @if ($student->photo)
                    <div class="flex items-center gap-3 mb-2">
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="Foto" class="w-12 h-12 rounded-full object-cover">
                        <span class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah foto.</span>
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('photo') border-red-500 @enderror">
                @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Alamat</label>
            <textarea name="address" rows="3" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $student->address) }}</textarea>
            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.students.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Perbarui</button>
        </div>
    </form>
</div>
@endsection