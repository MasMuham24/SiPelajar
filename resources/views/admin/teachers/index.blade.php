@extends('layouts.app')

@section('title', 'Kelola Guru')

@section('content')
    <div x-data="teacherCrud()">

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Guru</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola data guru yang terdaftar di sistem.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.teachers.template') }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Template CSV
                </a>
                <button @click="importModalOpen = true"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                    <i class="fas fa-file-import"></i> Import Guru
                </button>
                <a href="{{ route('admin.teachers.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Guru
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">

            <div class="p-4 border-b border-gray-200">
                <form method="GET" action="{{ route('admin.teachers.index') }}" class="flex gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari NIP, atau nama..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white w-10 h-10 flex items-center justify-center rounded-md transition shrink-0">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                            <th class="p-4 font-semibold text-gray-600">Foto</th>
                            <th class="p-4 font-semibold text-gray-600">Nama</th>
                            <th class="p-4 font-semibold text-gray-600">NIP</th>
                            <th class="p-4 font-semibold text-gray-600">No. Telepon</th>
                            <th class="p-4 font-semibold text-gray-600">JK</th>
                            <th class="p-4 font-semibold text-gray-600">Wali Kelas</th>
                            <th class="p-4 font-semibold text-gray-600 w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $teacher)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-4 text-gray-800">
                                    {{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
                                <td class="p-4">
                                    @if ($teacher->photo)
                                        <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $teacher->user->name }}"
                                            class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                            {{ strtoupper(substr($teacher->user->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4 text-gray-800 font-medium">{{ $teacher->user->name ?? '-' }}</td>
                                <td class="p-4 text-gray-800">{{ $teacher->nip }}</td>
                                <td class="p-4 text-gray-800">{{ $teacher->phone ?? '-' }}</td>
                                <td class="p-4 text-gray-800">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-semibold {{ $teacher->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                                        {{ $teacher->gender === 'Laki-laki' ? 'L' : 'P' }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-800">
                                    @if($teacher->classroom)
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">
                                            {{ $teacher->classroom->name }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-500">
                                            Guru Mapel
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('admin.teachers.show', $teacher) }}"
                                        class="text-blue-500 hover:text-blue-700 mr-3 transition" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}"
                                        class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        @click="confirmDelete({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? '') }}')"
                                        class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                    <p>Belum ada data guru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse ($teachers as $teacher)
                    <div class="p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if ($teacher->photo)
                                <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $teacher->user->name }}"
                                    class="w-10 h-10 rounded-full object-cover shrink-0">
                            @else
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold shrink-0">
                                    {{ strtoupper(substr($teacher->user->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ $teacher->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">NIP: {{ $teacher->nip }}</p>
                                @if($teacher->classroom)
                                    <p class="text-xs text-green-600">
                                        Wali: {{ $teacher->classroom->name }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400">
                                        Guru Mapel
                                    </p>
                                @endif
                                <div class="flex gap-1 mt-1">
                                    <span
                                        class="px-2 py-0.5 rounded text-xs font-semibold {{ $teacher->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                                        {{ $teacher->gender === 'Laki-laki' ? 'L' : 'P' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('admin.teachers.show', $teacher) }}"
                                class="text-blue-500 hover:text-blue-700 transition text-lg" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.teachers.edit', $teacher) }}"
                                class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button @click="confirmDelete({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? '') }}')"
                                class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                        <p>Belum ada data guru.</p>
                    </div>
                @endforelse
            </div>

            @if ($teachers->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $teachers->links() }}
                </div>
            @endif
        </div>
        <!-- Import Modal -->
        <div x-show="importModalOpen" x-transition.opacity
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div @click.outside="importModalOpen = false" class="bg-white rounded-lg w-full max-w-md p-6 mx-4 shadow-xl"
                x-show="importModalOpen" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Import Data Guru (CSV/Excel)</h3>
                    <button @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pilih File CSV atau Excel (.xlsx)</label>
                        <input type="file" name="file" accept=".csv, .txt, .xlsx, .xls"
                            class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500"
                            required>
                        <p class="text-xs text-gray-500 mt-1">Unduh template terlebih dahulu untuk memastikan format kolom
                            sesuai (name, nip, gender, phone, address).</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="importModalOpen = false"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</button>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition">Import</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function teacherCrud() {
            return {
                importModalOpen: false,

                init() {
                    @if (session('success'))
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: '{{ session('success') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    @endif
                    @if (session('error'))
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: '{{ session('error') }}',
                        });
                    @endif
                },

                confirmDelete(id, name) {
                    Swal.fire({
                        title: 'Hapus Guru?',
                        text: `Guru "${name}" akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/admin/teachers/${id}`;
                            form.innerHTML = `@csrf @method('DELETE')`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
@endsection
