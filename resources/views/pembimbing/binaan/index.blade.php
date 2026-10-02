@extends('layouts.pembimbing')

@section('title', 'Daftar Binaan & Rekapitulasi Kehadiran')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Daftar Peserta Binaan</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau status kehadiran hari ini untuk binaan aktif dan rekapitulasi kumulatif seluruh hari kerja selama periode magang.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('pembimbing.presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition shadow-2xs">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Riwayat Presensi Harian</span>
            </a>
            <a href="{{ route('pembimbing.presensi.cetak') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-sm shadow-purple-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.32-2.22-.32-3.329 0-4.142 3.134-7.5 7-7.5s7 3.358 7 7.5c0 1.11-.08 2.252-.32 3.329m-13.36 0A9.034 9.034 0 003 18.5c0 1.381 4.03 2.5 9 2.5s9-1.119 9-2.5a9.034 9.034 0 00-3.64-4.671m-13.36 0a9.043 9.043 0 0113.36 0" />
                </svg>
                <span>Cetak Rekap Presensi</span>
            </a>
        </div>
    </div>

    <!-- BAGIAN ATAS: Metrik Presensi Khusus HARI INI (Per Hari) -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Status Presensi Hari Ini ({{ $summaryToday['today_label'] }})
                </h2>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">
                Khusus peserta binaan yang sedang aktif
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            <!-- 1. Total Binaan Aktif -->
            <div class="p-4 rounded-2xl bg-white border border-purple-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">Binaan Aktif</span>
                    <span class="text-base">👥</span>
                </div>
                <div class="text-2xl font-black text-purple-800 mt-1">
                    {{ $summaryToday['binaan_aktif'] }}
                    <span class="text-[11px] font-semibold text-purple-500">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5" title="{{ $summaryToday['binaan_selesai'] }} peserta telah berstatus selesai">
                    Total: {{ $summaryToday['total_binaan'] }} ({{ $summaryToday['binaan_selesai'] }} selesai)
                </p>
            </div>

            <!-- 2. Hadir Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Hadir Hari Ini</span>
                    <span class="text-base">✅</span>
                </div>
                <div class="text-2xl font-black text-emerald-600 mt-1">
                    {{ $summaryToday['hadir_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-emerald-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Tercatat presensi masuk</p>
            </div>

            <!-- 3. Sakit Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-rose-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600">Sakit Hari Ini</span>
                    <span class="text-base">🩺</span>
                </div>
                <div class="text-2xl font-black text-rose-600 mt-1">
                    {{ $summaryToday['sakit_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-rose-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Izin sakit dokter</p>
            </div>

            <!-- 4. Izin Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-blue-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600">Izin Hari Ini</span>
                    <span class="text-base">📋</span>
                </div>
                <div class="text-2xl font-black text-blue-600 mt-1">
                    {{ $summaryToday['izin_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-blue-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Izin keperluan resmi</p>
            </div>

            <!-- 5. Cuti Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-purple-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600">Cuti Hari Ini</span>
                    <span class="text-base">🌴</span>
                </div>
                <div class="text-2xl font-black text-purple-600 mt-1">
                    {{ $summaryToday['cuti_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-purple-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Hak cuti resmi</p>
            </div>

            <!-- 6. Tugas Luar Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600">Tugas Luar Hari Ini</span>
                    <span class="text-base">🚗</span>
                </div>
                <div class="text-2xl font-black text-amber-600 mt-1">
                    {{ $summaryToday['tugas_luar_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-amber-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Dinas lapangan</p>
            </div>

            <!-- 7. Alpa / Belum Absen Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-600">Alpa / Belum Absen</span>
                    <span class="text-base">⚠️</span>
                </div>
                <div class="text-2xl font-black text-slate-700 mt-1">
                    {{ $summaryToday['alpa_hari_ini'] }}
                    <span class="text-[11px] font-semibold text-slate-400">Orang</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Belum ada presensi</p>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
        <form action="{{ route('pembimbing.binaan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="sm:col-span-5">
                <label for="q" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Cari Nama / NIM / Asal Kampus
                </label>
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        id="q"
                        value="{{ request('q') }}"
                        placeholder="Ketik nama peserta atau NIM..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </div>

            <!-- Filter Divisi -->
            <div class="sm:col-span-3">
                <label for="divisi_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Divisi Penempatan
                </label>
                <select
                    name="divisi_id"
                    id="divisi_id"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 bg-white"
                >
                    <option value="">Semua Divisi</option>
                    @foreach($divisiList as $divisi)
                        <option value="{{ $divisi->id }}" {{ request('divisi_id') == $divisi->id ? 'selected' : '' }}>
                            {{ $divisi->nama_divisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Magang -->
            <div class="sm:col-span-2">
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Status Peserta
                </label>
                <select
                    name="status"
                    id="status"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 bg-white"
                >
                    <option value="">Semua Status (Aktif & Selesai)</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Hanya Aktif</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai (Riwayat)</option>
                </select>
            </div>

            <!-- Tombol Filter -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="w-full py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['q', 'divisi_id', 'status']))
                    <a
                        href="{{ route('pembimbing.binaan.index') }}"
                        class="p-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition"
                        title="Reset Filter"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Binaan & Penghitungan Keseluruhan (Bagian Bawah) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Daftar Peserta &amp; Rekapitulasi Presensi Lengkap
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Peserta dengan status selesai tetap tercantum agar riwayat presensinya dapat ditinjau kapan saja.
                    </p>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Menampilkan <strong class="text-slate-800">{{ $binaanList->count() }}</strong> peserta binaan
            </div>
        </div>

        @if($binaanList->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                    🎓
                </div>
                <h3 class="text-sm font-bold text-slate-700">Tidak ada peserta binaan ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    @if(request()->hasAny(['q', 'divisi_id', 'status']))
                        Coba sesuaikan kata kunci pencarian atau bersihkan filter yang diterapkan.
                    @else
                        Saat ini belum ada peserta magang yang dialokasikan ke dalam bimbingan Anda.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">Peserta Binaan</th>
                            <th class="py-3.5 px-3">Status Hari Ini</th>
                            <th class="py-3.5 px-3 text-center" title="Hanya hari kerja resmi (Senin-Jumat) di luar tanggal merah & libur nasional">
                                Total Hari Kerja Magang
                            </th>
                            <th class="py-3.5 px-3 text-center" title="Total kehadiran kumulatif sampai hari ini">Hadir</th>
                            <th class="py-3.5 px-3 text-center" title="Total sakit kumulatif">Sakit</th>
                            <th class="py-3.5 px-3 text-center" title="Total izin keperluan kumulatif">Izin</th>
                            <th class="py-3.5 px-3 text-center" title="Total cuti resmi kumulatif">Cuti</th>
                            <th class="py-3.5 px-3 text-center" title="Total tugas luar / dinas lapangan kumulatif">Tugas Luar</th>
                            <th class="py-3.5 px-3 text-center" title="Total alpa / tidak hadir kumulatif">Alpa</th>
                            <th class="py-3.5 px-4 text-center">Persentase &amp; Sisa</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($binaanList as $item)
                            @php
                                $r = $item->rekap;
                            @endphp
                            <tr class="hover:bg-purple-50/20 transition group {{ ! $item->is_binaan_aktif ? 'bg-slate-50/50' : '' }}">
                                <!-- Info Peserta -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        @if($item->foto)
                                            <img src="{{ asset('storage/'.$item->foto) }}" alt="{{ $item->nama_lengkap }}" class="w-10 h-10 rounded-xl object-cover border border-purple-200 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-xl {{ $item->is_binaan_aktif ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-600' }} font-extrabold flex items-center justify-center text-xs shrink-0 border border-purple-200">
                                                {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <div class="font-bold text-slate-900 text-xs truncate group-hover:text-purple-700 transition">
                                                    {{ $item->nama_lengkap }}
                                                </div>
                                                @if(! $item->is_binaan_aktif)
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-200 text-slate-600 shrink-0">
                                                        Selesai
                                                    </span>
                                                @else
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                                        Aktif
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                                NIM: {{ $item->no_induk }} &bull; {{ $item->divisi->nama_divisi ?? '-' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 truncate max-w-[190px]">
                                                {{ $item->instansi_pendidikan ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Presensi Hari Ini -->
                                <td class="py-4 px-3">
                                    @if($item->today_status === 'hadir')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                            @if($item->presensi_today && $item->presensi_today->jam_masuk)
                                                <span class="font-mono text-[9px]">({{ substr($item->presensi_today->jam_masuk, 0, 5) }})</span>
                                            @endif
                                        </span>
                                    @elseif($item->today_status === 'tugas_luar')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Tugas Luar
                                        </span>
                                    @elseif($item->today_status === 'sakit')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Sakit
                                        </span>
                                    @elseif($item->today_status === 'izin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Izin
                                        </span>
                                    @elseif($item->today_status === 'cuti')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Cuti
                                        </span>
                                    @elseif($item->today_status === 'weekend')
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">
                                            Akhir Pekan
                                        </span>
                                    @elseif($item->today_status === 'libur')
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-600">
                                            Hari Libur
                                        </span>
                                    @elseif($item->today_status === 'alpa')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Alpa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Belum Absen
                                        </span>
                                    @endif
                                </td>

                                <!-- Total Hari Magang (Hanya Hari Kerja) -->
                                <td class="py-4 px-3 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 font-black text-xs border border-purple-200">
                                            {{ $r['total_hari_magang'] }} Hari Kerja
                                        </span>
                                        <span class="text-[10px] text-slate-400 mt-1 font-mono">
                                            {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/y') : '-' }} &ndash; {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/y') : '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Hadir (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 font-black text-xs border border-emerald-200">
                                        {{ $r['total_hadir'] }}
                                    </span>
                                </td>

                                <!-- Sakit (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[30px] px-2 py-1 rounded-xl {{ $r['total_sakit'] > 0 ? 'bg-rose-50 text-rose-700 font-bold border border-rose-200' : 'text-slate-400' }}">
                                        {{ $r['total_sakit'] }}
                                    </span>
                                </td>

                                <!-- Izin (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[30px] px-2 py-1 rounded-xl {{ $r['total_izin'] > 0 ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200' : 'text-slate-400' }}">
                                        {{ $r['total_izin'] }}
                                    </span>
                                </td>

                                <!-- Cuti (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[30px] px-2 py-1 rounded-xl {{ $r['total_cuti'] > 0 ? 'bg-purple-50 text-purple-700 font-bold border border-purple-200' : 'text-slate-400' }}">
                                        {{ $r['total_cuti'] }}
                                    </span>
                                </td>

                                <!-- Tugas Luar (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[30px] px-2 py-1 rounded-xl {{ $r['total_tugas_luar'] > 0 ? 'bg-amber-50 text-amber-700 font-bold border border-amber-200' : 'text-slate-400' }}">
                                        {{ $r['total_tugas_luar'] }}
                                    </span>
                                </td>

                                <!-- Alpa / Tidak Hadir (Kumulatif) -->
                                <td class="py-4 px-3 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-xl {{ $r['total_alpa'] > 0 ? 'bg-rose-100 text-rose-800 font-black border border-rose-300' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                        {{ $r['total_alpa'] }}
                                    </span>
                                </td>

                                <!-- Persentase & Sisa Hari -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-extrabold text-xs {{ $r['persentase_kehadiran'] >= 80 ? 'text-emerald-600' : ($r['persentase_kehadiran'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                                {{ number_format($r['persentase_kehadiran'], 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-24 bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1">
                                            <div
                                                class="h-full rounded-full {{ $r['persentase_kehadiran'] >= 80 ? 'bg-emerald-500' : ($r['persentase_kehadiran'] >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                style="width: {{ min(100, $r['persentase_kehadiran']) }}%"
                                            ></div>
                                        </div>
                                        <span class="text-[10px] text-slate-400 mt-1">
                                            {{ $r['sisa_hari_kerja'] }} hari tersisa
                                        </span>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-4 text-right">
                                    <a
                                        href="{{ route('pembimbing.binaan.show', $item->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white border border-purple-200 text-xs font-bold transition shadow-2xs group-hover:border-purple-300"
                                    >
                                        <span>Detail Rekap</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- BAGIAN BAWAH: BARIS TOTAL KESELURUHAN (KUMULATIF MASA MAGANG) -->
                    <tfoot class="bg-slate-100/90 font-black text-slate-800 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="2" class="py-4 px-4 text-xs uppercase tracking-wider text-slate-700">
                                <div class="flex items-center gap-2">
                                    <span>📊</span>
                                    <span>Total Keseluruhan (Akumulasi Masa Magang)</span>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-800 text-xs">
                                    {{ $summaryKumulatif['total_hari_magang'] }} Hari
                                </span>
                            </td>
                            <td class="py-4 px-3 text-center text-emerald-700 text-xs">
                                {{ $summaryKumulatif['total_hadir'] }}
                            </td>
                            <td class="py-4 px-3 text-center text-rose-700 text-xs">
                                {{ $summaryKumulatif['total_sakit'] }}
                            </td>
                            <td class="py-4 px-3 text-center text-blue-700 text-xs">
                                {{ $summaryKumulatif['total_izin'] }}
                            </td>
                            <td class="py-4 px-3 text-center text-purple-700 text-xs">
                                {{ $summaryKumulatif['total_cuti'] }}
                            </td>
                            <td class="py-4 px-3 text-center text-amber-700 text-xs">
                                {{ $summaryKumulatif['total_tugas_luar'] }}
                            </td>
                            <td class="py-4 px-3 text-center text-rose-700 text-xs">
                                {{ $summaryKumulatif['total_alpa'] }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="text-xs {{ $summaryKumulatif['rata_rata_kehadiran'] >= 80 ? 'text-emerald-700' : 'text-amber-700' }}">
                                    Rata-rata {{ number_format($summaryKumulatif['rata_rata_kehadiran'], 1) }}%
                                </span>
                            </td>
                            <td class="py-4 px-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Kartu Penjelasan Penghitungan di Bagian Bawah -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="text-purple-600 font-bold">ℹ️ Keterangan:</span>
                    <span>
                        <strong>Bagian Atas:</strong> Memantau status presensi per hari ini untuk binaan aktif. <br class="hidden sm:inline">
                        <strong>Bagian Bawah (Tabel &amp; Footer):</strong> Penghitungan kumulatif seluruh masa magang (total hari kerja efektif, hadir, sakit, izin, cuti, tugas luar, dan alpa).
                    </span>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
