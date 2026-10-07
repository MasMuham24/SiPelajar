@extends('layouts.app')

@section('title', 'Kelola Lokasi Sekolah')

@section('content')
<div x-data="officeCrud()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Lokasi Sekolah</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data lokasi sekolah untuk sistem absensi.</p>
        </div>
        <button @click="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
            <i class="fas fa-plus"></i> Tambah Lokasi
        </button>
        <button @click="bulkDelete()" x-show="selectedCount > 0" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2 w-full sm:w-auto justify-center">
            <i class="fas fa-trash"></i> Hapus (<span x-text="selectedCount"></span>)
        </button>
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
                        <th class="p-4 font-semibold text-gray-600">Nama Lokasi</th>
                        <th class="p-4 font-semibold text-gray-600">Latitude</th>
                        <th class="p-4 font-semibold text-gray-600">Longitude</th>
                        <th class="p-4 font-semibold text-gray-600 w-32">Radius (m)</th>
                        <th class="p-4 font-semibold text-gray-600 w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offices as $office)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4">
                                <input type="checkbox" class="office-checkbox w-4 h-4" value="{{ $office->id }}" @change="updateSelectCount()">
                            </td>
                            <td class="p-4 text-gray-800">{{ $loop->iteration + ($offices->currentPage() - 1) * $offices->perPage() }}</td>
                            <td class="p-4 text-gray-800 font-medium">{{ $office->name }}</td>
                            <td class="p-4 text-gray-800">{{ $office->latitude }}</td>
                            <td class="p-4 text-gray-800">{{ $office->longitude }}</td>
                            <td class="p-4 text-gray-800">
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-semibold">{{ $office->radius }}</span>
                            </td>
                            <td class="p-4">
                                <button @click="openModal('edit', {{ $office->id }}, '{{ addslashes($office->name) }}', '{{ $office->latitude }}', '{{ $office->longitude }}', '{{ $office->radius }}')" class="text-yellow-500 hover:text-yellow-700 mr-3 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete({{ $office->id }}, '{{ addslashes($office->name) }}')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-map-marker-alt text-4xl mb-2 block text-gray-300"></i>
                                <p>Belum ada data lokasi sekolah.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($offices as $office)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs text-gray-400 w-6 shrink-0">{{ $loop->iteration + ($offices->currentPage() - 1) * $offices->perPage() }}</span>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $office->name }}</p>
                            <div class="flex gap-2 mt-1 text-xs text-gray-500">
                                <span>Lat: {{ $office->latitude }}</span>
                                <span>Lng: {{ $office->longitude }}</span>
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">Radius: {{ $office->radius }}m</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button @click="openModal('edit', {{ $office->id }}, '{{ addslashes($office->name) }}', '{{ $office->latitude }}', '{{ $office->longitude }}', '{{ $office->radius }}')" class="text-yellow-500 hover:text-yellow-700 transition text-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click="confirmDelete({{ $office->id }}, '{{ addslashes($office->name) }}')" class="text-red-500 hover:text-red-700 transition text-lg" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-map-marker-alt text-4xl mb-2 block text-gray-300"></i>
                    <p>Belum ada data lokasi sekolah.</p>
                </div>
            @endforelse
        </div>

        @if ($offices->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $offices->links() }}
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="formData.name" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Latitude <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" name="latitude" x-model="formData.latitude" placeholder="-6.895114" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Longitude <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="decimal" name="longitude" x-model="formData.longitude" placeholder="110.617549" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Radius (meter) <span class="text-red-500">*</span></label>
                    <input type="number" name="radius" x-model="formData.radius" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" required min="10">
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
    function officeCrud() {
        return {
            modalOpen: false,
            modalTitle: 'Tambah Lokasi Sekolah',
            formAction: '{{ route('admin.offices.store') }}',
            methodField: 'POST',
            formData: { name: '', latitude: '', longitude: '', radius: 100 },
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
                const checkboxes = document.querySelectorAll('.office-checkbox');
                checkboxes.forEach(checkbox => { checkbox.checked = event.target.checked; });
                this.updateSelectCount();
            },

            updateSelectCount() {
                this.selectedCount = document.querySelectorAll('.office-checkbox:checked').length;
            },

            bulkDelete() {
                const checkboxes = document.querySelectorAll('.office-checkbox:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Pilih Lokasi', text: 'Pilih minimal 1 lokasi untuk dihapus.' });
                    return;
                }
                Swal.fire({
                    title: 'Hapus Lokasi?',
                    text: `${checkboxes.length} lokasi akan dihapus permanen.`,
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
                        form.action = '{{ route('admin.offices.bulkDestroy') }}';
                        let idsInput = '';
                        ids.forEach((id) => { idsInput += `<input type="hidden" name="ids[]" value="${id}">`; });
                        form.innerHTML = `@csrf ${idsInput}`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            },

            openModal(type, id = null, name = '', latitude = '', longitude = '', radius = 100) {
                if (type === 'add') {
                    this.modalTitle = 'Tambah Lokasi Sekolah';
                    this.formAction = '{{ route('admin.offices.store') }}';
                    this.methodField = 'POST';
                    this.formData = { name: '', latitude: '', longitude: '', radius: 100 };
                } else {
                    this.modalTitle = 'Edit Lokasi Sekolah';
                    this.formAction = `/admin/offices/${id}`;
                    this.methodField = 'PUT';
                    this.formData = { name: name, latitude: latitude, longitude: longitude, radius: radius };
                }
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
            },

            confirmDelete(id, name) {
                Swal.fire({
                    title: 'Hapus Lokasi Sekolah?',
                    text: `Lokasi "${name}" akan dihapus permanen.`,
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
                        form.action = `/admin/offices/${id}`;
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