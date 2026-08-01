@extends('layouts.app')

@section('title', 'Kelola Jurusan')

@section('content')
<div x-data="majorCrud()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Jurusan (Major)</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data jurusan yang tersedia di sistem.</p>
        </div>
        <button @click="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
            <i class="fas fa-plus"></i> Tambah Jurusan
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-semibold text-gray-600 w-16">No</th>
                        <th class="p-4 font-semibold text-gray-600">Nama Jurusan</th>
                        <th class="p-4 font-semibold text-gray-600 w-32">Kode</th>
                        <th class="p-4 font-semibold text-gray-600 w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($majors as $major)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 text-gray-800">{{ $loop->iteration + ($majors->currentPage() - 1) * $majors->perPage() }}</td>
                            <td class="p-4 text-gray-800 font-medium">{{ $major->name }}</td>
                            <td class="p-4 text-gray-800">
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-semibold">{{ $major->code }}</span>
                            </td>
                            <td class="p-4">
                                <button @click="openModal('edit', {{ $major->id }}, '{{ addslashes($major->name) }}', '{{ $major->code }}')" class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete({{ $major->id }}, '{{ addslashes($major->name) }}')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                                <p>Belum ada data jurusan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($majors as $major)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs text-gray-400 w-6 shrink-0">{{ $loop->iteration + ($majors->currentPage() - 1) * $majors->perPage() }}</span>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $major->name }}</p>
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold">{{ $major->code }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button @click="openModal('edit', {{ $major->id }}, '{{ addslashes($major->name) }}', '{{ $major->code }}')" class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click="confirmDelete({{ $major->id }}, '{{ addslashes($major->name) }}')" class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                    <p>Belum ada data jurusan.</p>
                </div>
            @endforelse
        </div>

        @if ($majors->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $majors->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Jurusan</label>
                    <input type="text" name="name" x-model="formData.name" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Kode Jurusan</label>
                    <input type="text" name="code" x-model="formData.code" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function majorCrud() {
        return {
            modalOpen: false,
            modalTitle: 'Tambah Jurusan',
            formAction: '{{ route('admin.majors.store') }}',
            methodField: 'POST',
            formData: { name: '', code: '' },

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
            },

            openModal(type, id = null, name = '', code = '') {
                if (type === 'add') {
                    this.modalTitle = 'Tambah Jurusan';
                    this.formAction = '{{ route('admin.majors.store') }}';
                    this.methodField = 'POST';
                    this.formData = { name: '', code: '' };
                } else {
                    this.modalTitle = 'Edit Jurusan';
                    this.formAction = `/admin/majors/${id}`;
                    this.methodField = 'PUT';
                    this.formData = { name: name, code: code };
                }
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
            },

            confirmDelete(id, name) {
                Swal.fire({
                    title: 'Hapus Jurusan?',
                    text: `Jurusan "${name}" akan dihapus permanen.`,
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
                        form.action = `/admin/majors/${id}`;
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
