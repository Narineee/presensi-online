@extends('layouts.admin')

@section('title', 'Monitoring Presensi')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Presensi Harian</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau rekaman absensi, titik koordinat GPS, dan bukti foto selfie Magang & CS.</p>
        </div>
    </div>

    <!-- Stats Ringkasan Hari Ini -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Hadir Hari Ini</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $statsToday['total_hadir'] }} Orang</p>
                <span class="text-[11px] text-emerald-600 font-medium">Tercatat di sistem</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bekerja Onsite</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsToday['total_onsite'] }} Orang</p>
                <span class="text-[11px] text-slate-400">Di Kantor</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                🏢
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bekerja WFH</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsToday['total_wfh'] }} Orang</p>
                <span class="text-[11px] text-slate-400">Dari Rumah / Remote</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                🏠
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sudah Absen Pulang</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $statsToday['total_sudah_pulang'] }} Orang</p>
                <span class="text-[11px] text-slate-400">Jam kerja selesai</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                🏁
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.presensi.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Presensi</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ request('tanggal', $filterTanggal) }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori Peran</label>
                <select name="role" id="role" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Peran (Magang & CS)</option>
                    <option value="magang" {{ request('role') === 'magang' ? 'selected' : '' }}>Magang</option>
                    <option value="cs" {{ request('role') === 'cs' ? 'selected' : '' }}>Customer Service (CS)</option>
                </select>
            </div>

            <div>
                <label for="mode_kerja" class="block text-xs font-semibold text-slate-700 mb-1.5">Mode Kerja</label>
                <select name="mode_kerja" id="mode_kerja" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Mode</option>
                    <option value="onsite" {{ request('mode_kerja') === 'onsite' ? 'selected' : '' }}>Onsite (Kantor)</option>
                    <option value="wfh" {{ request('mode_kerja') === 'wfh' ? 'selected' : '' }}>WFH (Rumah)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['tanggal', 'role', 'mode_kerja']))
                    <a href="{{ route('admin.presensi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Monitoring Presensi -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Pengguna</th>
                        <th class="px-6 py-4">Peran</th>
                        <th class="px-6 py-4">Mode</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Bukti Foto</th>
                        <th class="px-6 py-4">Lokasi GPS</th>
                        <th class="px-6 py-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($presensi as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900 w-12">
                                {{ $presensi->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $item->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">@<span>{{ $item->pengguna->username ?? '-' }}</span></div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->pengguna->role === 'magang')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Magang
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                        CS
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->mode_kerja === 'onsite')
                                    <span class="inline-flex items-center gap-1 font-bold text-blue-700">
                                        🏢 Onsite
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-bold text-amber-700">
                                        🏠 WFH
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if ($item->foto_masuk_url)
                                        <a href="{{ $item->foto_masuk_url }}" target="_blank" title="Foto Masuk" class="group relative">
                                            <img src="{{ $item->foto_masuk_url }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 hover:scale-110 transition shadow-xs">
                                        </a>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif

                                    @if ($item->foto_keluar_url)
                                        <a href="{{ $item->foto_keluar_url }}" target="_blank" title="Foto Pulang" class="group relative">
                                            <img src="{{ $item->foto_keluar_url }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 hover:scale-110 transition shadow-xs">
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->lokasi_masuk)
                                    <a href="https://www.google.com/maps?q={{ $item->lokasi_masuk }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-mono text-[11px]" title="Buka di Google Maps">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                        <span>{{ Str::limit($item->lokasi_masuk, 18) }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 max-w-xs truncate">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada rekaman presensi ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada data presensi yang sesuai dengan kriteria filter pada tanggal {{ \Carbon\Carbon::parse($filterTanggal)->isoFormat('D MMMM Y') }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($presensi->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $presensi->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
