@extends('layouts.app')

@section('title', 'Detail Guru')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Guru</h2>
        <p class="text-sm text-gray-500 mt-1">Informasi lengkap data guru.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden max-w-4xl mx-auto">
    <div class="md:flex">
        {{-- Bagian Foto (Kiri) --}}
        <div class="md:w-1/3 bg-gray-50 p-8 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
            @if ($teacher->photo)
                <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $teacher->user->name }}" class="w-48 h-48 rounded-full object-cover border-4 border-white shadow-lg mb-4">
            @else
                <div class="w-48 h-48 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-6xl shadow-lg mb-4 border-4 border-white">
                    {{ strtoupper(substr($teacher->user->name ?? '?', 0, 1)) }}
                </div>
            @endif
            <h3 class="text-xl font-bold text-gray-800 text-center">{{ $teacher->user->name ?? '-' }}</h3>
            <p class="text-gray-500 text-sm mt-1">NIP: {{ $teacher->nip }}</p>
            <span class="mt-3 px-3 py-1 rounded-full text-xs font-semibold {{ $teacher->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                {{ $teacher->gender }}
            </span>
        </div>

        {{-- Bagian Detail (Kanan) --}}
        <div class="md:w-2/3 p-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pribadi</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <p class="text-sm text-gray-500">NIP / NUPTK</p>
                    <p class="font-medium text-gray-800">{{ $teacher->nip }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Nama Lengkap</p>
                    <p class="font-medium text-gray-800">{{ $teacher->user->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Jenis Kelamin</p>
                    <p class="font-medium text-gray-800">{{ $teacher->gender }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">No. Telepon</p>
                    <p class="font-medium text-gray-800">{{ $teacher->phone ?? '-' }}</p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500">Alamat Lengkap</p>
                    <p class="font-medium text-gray-800">{{ $teacher->address ?? '-' }}</p>
                </div>
            </div>

            <h4 class="text-lg font-semibold text-gray-800 mt-8 mb-4 border-b pb-2">Informasi Akun</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <p class="text-sm text-gray-500">Username Login</p>
                    <p class="font-medium text-gray-800">{{ $teacher->user->username ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status Akun</p>
                    <p class="font-medium text-gray-800">
                        <span class="text-green-600 bg-green-100 px-2 py-1 rounded text-xs font-semibold">Aktif</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
