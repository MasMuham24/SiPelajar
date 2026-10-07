@extends('layouts.app')

@section('title', 'Kelola Tugas')

@section('content')
<div x-data="assignmentCrud()">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Tugas</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola tugas untuk kelas yang diajar.</p>
        </div>
        <div class="flex gap-2">
            <button @click="bulkDelete()" x-show="selectedCount > 0" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-trash"></i> Hapus (<span x-text="selectedCount"></span>)
            </button>
            <a href="{{ route('guru.assignments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Tugas
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-12">
                            <input type="checkbox" @change="toggleSelectAll($event)" class="w-4 h-4">
                        </th>
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Judul</th>
                        <th class="p-4 font-semibold text-gray-600">Kelas</th>
                        <th class="p-4 font-semibold text-gray-600">Deadline</th>
                        <th class="p-4 font-semibold text-gray-600">Status</th>
                        <th class="p-4 font-semibold text-gray-600">Lampiran</th>
                        <th class="p-4 font-semibold text-gray-600 w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4">
                                <input type="checkbox" class="assignment-checkbox w-4 h-4" value="{{ $assignment->id }}" @change="updateSelectCount()">
                            </td>
                            <td class="p-4 text-gray-800">{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                            <td class="p-4 text-gray-800 font-medium">{{ $assignment->title }}</td>
                            <td class="p-4 text-gray-800">{{ $assignment->classroom->name ?? '-' }}</td>
                            <td class="p-4 text-gray-800">{{ $assignment->deadline->format('d M Y H:i') }}</td>
                            <td class="p-4">
                                @if ($assignment->is_active)
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Diakhiri</span>
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
                                <a href="{{ route('guru.assignments.show', $assignment) }}" class="text-blue-500 hover:text-blue-700 mr-3 transition" title="Detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('guru.assignments.edit', $assignment) }}" class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit"><i class="fas fa-edit"></i></a>
                                @if ($assignment->is_active)
                                    <button @click="confirmEnd({{ $assignment->id }}, '{{ addslashes($assignment->title) }}')" class="text-indigo-500 hover:text-indigo-700 mr-3 transition" title="Akhiri Tugas"><i class="fas fa-stop"></i></button>
                                @endif
                                <button @click="confirmDelete({{ $assignment->id }}, '{{ addslashes($assignment->title) }}')" class="text-red-500 hover:text-red-700 transition" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                <p>Belum ada data tugas.</p>
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
                        <p class="text-xs text-gray-500">Kelas: {{ $assignment->classroom->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">Deadline: {{ $assignment->deadline->format('d M Y H:i') }}</p>
                        <div class="mt-1">
                            @if ($assignment->is_active)
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Diakhiri</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('guru.assignments.show', $assignment) }}" class="text-blue-500 hover:text-blue-700 transition text-lg" title="Detail"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('guru.assignments.edit', $assignment) }}" class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit"><i class="fas fa-edit"></i></a>
                        @if ($assignment->is_active)
                            <button @click="confirmEnd({{ $assignment->id }}, '{{ addslashes($assignment->title) }}')" class="text-indigo-500 hover:text-indigo-700 transition text-lg" title="Akhiri Tugas"><i class="fas fa-stop"></i></button>
                        @endif
                        <button @click="confirmDelete({{ $assignment->id }}, '{{ addslashes($assignment->title) }}')" class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                    <p>Belum ada data tugas.</p>
                </div>
            @endforelse
        </div>

        @if ($assignments->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function assignmentCrud() {
        return {
            selectedCount: 0,

            toggleSelectAll(event) {
                const checkboxes = document.querySelectorAll('.assignment-checkbox');
                checkboxes.forEach(checkbox => { checkbox.checked = event.target.checked; });
                this.updateSelectCount();
            },

            updateSelectCount() {
                this.selectedCount = document.querySelectorAll('.assignment-checkbox:checked').length;
            },

            bulkDelete() {
                const checkboxes = document.querySelectorAll('.assignment-checkbox:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Pilih Tugas', text: 'Pilih minimal 1 tugas untuk dihapus.' });
                    return;
                }
                Swal.fire({
                    title: 'Hapus Tugas?',
                    text: `${checkboxes.length} tugas akan dihapus permanen.`,
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
                        form.action = '{{ route('guru.assignments.bulkDestroy') }}';
                        let idsInput = '';
                        ids.forEach((id) => { idsInput += `<input type="hidden" name="ids[]" value="${id}">`; });
                        form.innerHTML = `@csrf ${idsInput}`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            },

            confirmDelete(id, title) {
                Swal.fire({
                    title: 'Hapus Tugas?',
                    text: `Tugas "${title}" akan dihapus permanen.`,
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
                        form.action = `/guru/assignments/${id}`;
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            },
            confirmEnd(id, title) {
                Swal.fire({
                    title: 'Akhiri Tugas?',
                    text: `Tugas "${title}" akan ditutup. Siswa tidak dapat lagi mengirim atau mengedit jawaban.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, akhiri!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/guru/assignments/${id}/end`;
                        form.innerHTML = `@csrf @method('PATCH')`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        }
    }
</script>
@endsection
