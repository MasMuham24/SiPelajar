@extends('layouts.app')

@section('title', 'Kelola Akun')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Kelola Akun Pengguna</h1>
    <div class="flex gap-2">
        <button id="bulkDeleteBtn" onclick="bulkDelete()" style="display:none"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-trash mr-2"></i> Hapus (<span id="selectedCount">0</span>)
        </button>
        <a href="{{ route('admin.accounts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i> Tambah Akun
        </a>
    </div>
</div>

<div class="mb-4 flex items-center justify-between">
    <form method="GET" action="{{ route('admin.accounts.index') }}" class="flex items-center space-x-3">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari akun..." class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        <select name="role" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            <option value="">Semua Role</option>
            <option value="admin" {{ ($role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="guru" {{ ($role ?? '') == 'guru' ? 'selected' : '' }}>Guru</option>
            <option value="siswa" {{ ($role ?? '') == 'siswa' ? 'selected' : '' }}>Siswa</option>
        </select>
        @if($search || $role)
            <a href="{{ route('admin.accounts.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">Reset</a>
        @endif
        <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
            <i class="fas fa-filter"></i> Filter
        </button>
    </form>
    <form method="GET" action="{{ route('admin.accounts.index') }}" class="flex items-center space-x-2">
        @if($search)
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
        @if($role)
            <input type="hidden" name="role" value="{{ $role }}">
        @endif
        <span class="text-sm text-gray-600">Urutkan:</span>
        <select name="sort" onchange="this.form.submit()" class="px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="name" {{ ($sort ?? '') == 'name' ? 'selected' : '' }}>Nama</option>
            <option value="username" {{ ($sort ?? '') == 'username' ? 'selected' : '' }}>Username</option>
            <option value="email" {{ ($sort ?? '') == 'email' ? 'selected' : '' }}>Email</option>
            <option value="role" {{ ($sort ?? '') == 'role' ? 'selected' : '' }}>Role</option>
            <option value="created_at" {{ ($sort ?? '') == 'created_at' ? 'selected' : '' }}>Tanggal Daftar</option>
        </select>
        <select name="direction" onchange="this.form.submit()" class="px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="asc" {{ ($direction ?? '') == 'asc' ? 'selected' : '' }}>Naik</option>
            <option value="desc" {{ ($direction ?? '') == 'desc' ? 'selected' : '' }}>Turun</option>
        </select>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @php
                    $sortUrl = function($column) use ($sort, $direction) {
                        $params = array_merge(request()->except(['sort', 'direction', 'page']), ['sort' => $column, 'direction' => $sort == $column && $direction == 'asc' ? 'desc' : 'asc']);
                        return route('admin.accounts.index') . '?' . http_build_query($params);
                    };
                @endphp
                <th class="px-4 py-3 text-left">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="w-4 h-4">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ $sortUrl('name') }}" class="text-gray-700 hover:text-blue-600 flex items-center">
                        Nama
                        @if($sort == 'name')
                            <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }} ml-1 text-xs"></i>
                        @endif
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ $sortUrl('username') }}" class="text-gray-700 hover:text-blue-600 flex items-center">
                        Username
                        @if($sort == 'username')
                            <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }} ml-1 text-xs"></i>
                        @endif
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ $sortUrl('email') }}" class="text-gray-700 hover:text-blue-600 flex items-center">
                        Email
                        @if($sort == 'email')
                            <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }} ml-1 text-xs"></i>
                        @endif
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ $sortUrl('role') }}" class="text-gray-700 hover:text-blue-600 flex items-center">
                        Role
                        @if($sort == 'role')
                            <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }} ml-1 text-xs"></i>
                        @endif
                    </a>
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($users as $user)
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="checkbox" class="account-checkbox w-4 h-4" value="{{ $user->id }}" onchange="updateCount()">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $users->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $badgeColor = match($user->role) {
                                'admin' => 'bg-red-100 text-red-800',
                                'guru' => 'bg-blue-100 text-blue-800',
                                'siswa' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                        <a href="{{ route('admin.accounts.show', ['account' => $user]) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.accounts.edit', ['account' => $user]) }}" class="text-yellow-600 hover:text-yellow-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.accounts.destroy', ['account' => $user]) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada akun pengguna.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
<div class="mt-4">
    {{ $users->appends(request()->except('page'))->links() }}
</div>
@endif

<script>
function toggleSelectAll(el) {
    document.querySelectorAll('.account-checkbox').forEach(cb => { cb.checked = el.checked; });
    updateCount();
}
function updateCount() {
    const count = document.querySelectorAll('.account-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkDeleteBtn').style.display = count > 0 ? '' : 'none';
}
function bulkDelete() {
    const checked = document.querySelectorAll('.account-checkbox:checked');
    if (checked.length === 0) { alert('Pilih minimal 1 akun untuk dihapus.'); return; }
    if (!confirm(`Apakah Anda yakin ingin menghapus ${checked.length} akun?`)) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.accounts.bulkDestroy') }}';
    form.innerHTML = '@csrf';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
