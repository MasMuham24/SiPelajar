@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
{{-- Header --}}
<div class="mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.students.index') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition"
           title="Kembali">
            ←
        </a>

        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">
                Tambahkan data siswa baru ke dalam sistem.
            </p>
        </div>
    </div>
</div>

{{-- Card Form --}}
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    {{-- Card Header --}}
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-800">
            Informasi Siswa
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Isi data siswa dengan lengkap dan benar.
        </p>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf

        {{-- Nama & Kelas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Nama Siswa <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Masukkan nama siswa"
                    required
                >

                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Kelas <span class="text-red-500">*</span>
                </label>

                <select
                    name="classroom_id"
                    class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('classroom_id') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Pilih Kelas --</option>

                    @foreach ($classrooms as $classroom)
                        <option
                            value="{{ $classroom->id }}"
                            {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}
                        >
                            {{ $classroom->name }} {{ $classroom->grade }} -
                            {{ $classroom->major->name ?? '-' }}
                        </option>
                    @endforeach
                </select>

                @error('classroom_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- NIS & NISN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    NIS <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="nis"
                    value="{{ old('nis') }}"
                    class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nis') border-red-500 @enderror"
                    placeholder="Masukkan NIS"
                    required
                >

                @error('nis')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    NISN <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="nisn"
                    value="{{ old('nisn') }}"
                    class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nisn') border-red-500 @enderror"
                    placeholder="Masukkan NISN"
                    required
                >

                @error('nisn')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Jenis Kelamin --}}
        <div class="mb-5">

            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Jenis Kelamin <span class="text-red-500">*</span>
            </label>

            <div class="flex flex-wrap gap-5">

                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="radio"
                        name="gender"
                        value="Laki-laki"
                        {{ old('gender') == 'Laki-laki' ? 'checked' : '' }}
                        class="form-radio text-blue-600"
                        required
                    >

                    <span class="ml-2 text-gray-700">
                        Laki-laki
                    </span>
                </label>

                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="radio"
                        name="gender"
                        value="Perempuan"
                        {{ old('gender') == 'Perempuan' ? 'checked' : '' }}
                        class="form-radio text-pink-600"
                        required
                    >

                    <span class="ml-2 text-gray-700">
                        Perempuan
                    </span>
                </label>

            </div>

            @error('gender')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

        </div>

        {{-- Telepon & Foto --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    No. Telepon
                </label>

                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    maxlength="20"
                    inputmode="numeric"
                    class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                    placeholder="Contoh: 081234567890"
                >

                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Foto
                </label>

                <input
                    type="file"
                    name="photo"
                    accept="image/*"
                    class="w-full border border-gray-300 p-2 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('photo') border-red-500 @enderror"
                >

                @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Alamat --}}
        <div class="mb-6">

            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Alamat
            </label>

            <textarea
                name="address"
                rows="4"
                class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                placeholder="Masukkan alamat lengkap siswa"
            >{{ old('address') }}</textarea>

            @error('address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

        </div>

        {{-- Action --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-gray-100">

            <a
                href="{{ route('admin.students.index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition"
            >
                ← Kembali
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-sm transition"
            >
                Simpan Siswa
            </button>

        </div>

    </form>
</div>
```

</div>

@endsection
