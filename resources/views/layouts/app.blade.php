<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SiPelajar') - Sistem Informasi Pelajar</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- AlpineJS CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Content wrapper -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Main Content -->
            <main class="w-full grow p-6">
                @if (session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif
                
                @if (session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.footer')
            
        </div>
    </div>
</body>
</html>