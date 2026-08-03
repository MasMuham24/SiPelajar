@extends('layouts.app')

@section('title', 'Tugas Saya')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Tugas Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar tugas dari guru untuk kelas Anda.</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Judul</th>
                        <th class="p-4 font-semibold text-gray-600">Guru</th>
                        <th class="p-4 font-semibold text-gray-600">Deadline</th>
                        <th class="p-4 font-semibold text-gray-600">Status</th>
                        <th class="p-4 font-semibold text-gray-600">Lampiran</th>
                        <th class="p-4 font-semibold text-gray-600 w-32">Aksi</th>
                    </tr>
                </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4 text-gray-800">{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                        <td class="p-4 text-gray-800 font-medium">{{ $assignment->title }}</td>
                        <td class="p-4 text-gray-800">{{ $assignment->teacher->user->name ?? '-' }}</td>
                        <td class="p-4 text-gray-800">{{ $assignment->deadline->format('d M Y H:i') }}</td>
                        <td class="p-4">
                            @if ($assignment->is_active)
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Ditutup</span>
                            @endif
                        </td>
                        <td class="p-4 text-gray-800">
                            @if ($assignment->attachment)
                                <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-4">
                            <a href="{{ route('siswa.assignments.show', $assignment) }}" class="text-blue-500 hover:text-blue-700 transition" title="Lihat"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                            <p>Belum ada tugas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-100">
        @forelse ($assignments as $assignment)
            <div class="p-4 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ $assignment->title }}</p>
                    <p class="text-xs text-gray-500">Guru: {{ $assignment->teacher->user->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">Deadline: {{ $assignment->deadline->format('d M Y H:i') }}</p>
                    <div class="mt-1">
                        @if ($assignment->is_active)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Ditutup</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('siswa.assignments.show', $assignment) }}" class="text-blue-500 hover:text-blue-700 transition text-lg shrink-0" title="Lihat"><i class="fas fa-eye"></i></a>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                <p>Belum ada tugas.</p>
            </div>
        @endforelse
    </div>

    @if ($assignments->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection
