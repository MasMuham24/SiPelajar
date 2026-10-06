@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Absensi Saya</h1>
        <p class="text-sm text-gray-600">{{ now()->format('d M Y') }}</p>
    </div>
</div>

@php
    $todayAttendance = \App\Models\Attendance::where('student_id', auth()->user()->student->id)
        ->whereDate('date', today())
        ->first();

    $hasCheckin = $todayAttendance && $todayAttendance->check_in;
    $hasCheckout = $todayAttendance && $todayAttendance->check_out;
    $isLate = $todayAttendance && ($todayAttendance->status === 'terlambat' || $todayAttendance->late_minutes > 0);
    $now = now();
    $checkoutMin = now()->setTime(15, 30, 0);
    $checkoutEnabled = $hasCheckin && !$hasCheckout && $now->gte($checkoutMin);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-sign-in-alt text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Absen Masuk</p>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $hasCheckin ? $todayAttendance->check_in->format('H:i') : '-' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-sign-out-alt text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Absen Pulang</p>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $hasCheckout ? $todayAttendance->check_out->format('H:i') : '-' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full {{ $isLate ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center">
                <i class="fas {{ $isLate ? 'fa-exclamation-triangle text-red-600' : 'fa-check-circle text-green-600' }} text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $hasCheckin ? ($isLate ? 'Terlambat (' . $todayAttendance->late_minutes . ' menit)' : 'Tepat Waktu') : '-' }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Absensi Hari Ini</h2>

    @if ($hasCheckin && $hasCheckout)
        <div class="text-center py-4">
            <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i> Absensi hari ini sudah selesai
            </span>
        </div>
    @elseif ($hasCheckin && !$hasCheckout)
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Anda sudah absen masuk</p>
                <p class="text-xs text-gray-400">Jam masuk: {{ $todayAttendance->check_in->format('H:i') }}</p>
            </div>
            <button id="checkoutBtn" onclick="performCheckout()"
                    {{ !$checkoutEnabled ? 'disabled' : '' }}
                    class="px-6 py-3 rounded-lg text-white font-medium transition
                           {{ $checkoutEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                <i class="fas fa-sign-out-alt mr-2"></i>
                {{ $checkoutEnabled ? 'Absen Pulang' : 'Belum Waktunya Checkout' }}
            </button>
        </div>
        @if (!$checkoutEnabled && $now->lt($checkoutMin))
            <p class="text-xs text-yellow-600 mt-2" id="checkoutTimer">
                <i class="fas fa-clock mr-1"></i>
                Checkout baru bisa dilakukan setelah pukul 15:30. Sekarang pukul {{ $now->format('H:i') }}.
            </p>
        @endif
    @else
        <div class="text-center py-4">
            <p class="text-gray-600 mb-4">Anda belum melakukan absensi masuk hari ini.</p>
            <button id="checkinBtn" onclick="performCheckin()"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Absen Masuk
            </button>
        </div>
    @endif
</div>

<script>
function updateButtons() {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const checkoutHour = 15;
    const checkoutMinute = 30;
    const isCheckoutTime = (currentHour > checkoutHour) || (currentHour === checkoutHour && currentMinute >= checkoutMinute);

    const checkoutBtn = document.getElementById('checkoutBtn');
    const checkoutTimer = document.getElementById('checkoutTimer');

    if (checkoutBtn && isCheckoutTime) {
        checkoutBtn.disabled = false;
        checkoutBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        checkoutBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        checkoutBtn.innerHTML = '<i class="fas fa-sign-out-alt mr-2"></i> Absen Pulang';
    }

    if (checkoutTimer && isCheckoutTime) {
        checkoutTimer.remove();
    }
}

setInterval(updateButtons, 30000);
updateButtons();
</script>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->classroom->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $statusLabel = match($attendance->status) {
                                'izin', 'Izin' => 'Izin',
                                'sakit', 'Sakit' => 'Sakit',
                                'alpha', 'alfa', 'Alfa' => 'Alfa',
                                'terlambat', 'Terlambat' => 'Terlambat',
                                'hadir', 'Hadir' => ($attendance->late_minutes > 0 ? 'Terlambat' : 'Tepat Waktu'),
                                default => $attendance->late_minutes > 0 ? 'Terlambat' : 'Tepat Waktu',
                            };
                            $badgeColor = match($statusLabel) {
                                'Tepat Waktu', 'Hadir' => 'bg-green-100 text-green-800',
                                'Terlambat' => 'bg-yellow-100 text-yellow-800',
                                'Izin' => 'bg-blue-100 text-blue-800',
                                'Sakit' => 'bg-orange-100 text-orange-800',
                                'Alfa' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300"></i>
                        <p>Belum ada data absensi.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($attendances->hasPages())
<div class="mt-4">
    {{ $attendances->links() }}
</div>
@endif

<script>
function performCheckin() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung GPS. Silakan gunakan browser yang mendukung lokasi.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("siswa.attendance.checkin") }}';
            form.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const lat = document.createElement('input');
            lat.type = 'hidden';
            lat.name = 'latitude';
            lat.value = position.coords.latitude;
            form.appendChild(lat);

            const lng = document.createElement('input');
            lng.type = 'hidden';
            lng.name = 'longitude';
            lng.value = position.coords.longitude;
            form.appendChild(lng);

            document.body.appendChild(form);
            form.submit();
        },
        function(error) {
            let message = 'Tidak dapat mengakses lokasi.';
            if (error.code === 1) {
                message = 'Izin lokasi ditolak. Silakan izinkan akses lokasi untuk absensi.';
            } else if (error.code === 2) {
                message = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
            } else if (error.code === 3) {
                message = 'Timeout saat mengambil lokasi. Silakan coba lagi.';
            }
            alert(message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function performCheckout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("siswa.attendance.checkout") }}';
    form.style.display = 'none';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
