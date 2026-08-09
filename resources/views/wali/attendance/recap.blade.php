@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Rekap Absensi
            </h2>

            @if($classroom)
                <p class="text-sm text-gray-500 mt-1">
                    Kelas {{ $classroom->name }}
                </p>
            @endif
        </div>

    </div>

    @if(!$classroom)

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
            <p class="text-yellow-800">
                Anda belum memiliki kelas sebagai wali kelas.
            </p>
        </div>

    @else

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">

            <form
                method="GET"
                action="{{ route('wali-kelas.attendance.recap') }}"
                class="grid grid-cols-1 md:grid-cols-3 gap-4"
            >

                {{-- Bulan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bulan
                    </label>

                    <select
                        name="month"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        @foreach(range(1, 12) as $m)
                            <option
                                value="{{ $m }}"
                                @selected($month == $m)
                            >
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun
                    </label>

                    <select
                        name="year"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option
                                value="{{ $y }}"
                                @selected($year == $y)
                            >
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Button --}}
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full px-4 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition"
                    >
                        Tampilkan Rekap
                    </button>
                </div>

            </form>

        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    Rekap Absensi -
                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                    {{ $year }}
                </h3>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">NIS</th>
                            <th class="px-4 py-3 text-left">Nama Siswa</th>
                            <th class="px-4 py-3 text-center">Hadir</th>
                            <th class="px-4 py-3 text-center">Terlambat</th>
                            <th class="px-4 py-3 text-center">Izin</th>
                            <th class="px-4 py-3 text-center">Sakit</th>
                            <th class="px-4 py-3 text-center">Alpha</th>
                            <th class="px-4 py-3 text-center">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($recaps as $index => $recap)

                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-3">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $recap['student']->nis }}
                                </td>

                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $recap['student']->name }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $recap['hadir'] }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $recap['terlambat'] }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $recap['izin'] }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $recap['sakit'] }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $recap['alpha'] }}
                                </td>

                                <td class="px-4 py-3 text-center font-bold">
                                    {{ $recap['total'] }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="9"
                                    class="px-4 py-10 text-center text-gray-500"
                                >
                                    Belum ada data siswa.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    @endif

</div>

@endsection
