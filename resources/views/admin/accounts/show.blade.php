@extends('layouts.app')

@section('title', 'Detail Akun')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Detail Akun Pengguna</h1>
    <p class="text-sm text-gray-600">Informasi lengkap akun pengguna</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                <p class="mt-1 text-sm text-gray-900">{{ $account->name }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500">Username</label>
                <p class="mt-1 text-sm text-gray-900">{{ $account->username }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ $account->email ?? '-' }}</p>
            </div>
        </div>
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Role</label>
                <p class="mt-1">
                    @php
                        $badgeColor = match($account->role) {
                            'admin' => 'bg-red-100 text-red-800',
                            'guru' => 'bg-blue-100 text-blue-800',
                            'siswa' => 'bg-green-100 text-green-800',
                             default => 'bg-gray-100 text-gray-800',
                     };
                     @endphp
                     <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">{{ ucfirst($account->role) }}</span>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500">Tanggal Dibuat</label>
                <p class="mt-1 text-sm text-gray-900">{{ $account->created_at ? $account->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500">Terakhir Diperbarui</label>
                <p class="mt-1 text-sm text-gray-900">{{ $account->updated_at ? $account->updated_at->format('d M Y H:i') : '-' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-end space-x-3 pt-6 border-t border-gray-200">
        <a href="{{ route('admin.accounts.index') }}" 
           class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Kembali
        </a>
        <a href="{{ route('admin.accounts.edit', ['account' => $account]) }}" 
           class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
            Edit Akun
        </a>
    </div>
</div>
@endsection