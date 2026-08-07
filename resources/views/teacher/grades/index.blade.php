@extends('layouts.app')

@section('title', 'Rekap Nilai')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Rekap Nilai Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Rata-rata nilai per siswa dari semua tugas yang sudah dinilai</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="{{ route('guru.grades.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-2">Cari Kelas</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Contoh: 10 TJKT 1" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Urutkan per Kelas</label>
            <select name="classroom_id" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="">Semua Kelas</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((string) $classroomId === (string) $classroom->id)>{{ $classroom->grade }} {{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('guru.grades.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md transition">Reset</a>
        </div>
    </form>
</div>

@if (empty($recaps))
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <i class="fas fa-chart-bar text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">Belum ada data nilai</p>
        <p class="text-gray-400 text-sm mt-1">Nilai akan muncul setelah Anda menilai submission siswa</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Siswa</th>
                        <th class="p-4 font-semibold text-gray-600">NIS</th>
                        <th class="p-4 font-semibold text-gray-600">Kelas</th>
                        <th class="p-4 font-semibold text-gray-600">Jumlah Dinilai</th>
                        <th class="p-4 font-semibold text-gray-600">Rata-rata</th>
                        <th class="p-4 font-semibold text-gray-600">Nilai Tertinggi</th>
                        <th class="p-4 font-semibold text-gray-600">Nilai Terendah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recaps as $index => $recap)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 text-gray-800">{{ $loop->iteration }}</td>
                            <td class="p-4 text-gray-800 font-medium">{{ $recap['student']->name ?? '-' }}</td>
                            <td class="p-4 text-gray-800">{{ $recap['student']->nis ?? '-' }}</td>
                            <td class="p-4 text-gray-800">{{ $recap['student']->classroom->name ?? '-' }}</td>
                            <td class="p-4 text-gray-800">{{ count($recap['scores']) }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded text-sm font-semibold bg-blue-100 text-blue-700">
                                    {{ number_format($recap['average'], 2) }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-800">{{ count($recap['scores']) > 0 ? max($recap['scores']) : '-' }}</td>
                            <td class="p-4 text-gray-800">{{ count($recap['scores']) > 0 ? min($recap['scores']) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection