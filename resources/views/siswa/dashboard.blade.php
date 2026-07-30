@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Dashboard Siswa</h1>
    <p class="text-gray-600 mb-8">
        Selamat datang di dashboard siswa. Anda bisa melihat tugas, absensi, dan nilai.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Statistik -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Tugas Saya</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $myAssignments }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Absensi Hari Ini</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $todayAttendance }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-gray-700 font-semibold mb-3">Nilai Terakhir</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $lastGrade }}</p>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-gray-700 font-semibold mb-3">Pencarian Tugas</h2>
        <div class="flex items-center border-t border-gray-200 mt-2">
            <input type="text" placeholder="Cari tugas..." class="flex-1 py-2 px-3 rounded-l-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-md hover:bg-blue-600">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
        </div>
    </div>
</div>
@endsection