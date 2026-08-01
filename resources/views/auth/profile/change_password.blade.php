@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Ubah Kata Sandi</h2>
            <p class="text-sm text-gray-500 mt-1">Pastikan akun Anda menggunakan kata sandi yang aman.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Profil
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('current_password') border-red-500 @enderror" required>
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kata Sandi Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror" required>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 @error('password_confirmation') border-red-500 @enderror" required>
                @error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('profile.show') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Perbarui Kata Sandi</button>
            </div>
        </form>
    </div>
</div>
@endsection
