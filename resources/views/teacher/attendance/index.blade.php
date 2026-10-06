@extends('layouts.app')

@section('title', 'Absensi Hari Ini')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Absensi Hari Ini</h1>
        <p class="text-sm text-gray-600">{{ now()->format('d M Y') }}</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('guru.attendance.history') }}" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-history mr-2"></i> Riwayat
        </a>
    </div>
</div>

@php
    $teacher = \App\Models\Teacher::where('user_id', auth()->id())->first();
    $todayTeacherAttendance = $teacher
        ? \App\Models\Attendance::where('teacher_id', $teacher->id)->whereDate('date', today())->first()
        : null;
    $hasTeacherCheckin = $todayTeacherAttendance && $todayTeacherAttendance->check_in;
    $hasTeacherCheckout = $todayTeacherAttendance && $todayTeacherAttendance->check_out;
    $teacherIsLate = $todayTeacherAttendance && ($todayTeacherAttendance->status === 'terlambat' || $todayTeacherAttendance->late_minutes > 0);
    $now = now();
    $teacherCheckoutMin = now()->setTime(16, 0, 0);
    $teacherCheckoutEnabled = $hasTeacherCheckin && !$hasTeacherCheckout && $now->gte($teacherCheckoutMin);
@endphp

@if($teacher)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Absensi Pribadi Guru</h2>

    @if ($hasTeacherCheckin && $hasTeacherCheckout)
        <div class="text-center py-4">
            <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i> Absensi hari ini sudah selesai
            </span>
        </div>
    @elseif ($hasTeacherCheckin && !$hasTeacherCheckout)
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Anda sudah absen masuk</p>
                <p class="text-xs text-gray-400">Jam masuk: {{ $todayTeacherAttendance->check_in->format('H:i') }}</p>
            </div>
            <button id="teacherCheckoutBtn" onclick="performTeacherCheckout()"
                    {{ !$teacherCheckoutEnabled ? 'disabled' : '' }}
                    class="px-6 py-3 rounded-lg text-white font-medium transition
                           {{ $teacherCheckoutEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                <i class="fas fa-sign-out-alt mr-2"></i>
                {{ $teacherCheckoutEnabled ? 'Absen Pulang' : 'Belum Waktunya Checkout' }}
            </button>
        </div>
        @if (!$teacherCheckoutEnabled && $now->lt($teacherCheckoutMin))
            <p class="text-xs text-yellow-600 mt-2" id="teacherCheckoutTimer">
                <i class="fas fa-clock mr-1"></i>
                Checkout baru bisa dilakukan setelah pukul 16:00. Sekarang pukul {{ $now->format('H:i') }}.
            </p>
        @endif
    @else
        <div class="text-center py-4">
            <p class="text-gray-600 mb-4">Anda belum melakukan absensi masuk hari ini.</p>
            <button id="teacherCheckinBtn" onclick="performTeacherCheckin()"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Absen Masuk
            </button>
        </div>
    @endif
</div>
@endif

<script>
function updateTeacherCheckoutButton() {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const checkoutHour = 16;
    const checkoutMinute = 0;
    const isCheckoutTime = (currentHour > checkoutHour) || (currentHour === checkoutHour && currentMinute >= checkoutMinute);

    const checkoutBtn = document.getElementById('teacherCheckoutBtn');
    const checkoutTimer = document.getElementById('teacherCheckoutTimer');

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

setInterval(updateTeacherCheckoutButton, 30000);
updateTeacherCheckoutButton();
</script>

@php
    $badgeColor = function($status) {
        return match(ucfirst(strtolower($status ?? ''))) {
            'Hadir' => 'bg-green-100 text-green-800',
            'Terlambat' => 'bg-yellow-100 text-yellow-800',
            'Izin' => 'bg-blue-100 text-blue-800',
            'Sakit' => 'bg-orange-100 text-orange-800',
            'Alfa', 'Alpha' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    };
@endphp

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
            </tr>
        </thead>
        <tbody id="attendanceTableBody" class="bg-white divide-y divide-gray-200">
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendances->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->student->user->name ?? $attendance->student->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->classroom->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->date ? $attendance->date->format('d M Y') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor($attendance->status) }}">{{ $attendance->status }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' menit' : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300"></i>
                        <p>Belum ada data absensi hari ini.</p>
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
function performTeacherCheckin() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung GPS. Silakan gunakan browser yang mendukung lokasi.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("guru.attendance.checkin") }}';
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

function performTeacherCheckout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("guru.attendance.checkout") }}';
    form.style.display = 'none';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    document.body.appendChild(form);
    form.submit();
}

// Auto-refresh via AJAX Polling (interval 5 detik)
(function () {
    const tableBody = document.getElementById('attendanceTableBody');
    if (!tableBody) return;

    let isPolling = false;
    let lastDataHash = null;

    function escapeHtml(str) {
        if (str === null || str === undefined || str === '') return '-';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderAttendanceTable(items) {
        if (!items || items.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300"></i>
                        <p>Belum ada data absensi hari ini.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach(function (att) {
            html += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(att.index)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(att.student_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(att.classroom_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(att.date)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">${escapeHtml(att.check_in)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">${escapeHtml(att.check_out)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 py-1 text-xs rounded-full ${escapeHtml(att.badge_class)}">${escapeHtml(att.status_label)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        ${escapeHtml(att.late_text)}
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
    }

    async function pollAttendance() {
        if (isPolling) return;
        isPolling = true;

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const currentDate = urlParams.get('date');
            const currentPage = urlParams.get('page');
            let fetchUrl = '{{ route("guru.attendance.data") }}';
            const params = new URLSearchParams();
            if (currentDate) params.append('date', currentDate);
            if (currentPage) params.append('page', currentPage);
            if (params.toString()) {
                fetchUrl += '?' + params.toString();
            }

            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const json = await response.json();
                if (json.success && Array.isArray(json.data)) {
                    const currentHash = JSON.stringify(json.data);
                    if (currentHash !== lastDataHash) {
                        lastDataHash = currentHash;
                        renderAttendanceTable(json.data);
                    }
                }
            }
        } catch (err) {
            console.warn('Polling absensi guru gagal, mencoba lagi dalam 5 detik...', err);
        } finally {
            isPolling = false;
            setTimeout(pollAttendance, 5000);
        }
    }

    // Mulai polling 5 detik setelah halaman selesai dimuat
    setTimeout(pollAttendance, 5000);
})();
</script>
@endsection
