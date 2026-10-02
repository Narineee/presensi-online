@extends('layouts.admin')

@section('title', 'Monitoring Presensi')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Presensi Harian</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau rekaman absensi, titik koordinat GPS, dan bukti foto selfie Peserta Magang.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.presensi.cetak', request()->all()) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
                <span>Cetak Rekap Presensi</span>
            </a>
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
        <form action="{{ route('admin.presensi.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <!-- Filter Tanggal Awal -->
            <div>
                <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Awal</label>
                <input
                    type="date"
                    name="tanggal_mulai"
                    id="tanggal_mulai"
                    value="{{ request('tanggal_mulai') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Filter Tanggal Selesai -->
            <div>
                <label for="tanggal_akhir" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Selesai</label>
                <input
                    type="date"
                    name="tanggal_akhir"
                    id="tanggal_akhir"
                    value="{{ request('tanggal_akhir') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Mode Kerja -->
            <div>
                <label for="mode_kerja" class="block text-xs font-semibold text-slate-700 mb-1.5">Mode Kerja</label>
                <select name="mode_kerja" id="mode_kerja" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Mode</option>
                    <option value="onsite" {{ request('mode_kerja') === 'onsite' ? 'selected' : '' }}>Onsite</option>
                    <option value="wfh" {{ request('mode_kerja') === 'wfh' ? 'selected' : '' }}>WFH</option>
                </select>
            </div>

            <!-- Aksi Tombol -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_akhir', 'mode_kerja', 'tanggal']))
                    <a href="{{ route('admin.presensi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Presensi -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Peserta Magang</th>
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
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $item->pengguna->magang->divisi->nama_divisi ?? '@' . ($item->pengguna->username ?? '-') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar')
                                    <span class="inline-flex items-center gap-1 font-bold text-indigo-700">
                                        🚗 Tugas Luar
                                    </span>
                                @elseif($item->mode_kerja === 'onsite')
                                    <span class="inline-flex items-center gap-1 font-bold text-blue-700">
                                        🏢 Onsite
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-bold text-amber-700">
                                        🏠 WFH
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-900">
                                {{ $item->jam_masuk ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-900">
                                {{ $item->jam_keluar ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if($item->foto_masuk)
                                        <a href="{{ asset('storage/' . $item->foto_masuk) }}" target="_blank" class="p-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Lihat Foto Masuk">
                                            📸 Masuk
                                        </a>
                                    @endif
                                    @if($item->foto_keluar)
                                        <a href="{{ asset('storage/' . $item->foto_keluar) }}" target="_blank" class="p-1 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Lihat Foto Pulang">
                                            📸 Pulang
                                        </a>
                                    @endif
                                    @if(! $item->foto_masuk && ! $item->foto_keluar)
                                        <span class="text-slate-400">&mdash;</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate" title="{{ $item->lokasi_masuk }}">
                                <span class="font-mono text-slate-700">{{ $item->lokasi_masuk ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate" title="{{ $item->keterangan }}">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Belum ada data presensi yang sesuai dengan parameter filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensi->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $presensi->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
