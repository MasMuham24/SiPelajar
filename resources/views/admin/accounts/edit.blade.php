@extends('layouts.app')

@section('title', 'Edit Akun')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Akun Pengguna</h1>
    <p class="text-sm text-gray-600">Perbarui informasi akun berikut</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.accounts.update', ['account' => $account]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                <input type="text" name="username" value="{{ old('username', $account->username) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $account->email) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" name="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti password</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="flex items-end">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select name="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role', $account->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ old('role', $account->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa" {{ old('role', $account->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                    @error('role')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.accounts.index') }}" 
               class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                Perbarui
            </button>
        </div>
    </form>
</div>
@endsection