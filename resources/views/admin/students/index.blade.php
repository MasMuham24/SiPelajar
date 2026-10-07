@extends('layouts.app')

@section('title', 'Kelola Siswa')

@section('content')
<div x-data="studentCrud()">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Siswa</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data siswa yang terdaftar di sistem.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.students.template', ['format' => 'csv']) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Template CSV
            </a>
            <a href="{{ route('admin.students.template', ['format' => 'xlsx']) }}" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Template XLSX
            </a>
            <button @click="importModalOpen = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-file-import"></i> Import Siswa
            </button>
            <a href="{{ route('admin.students.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Siswa
            </a>
            <button @click="bulkDelete()" x-show="selectedCount > 0" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-trash"></i> Hapus (<span x-text="selectedCount"></span>)
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">

        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex gap-2">
                <div class="relative flex-1 min-w-0">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS, NISN, atau nama..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 text-sm">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white w-10 h-10 flex items-center justify-center rounded-md transition shrink-0">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-12">
                            <input type="checkbox" @change="toggleSelectAll($event)" class="w-4 h-4">
                        </th>
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Foto</th>
                        <th class="p-4 font-semibold text-gray-600">Nama</th>
                        <th class="p-4 font-semibold text-gray-600">NIS</th>
                        <th class="p-4 font-semibold text-gray-600">NISN</th>
                        <th class="p-4 font-semibold text-gray-600">Kelas</th>
                        <th class="p-4 font-semibold text-gray-600">JK</th>
                        <th class="p-4 font-semibold text-gray-600 w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4">
                                <input type="checkbox" class="student-checkbox w-4 h-4" value="{{ $student->id }}" @change="updateSelectCount()">
                            </td>
                            <td class="p-4 text-gray-800">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                            <td class="p-4">
                                @if ($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->user->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                        {{ strtoupper(substr($student->user->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 text-gray-800 font-medium">{{ $student->user->name ?? '-' }}</td>
                            <td class="p-4 text-gray-800">{{ $student->nis }}</td>
                            <td class="p-4 text-gray-800">{{ $student->nisn }}</td>
                            <td class="p-4 text-gray-800">
                                @if ($student->classroom)
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-semibold">
                                        {{ $student->classroom->name }} {{ $student->classroom->grade }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-4 text-gray-800">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $student->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $student->gender === 'Laki-laki' ? 'L' : 'P' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-blue-500 hover:text-blue-700 mr-3 transition" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button @click="confirmDelete({{ $student->id }}, '{{ addslashes($student->user->name ?? '') }}')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                <p>Belum ada data siswa.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($students as $student)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->user->name }}" class="w-10 h-10 rounded-full object-cover shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold shrink-0">
                                {{ strtoupper(substr($student->user->name ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $student->user->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">NIS: {{ $student->nis }}</p>
                            <div class="flex gap-1 mt-1">
                                @if ($student->classroom)
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $student->classroom->name }} {{ $student->classroom->grade }}</span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $student->gender === 'Laki-laki' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $student->gender === 'Laki-laki' ? 'L' : 'P' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('admin.students.show', $student) }}" class="text-blue-500 hover:text-blue-700 transition text-lg" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button @click="confirmDelete({{ $student->id }}, '{{ addslashes($student->user->name ?? '') }}')" class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                    <p>Belum ada data siswa.</p>
                </div>
            @endforelse
        </div>

        @if ($students->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    <!-- Import Modal -->
    <div x-show="importModalOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div @click.outside="importModalOpen = false" class="bg-white rounded-lg w-full max-w-lg p-6 mx-4 shadow-xl" x-show="importModalOpen" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Import Data Siswa</h3>
                <button @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4 bg-gray-50 border border-gray-200 p-3 rounded-md text-xs text-gray-600 space-y-1">
                    <p><strong>Format yang didukung:</strong> CSV / XLSX (.csv, .xlsx, .xls)</p>
                    <p><strong>Kolom:</strong> Nama, NIS, NISN, Jurusan, Kelas, Gender, No. HP, Alamat</p>
                    <div class="flex items-center gap-2 pt-2 border-t border-gray-200 mt-2">
                        <span>Unduh template:</span>
                        <a href="{{ route('admin.students.template', ['format' => 'csv']) }}" class="text-emerald-600 hover:underline inline-flex items-center gap-1 font-semibold">
                            <i class="fas fa-download"></i> Template CSV
                        </a>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('admin.students.template', ['format' => 'xlsx']) }}" class="text-teal-600 hover:underline inline-flex items-center gap-1 font-semibold">
                            <i class="fas fa-download"></i> Template XLSX
                        </a>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih File</label>
                    <input type="file" name="file" accept=".csv, .txt, .xlsx, .xls" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="importModalOpen = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition">Import</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function studentCrud() {
        return {
            importModalOpen: false,
            selectedCount: 0,

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
                        html: {!! json_encode(session('error')) !!},
                    });
                @endif
            },

            toggleSelectAll(event) {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(checkbox => { checkbox.checked = event.target.checked; });
                this.updateSelectCount();
            },

            updateSelectCount() {
                this.selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
            },

            bulkDelete() {
                const checkboxes = document.querySelectorAll('.student-checkbox:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Pilih Siswa', text: 'Pilih minimal 1 siswa untuk dihapus.' });
                    return;
                }
                Swal.fire({
                    title: 'Hapus Siswa?',
                    text: `${checkboxes.length} siswa akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const ids = Array.from(checkboxes).map(cb => cb.value);
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('admin.students.bulkDestroy') }}';
                        let idsInput = '';
                        ids.forEach((id) => { idsInput += `<input type="hidden" name="ids[]" value="${id}">`; });
                        form.innerHTML = `@csrf ${idsInput}`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            },

            confirmDelete(id, name) {
                Swal.fire({
                    title: 'Hapus Siswa?',
                    text: `Siswa "${name}" akan dihapus permanen.`,
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
                        form.action = `/admin/students/${id}`;
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
