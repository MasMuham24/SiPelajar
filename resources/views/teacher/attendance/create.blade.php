@extends('layouts.app')

@section('title', 'Isi Absensi')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Isi Absensi Siswa</h1>
        <p class="text-sm text-gray-600">Tentukan status kehadiran siswa pada tanggal yang dipilih ({{ $students->count() }} siswa)</p>
    </div>
    <a href="{{ route('guru.attendance.index') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

@php
    $statuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alfa'];
@endphp

<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Kelas</label>
            <form method="GET" action="{{ route('guru.attendance.create') }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <select name="kelas" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="" {{ !$selectedClassroom ? 'selected' : '' }}>Semua Kelas</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ (string) $selectedClassroom === (string) $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
            <input type="date" name="date" form="form-absensi" value="{{ old('date', $selectedDate) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror">
            @error('date')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Set Semua Status</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($statuses as $status)
                <button type="button" onclick="markAllStatus('{{ $status }}')"
                        class="px-3 py-1.5 text-xs rounded-full border border-gray-300 hover:bg-blue-50 transition">
                    {{ $status }}
                </button>
            @endforeach
        </div>
    </div>

    <form id="form-absensi" method="POST" action="{{ route('guru.attendance.store') }}">
        @csrf

        <div class="border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($students as $student)
                        <tr>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">{{ $student->nis }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">{{ $student->name }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $student->classroom_name }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex justify-center gap-2">
                                    @foreach ($statuses as $status)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $status }}"
                                                   {{ $status === 'Hadir' ? 'checked' : '' }}
                                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-1 text-xs text-gray-700">{{ $status }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada siswa ditemukan untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('guru.attendance.index') }}"
               class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Simpan Absensi
            </button>
        </div>
    </form>
</div>

<script>
    function markAllStatus(status) {
        document.querySelectorAll('#form-absensi input[type="radio"]').forEach(radio => {
            if (radio.value === status) {
                radio.checked = true;
            }
        });
    }
</script>
@endsection
