@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Dashboard Guru</h1>
    <p class="text-gray-600 mb-8">
        Selamat datang di dashboard guru. Anda bisa melihat tugas, absensi, dan penilaian siswa.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Statistik -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Tugas Aktif</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $activeAssignments }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Absensi Hari Ini</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $todayAttendance }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Nilai Terendah</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $lowestScore }}</p>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-gray-700 font-semibold mb-3">Pencarian Siswa</h2>
        <div class="flex items-center border-t border-gray-200 mt-2">
            <input type="text" placeholder="Cari siswa..." class="flex-1 py-2 px-3 rounded-l-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-md hover:bg-blue-600">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
        </div>
    </div>
</div>
@endsection