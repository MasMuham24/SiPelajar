<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" 
       class="fixed md:static inset-y-0 left-0 w-64 bg-blue-900 text-white transition-transform duration-200 ease-in-out z-40">
    
    <!-- Sidebar Header -->
    <div class="p-6 border-b border-blue-800">
        <h2 class="text-lg font-bold">Menu</h2>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        @if (Auth::user()->role === 'admin')
            <!-- Admin Menu -->
            <a href="{{ route('admin.dashboard') }}" 
               class="block px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.*') ? 'bg-blue-700' : 'hover:bg-blue-800' }}">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-users mr-3"></i> Kelola Pengguna
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-graduation-cap mr-3"></i> Kelola Kelas
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-book mr-3"></i> Kelola Jurusan
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-file-alt mr-3"></i> Laporan
            </a>
        
        @elseif (Auth::user()->role === 'guru')
            <!-- Guru Menu -->
            <a href="{{ route('guru.dashboard') }}" 
               class="block px-4 py-3 rounded-lg transition {{ request()->routeIs('guru.*') ? 'bg-blue-700' : 'hover:bg-blue-800' }}">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-tasks mr-3"></i> Tugas
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-users mr-3"></i> Siswa
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-clipboard-list mr-3"></i> Absensi
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-star mr-3"></i> Penilaian
            </a>
        
        @elseif (Auth::user()->role === 'siswa')
            <!-- Siswa Menu -->
            <a href="{{ route('siswa.dashboard') }}" 
               class="block px-4 py-3 rounded-lg transition {{ request()->routeIs('siswa.*') ? 'bg-blue-700' : 'hover:bg-blue-800' }}">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-book-open mr-3"></i> Tugas Saya
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-clipboard-list mr-3"></i> Absensi
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-bell mr-3"></i> Pengumuman
            </a>
            <a href="#" class="block px-4 py-3 rounded-lg hover:bg-blue-800 transition">
                <i class="fas fa-user mr-3"></i> Profil
            </a>
        @endif
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-4 py-6 border-t border-blue-800">
        <p class="text-xs text-blue-300">SiPelajar v1.0</p>
    </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 md:hidden z-30"></div>