@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Dashboard Admin</h1>
    <p class="text-gray-600 mb-8">
        Selamat datang di dashboard admin. Anda bisa melihat ringkasan statistik, manajemen user, dan laporan.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Statistik -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Total Pengguna</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Tahun Ini</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $currentYearCount }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Kelas Terlengkap</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $completeClasses }}</p>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-gray-700 font-semibold mb-3">Pencarian Pengguna</h2>
        <div class="flex items-center border-t border-gray-200 mt-2">
            <input type="text" placeholder="Cari pengguna..." class="flex-1 py-2 px-3 rounded-l-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-md hover:bg-blue-600">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
        </div>
    </div>
</div>
@endsection