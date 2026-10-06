@extends('layouts.app')

@section('title', 'Pengaturan Jam Absensi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Pengaturan Jam Absensi</h2>
        <p class="text-sm text-gray-500 mt-1">Atur batas waktu jam masuk dan awal jam pulang absensi sekolah.</p>
    </div>

    <!-- Nilai Waktu Saat Ini -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fas fa-sign-in-alt text-xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-blue-600">Jam Masuk Saat Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $setting->getFormattedStartTime() }} WIB</p>
                <p class="text-xs text-gray-500">Absen setelah jam ini tercatat terlambat</p>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-green-600">Jam Pulang Saat Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $setting->getFormattedEndTime() }} WIB</p>
                <p class="text-xs text-gray-500">Bisa checkout setelah jam ini</p>
            </div>
        </div>
    </div>

    <!-- Form Pengaturan -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.attendance-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <!-- Input Jam Masuk -->
                <div>
                    <label for="school_start_time" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-clock text-blue-500 mr-1"></i> Jam Masuk (Batas Tepat Waktu)
                    </label>
                    <input type="time"
                           name="school_start_time"
                           id="school_start_time"
                           value="{{ old('school_start_time', $setting->getFormattedStartTime()) }}"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none @error('school_start_time') border-red-500 @else border-gray-300 @enderror">
                    @error('school_start_time')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Siswa yang absen setelah waktu ini akan diberi status terlambat.</p>
                </div>

                <!-- Input Jam Pulang -->
                <div>
                    <label for="school_end_time" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-clock text-green-500 mr-1"></i> Jam Pulang (Awal Waktu Checkout)
                    </label>
                    <input type="time"
                           name="school_end_time"
                           id="school_end_time"
                           value="{{ old('school_end_time', $setting->getFormattedEndTime()) }}"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none @error('school_end_time') border-red-500 @else border-gray-300 @enderror">
                    @error('school_end_time')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Checkout baru dapat dilakukan setelah jam ini.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
