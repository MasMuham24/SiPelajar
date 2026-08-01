@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Edit Profil</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi profil akun Anda.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('profile.password.edit') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                    <i class="fas fa-key mr-1"></i> Ubah Kata Sandi
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
