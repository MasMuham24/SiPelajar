@extends('layouts.app')

@section('title', 'Tambah Guru')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Tambah Guru</h2>
            <p class="text-sm text-gray-500 mt-1">Isi form untuk menambahkan guru baru.</p>
        </div>
        <a href="{{ route('admin.teachers.index') }}"
            class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
        <form action="{{ route('admin.teachers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                        required>
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">NIP / NUPTK <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nip" value="{{ old('nip') }}"
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('nip') border-red-500 @enderror"
                        required>
                    @error('nip')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin <span
                            class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="gender" value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'checked' : '' }} class="form-radio text-blue-600" required>
                            <span class="ml-2">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="gender" value="Perempuan" {{ old('gender') == 'Perempuan' ? 'checked' : '' }} class="form-radio text-pink-600" required>
                            <span class="ml-2">Perempuan</span>
                        </label>
                    </div>
                    @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Foto Profil</label>
                    <input type="file" name="photo" accept="image/*"
                        class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('photo') border-red-500 @enderror">
                    @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap</label>
                <textarea name="address" rows="3"
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Wali Kelas (Opsional)</label>
                <select name="classroom_id"
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('classroom_id') border-red-500 @enderror">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
                @error('classroom_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.teachers.index') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</a>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Simpan Guru</button>
            </div>
        </form>
    </div>
@endsection
