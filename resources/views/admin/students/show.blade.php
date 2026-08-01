@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Informasi lengkap data siswa.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.students.edit', $student) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.students.index') }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            @if ($student->photo)
                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->user->name }}" class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-blue-100">
            @else
                <div class="w-32 h-32 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-4xl mx-auto mb-4 border-4 border-blue-100">
                    {{ strtoupper(substr($student->user->name ?? '?', 0, 1)) }}
                </div>
            @endif
            <h3 class="text-lg font-bold text-gray-800">{{ $student->user->name ?? '-' }}</h3>
            <p class="text-sm text-gray-500">{{ $student->user->email ?? '-' }}</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold {{ $student->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                {{ $student->gender }}
            </span>
        </div>
    </div>

    {{-- Detail Info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-user text-blue-600 mr-2"></i> Informasi Pribadi
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">NIS</label>
                    <p class="text-gray-800 font-medium">{{ $student->nis }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">NISN</label>
                    <p class="text-gray-800 font-medium">{{ $student->nisn }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">Jenis Kelamin</label>
                    <p class="text-gray-800 font-medium">{{ $student->gender }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">No. Telepon</label>
                    <p class="text-gray-800 font-medium">{{ $student->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-school text-blue-600 mr-2"></i> Informasi Akademik
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">Kelas</label>
                    <p class="text-gray-800 font-medium">{{ $student->classroom->name ?? '-' }} {{ $student->classroom->grade ?? '' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide">Jurusan</label>
                    <p class="text-gray-800 font-medium">{{ $student->classroom->major->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i> Alamat
            </h4>
            <p class="text-gray-800">{{ $student->address ?? 'Tidak ada alamat.' }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i> Pengumpulan Tugas
            </h4>
            @if ($student->submissions && $student->submissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="p-3 font-semibold text-gray-600 text-sm">No</th>
                                <th class="p-3 font-semibold text-gray-600 text-sm">Tugas</th>
                                <th class="p-3 font-semibold text-gray-600 text-sm">Nilai</th>
                                <th class="p-3 font-semibold text-gray-600 text-sm">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($student->submissions as $submission)
                                <tr class="border-b border-gray-100">
                                    <td class="p-3 text-sm text-gray-800">{{ $loop->iteration }}</td>
                                    <td class="p-3 text-sm text-gray-800">{{ $submission->assignment->title ?? '-' }}</td>
                                    <td class="p-3 text-sm">
                                        @if ($submission->score)
                                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $submission->score }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">{{ $submission->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-400 text-sm">Belum ada pengumpulan tugas.</p>
            @endif
        </div>
    </div>
</div>
@endsection