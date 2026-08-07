@extends('layouts.app')

@section('title', 'Submission Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Submission Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $assignment->title }} · Kelas {{ $assignment->classroom->name ?? '-' }}</p>
    </div>
    <a href="{{ route('guru.assignments.show', $assignment) }}" class="text-gray-600 hover:text-gray-800 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                    <th class="p-4 font-semibold text-gray-600">Siswa</th>
                    <th class="p-4 font-semibold text-gray-600">NIS</th>
                    <th class="p-4 font-semibold text-gray-600">Waktu Kirim</th>
                    <th class="p-4 font-semibold text-gray-600">Status</th>
                    <th class="p-4 font-semibold text-gray-600 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $index => $submission)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4 text-gray-800">{{ $loop->iteration }}</td>
                        <td class="p-4 text-gray-800 font-medium">{{ $submission->student->name ?? $submission->student->user->name ?? '-' }}</td>
                        <td class="p-4 text-gray-800">{{ $submission->student->nis ?? '-' }}</td>
                        <td class="p-4 text-gray-800">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y H:i') : '-' }}</td>
                        <td class="p-4">
                            @if ($submission->score !== null)
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Dinilai: {{ $submission->score }}</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Belum Dinilai</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <a href="{{ route('guru.submissions.show', $submission) }}" class="text-blue-500 hover:text-blue-700 transition" title="Detail & Nilai">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                            <p>Belum ada siswa yang mengumpulkan tugas ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-100">
        @forelse ($submissions as $submission)
            <div class="p-4 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ $submission->student->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $submission->student->nis ?? '' }}</p>
                    <p class="text-xs text-gray-500">Kirim: {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y H:i') : '-' }}</p>
                    <div class="mt-1">
                        @if ($submission->score !== null)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">Dinilai: {{ $submission->score }}</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Belum Dinilai</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('guru.submissions.show', $submission) }}" class="text-blue-500 hover:text-blue-700 transition text-lg" title="Detail & Nilai">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                <p>Belum ada siswa yang mengumpulkan tugas ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
