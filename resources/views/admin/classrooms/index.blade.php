@extends('layouts.app')

@section('title', 'Kelola Kelas')

@section('content')
<div x-data="classroomCrud()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Kelas</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data kelas yang tersedia di sistem.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
                <i class="fas fa-plus"></i> Tambah Kelas
            </button>
            <button @click="bulkDelete()" x-show="selectedCount > 0" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
                <i class="fas fa-trash"></i> Hapus (<span x-text="selectedCount"></span>)
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-12">
                            <input type="checkbox" @change="toggleSelectAll($event)" class="w-4 h-4">
                        </th>
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Nama Kelas</th>
                        <th class="p-4 font-semibold text-gray-600">Jurusan</th>
                        <th class="p-4 font-semibold text-gray-600 w-24">Tingkat</th>
                        <th class="p-4 font-semibold text-gray-600 w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classrooms as $classroom)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4">
                                <input type="checkbox" class="classroom-checkbox w-4 h-4" value="{{ $classroom->id }}" @change="updateSelectCount()">
                            </td>
                            <td class="p-4 text-gray-800">{{ $loop->iteration + ($classrooms->currentPage() - 1) * $classrooms->perPage() }}</td>
                            <td class="p-4 text-gray-800 font-medium">{{ $classroom->name }}</td>
                            <td class="p-4 text-gray-800">{{ $classroom->major->name }}</td>
                            <td class="p-4 text-gray-800">
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold">Kelas {{ $classroom->grade }}</span>
                            </td>
                            <td class="p-4">
                                <button @click="openModal('edit', {{ $classroom->id }}, '{{ addslashes($classroom->name) }}', '{{ $classroom->grade }}', '{{ $classroom->major_id }}')" class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete({{ $classroom->id }}, '{{ addslashes($classroom->name) }}')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                <p>Belum ada data kelas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($classrooms as $classroom)
                <div class="p-4 flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $classroom->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $classroom->major->name }}</p>
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-semibold inline-block mt-2">Kelas {{ $classroom->grade }}</span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button @click="openModal('edit', {{ $classroom->id }}, '{{ addslashes($classroom->name) }}', '{{ $classroom->grade }}', '{{ $classroom->major_id }}')" class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click="confirmDelete({{ $classroom->id }}, '{{ addslashes($classroom->name) }}')" class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                    <p>Belum ada data kelas.</p>
                </div>
            @endforelse
        </div>

        @if ($classrooms->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $classrooms->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div @click.outside="closeModal()" class="bg-white rounded-lg w-full max-w-md p-6 mx-4 shadow-xl" x-show="modalOpen" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="formAction" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="methodField">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kelas</label>
                    <input type="text" name="name" x-model="formData.name" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jurusan</label>
                    <select name="major_id" x-model="formData.major_id" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($majors as $major)
                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tingkat Kelas</label>
                    <select name="grade" x-model="formData.grade" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function classroomCrud() {
        return {
            modalOpen: false,
            modalTitle: 'Tambah Kelas',
            formAction: '{{ route('admin.classrooms.store') }}',
            methodField: 'POST',
            formData: { name: '', major_id: '', grade: '' },
            majors: @json($majors),
            selectedCount: 0,

            init() {
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        timer: 1500,
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

            toggleSelectAll(event) {
                const checkboxes = document.querySelectorAll('.classroom-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = event.target.checked;
                });
                this.updateSelectCount();
            },

            updateSelectCount() {
                const checkboxes = document.querySelectorAll('.classroom-checkbox:checked');
                this.selectedCount = checkboxes.length;
            },

            bulkDelete() {
                const checkboxes = document.querySelectorAll('.classroom-checkbox:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Kelas',
                        text: 'Pilih minimal 1 kelas untuk dihapus.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Kelas?',
                    text: `${checkboxes.length} kelas akan dihapus permanen.`,
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
                        form.action = '{{ route('admin.classrooms.bulkDestroy') }}';
                        let idsInput = '';
                        ids.forEach((id, index) => {
                            idsInput += `<input type="hidden" name="ids[]" value="${id}">`;
                        });
                        form.innerHTML = `@csrf ${idsInput}`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            },

            openModal(type, id = null, name = '', grade = '', majorId = '') {
                if (type === 'add') {
                    this.modalTitle = 'Tambah Kelas';
                    this.formAction = '{{ route('admin.classrooms.store') }}';
                    this.methodField = 'POST';
                    this.formData = { name: '', major_id: '', grade: '' };
                } else {
                    this.modalTitle = 'Edit Kelas';
                    this.formAction = `/admin/classrooms/${id}`;
                    this.methodField = 'PUT';
                    this.formData = { name: name, major_id: majorId, grade: grade };
                }
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
            },

            confirmDelete(id, name) {
                Swal.fire({
                    title: 'Hapus Kelas?',
                    text: `Kelas "${name}" akan dihapus permanen.`,
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
                        form.action = `/admin/classrooms/${id}`;
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
