@extends('layouts.admin')

@section('title', 'Detail Peserta - ' . $magang->nama_lengkap)

@section('content')
<div class="space-y-6">

    <!-- Flash Message Notification -->
    @if (session('success'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>Terjadi kesalahan pada input:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if ($magang->foto)
                    <img src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-slate-100 shadow-xs">
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-extrabold text-2xl shadow-xs">
                        {{ strtoupper(substr($magang->nama_lengkap, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $magang->nama_lengkap }}</h1>
                        @if ($magang->jenis_kelamin === 'L')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Laki-laki</span>
                        @elseif ($magang->jenis_kelamin === 'P')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-pink-50 text-pink-700 border border-pink-200">Perempuan</span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        {{ $magang->instansi_pendidikan ?? 'Instansi Belum Diatur' }}
                        @if ($magang->jurusan) &bull; {{ $magang->jurusan }} @endif
                    </p>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                        <!-- Status Magang -->
                        @if ($magang->status === 'aktif')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Magang Aktif
                            </span>
                        @elseif ($magang->status === 'selesai')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Cuti
                            </span>
                        @endif

                        <!-- Divisi Aktif Berjalan -->
                        @php
                            $penempatanAktif = $magang->penempatanAktif;
                            $divisiAktif = $penempatanAktif ? $penempatanAktif->divisi : $magang->divisi;
                        @endphp
                        @if ($divisiAktif)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Divisi Aktif: <strong>{{ $divisiAktif->nama_divisi }}</strong></span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.magang.edit', $magang->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    <span>Edit Profil</span>
                </a>
                <a href="{{ route('admin.magang.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Kembali ke Data Magang</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs (Sesuai PRD Section 2) -->
        <div class="mt-8 border-b border-slate-200">
            <nav class="flex space-x-1 sm:space-x-3 overflow-x-auto pb-px" aria-label="Tabs">
                <!-- Tab Penempatan Divisi (Fokus Utama) -->
                <button
                    type="button"
                    onclick="switchTab('penempatan')"
                    id="tab-btn-penempatan"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-bold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span>Penempatan Divisi</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                        {{ $magang->penempatanMagang->count() }}
                    </span>
                </button>

                <!-- Tab Informasi Peserta -->
                <button
                    type="button"
                    onclick="switchTab('informasi')"
                    id="tab-btn-informasi"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span>Informasi Peserta</span>
                </button>

                <!-- Tab Periode Magang -->
                <button
                    type="button"
                    onclick="switchTab('periode')"
                    id="tab-btn-periode"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span>Periode Magang</span>
                </button>

                <!-- Tab Pembimbing -->
                <button
                    type="button"
                    onclick="switchTab('pembimbing')"
                    id="tab-btn-pembimbing"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    <span>Pembimbing</span>
                </button>

                <!-- Tab Presensi -->
                <button
                    type="button"
                    onclick="switchTab('presensi')"
                    id="tab-btn-presensi"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Presensi</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $presensiStats['total'] }}
                    </span>
                </button>

                <!-- Tab Aktivitas -->
                <button
                    type="button"
                    onclick="switchTab('aktivitas')"
                    id="tab-btn-aktivitas"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <span>Aktivitas</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $aktivitasStats['total'] }}
                    </span>
                </button>

                <!-- Tab Penilaian -->
                <button
                    type="button"
                    onclick="switchTab('penilaian')"
                    id="tab-btn-penilaian"
                    class="tab-btn inline-flex items-center gap-2 py-3 px-3.5 border-b-2 font-semibold text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <span>Penilaian</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: PENEMPATAN DIVISI (FOKUS UTAMA)     -->
    <!-- ========================================== -->
    <div id="tab-content-penempatan" class="tab-pane space-y-6">

        <!-- Card Section: Penempatan Divisi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Riwayat Penempatan Divisi</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $magang->penempatanMagang->count() }} Penempatan
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Satu peserta hanya memiliki 1 periode magang, namun dapat ditempatkan pada berbagai divisi secara berkesinambungan.
                    </p>
                </div>

                <!-- Tombol Tambah Penempatan -->
                <button
                    type="button"
                    onclick="openTambahModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>+ Tambah Penempatan</span>
                </button>
            </div>

            <!-- Banner Penempatan Sedang Berjalan -->
            @if ($penempatanAktif)
                <div class="mt-6 p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 flex items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Sedang Berjalan Saat Ini</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Aktif
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 mt-0.5">{{ $penempatanAktif->divisi->nama_divisi }}</h3>
                            <p class="text-xs text-slate-600">
                                Periode: <strong>{{ $penempatanAktif->tanggal_mulai->format('d M Y') }}</strong> s/d <strong>{{ $penempatanAktif->tanggal_selesai->format('d M Y') }}</strong>
                                @if ($penempatanAktif->divisi->nama_pimpinan)
                                    &bull; Pimpinan: {{ $penempatanAktif->divisi->nama_pimpinan }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabel Riwayat Penempatan Divisi (Sesuai PRD Section 3) -->
            <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Divisi</th>
                            <th class="py-3 px-4 text-center">Tanggal Mulai</th>
                            <th class="py-3 px-4 text-center">Tanggal Selesai</th>
                            <th class="py-3 px-4 text-center">Durasi</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($magang->penempatanMagang as $item)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-sm">
                                        {{ $item->divisi->nama_divisi }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Pimpinan: {{ $item->divisi->nama_pimpinan ?? '-' }}
                                        @if ($item->divisi->jabatan_pimpinan)
                                            ({{ $item->divisi->jabatan_pimpinan }})
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap font-medium text-slate-700">
                                    {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap font-medium text-slate-700">
                                    {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap text-slate-500 text-xs">
                                    @php
                                        $hari = $item->tanggal_mulai && $item->tanggal_selesai ? $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1 : 0;
                                    @endphp
                                    <span class="font-medium text-slate-700">{{ $hari }} Hari</span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if ($item->status === 'Berjalan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Berjalan
                                        </span>
                                    @elseif ($item->status === 'Akan Datang')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Akan Datang
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Button -->
                                        <button
                                            type="button"
                                            onclick='openEditModal(@json($item))'
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition cursor-pointer"
                                            title="Edit Penempatan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Hapus Button -->
                                        <form
                                            action="{{ route('admin.magang.penempatan.destroy', ['magang' => $magang->id, 'penempatan' => $item->id]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat penempatan di {{ $item->divisi->nama_divisi }} ini?');"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition cursor-pointer"
                                                title="Hapus Penempatan"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="max-w-sm mx-auto text-center">
                                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800">Belum ada penempatan divisi untuk peserta ini.</p>
                                        <p class="text-xs text-slate-400 mt-1">Tambahkan penempatan divisi pertama untuk memulai rotasi penempatan kerja peserta.</p>
                                        <button
                                            type="button"
                                            onclick="openTambahModal()"
                                            class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <span>Tambah Penempatan Divisi</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Catatan Edukatif PRD -->
            <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div class="space-y-1">
                    <p class="font-semibold text-slate-800">Ketentuan Riwayat Penempatan Divisi:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-500">
                        <li>Periode utama magang peserta: <strong>{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d M Y') : '-' }}</strong> s/d <strong>{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d M Y') : '-' }}</strong>.</li>
                        <li>Tanggal penempatan divisi wajib berada di dalam batas periode magang peserta dan tidak boleh saling bertabrakan (overlap).</li>
                        <li>Presensi harian dan lembar cetak laporan otomatis membaca divisi penempatan sesuai tanggal pelaksanaan aktivitas atau presensi peserta.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 2: INFORMASI PESERTA                   -->
    <!-- ========================================== -->
    <div id="tab-content-informasi" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight pb-4 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Profil dan Akun Peserta</span>
            </h2>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 text-base">{{ $magang->nama_lengkap }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Nomor Induk / NIM</span>
                        <span class="font-medium text-slate-700">{{ $magang->no_induk ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Jenis Kelamin</span>
                        <span class="font-medium text-slate-700">{{ $magang->jenis_kelamin_teks ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Nomor Telepon / WhatsApp</span>
                        <span class="font-medium text-slate-700">{{ $magang->no_hp ?? '-' }}</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Instansi Pendidikan / Kampus</span>
                        <span class="font-bold text-slate-800">{{ $magang->instansi_pendidikan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Program Studi / Jurusan</span>
                        <span class="font-medium text-slate-700">{{ $magang->jurusan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Username Akun Login</span>
                        <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $magang->pengguna->username ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block uppercase">Status Verifikasi Wajah</span>
                        @if ($magang->face_registered_at)
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Terdaftar ({{ $magang->face_registered_at->format('d/m/Y H:i') }})
                                </span>
                                <a href="{{ route('admin.magang.wajah.foto', $magang->id) }}" target="_blank" class="text-xs text-blue-600 hover:underline">Lihat Foto</a>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 mt-1">
                                Belum Merekam Wajah
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: PERIODE MAGANG                      -->
    <!-- ========================================== -->
    <div id="tab-content-periode" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight pb-4 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span>Periode Magang Utama</span>
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Tanggal Mulai</span>
                    <span class="font-bold text-slate-900 text-base mt-1 block">
                        {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Tanggal Selesai</span>
                    <span class="font-bold text-slate-900 text-base mt-1 block">
                        {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Total Durasi</span>
                    @php
                        $durasiBulan = $magang->tanggal_mulai && $magang->tanggal_selesai ? $magang->tanggal_mulai->diffInMonths($magang->tanggal_selesai) : 0;
                        $durasiHari = $magang->tanggal_mulai && $magang->tanggal_selesai ? $magang->tanggal_mulai->diffInDays($magang->tanggal_selesai) + 1 : 0;
                    @endphp
                    <span class="font-bold text-blue-600 text-base mt-1 block">
                        {{ $durasiHari }} Hari (~{{ $durasiBulan }} Bulan)
                    </span>
                </div>
            </div>

            <div class="mt-6 p-4 rounded-xl bg-blue-50/50 border border-blue-200 text-xs text-blue-800">
                <strong>Catatan Konsep:</strong> Periode magang utama adalah masa berlaku kontrak atau izin magang peserta secara keseluruhan. Riwayat penempatan divisi peserta bergerak di dalam rentang waktu periode utama ini.
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: PEMBIMBING                          -->
    <!-- ========================================== -->
    <div id="tab-content-pembimbing" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight pb-4 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <span>Pembimbing Lapangan</span>
            </h2>

            @if ($magang->pembimbing)
                <div class="mt-6 flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-700 text-white flex items-center justify-center font-bold text-xl shadow-xs shrink-0">
                        {{ strtoupper(substr($magang->pembimbing->nama_lengkap, 0, 1)) }}
                    </div>
                    <div class="space-y-1.5 text-xs sm:text-sm">
                        <h3 class="font-bold text-slate-900 text-base">{{ $magang->pembimbing->nama_lengkap }}</h3>
                        <p class="text-slate-500">NIP: <span class="font-mono font-medium text-slate-700">{{ $magang->pembimbing->nip ?? '-' }}</span></p>
                        <p class="text-slate-500">Jabatan: <span class="font-medium text-slate-700">{{ $magang->pembimbing->jabatan ?? 'Pembimbing Lapangan' }}</span></p>
                        <p class="text-slate-500">Kontak: <span class="font-medium text-slate-700">{{ $magang->pembimbing->no_hp ?? '-' }}</span></p>
                    </div>
                </div>
            @else
                <div class="mt-6 p-8 text-center text-slate-400">
                    Belum ada pembimbing lapangan yang ditetapkan untuk peserta ini.
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 5: PRESENSI                            -->
    <!-- ========================================== -->
    <div id="tab-content-presensi" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Presensi Kehadiran Peserta</span>
                </h2>
                <a href="{{ route('admin.presensi.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    Lihat Semua di Monitoring Presensi &rarr;
                </a>
            </div>

            <!-- Stats Presensi -->
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                    <span class="text-2xl font-bold text-slate-900">{{ $presensiStats['total'] }}</span>
                    <span class="text-xs text-slate-500 block mt-1">Total Presensi</span>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                    <span class="text-2xl font-bold text-emerald-700">{{ $presensiStats['hadir'] }}</span>
                    <span class="text-xs text-emerald-600 block mt-1">Tepat Waktu</span>
                </div>
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center">
                    <span class="text-2xl font-bold text-amber-700">{{ $presensiStats['terlambat'] }}</span>
                    <span class="text-xs text-amber-600 block mt-1">Terlambat</span>
                </div>
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center">
                    <span class="text-2xl font-bold text-blue-700">{{ $presensiStats['izin'] }}</span>
                    <span class="text-xs text-blue-600 block mt-1">Izin / Sakit</span>
                </div>
            </div>

            <!-- 10 Presensi Terakhir -->
            <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Divisi Pada Tanggal Tersebut</th>
                            <th class="py-3 px-4 text-center">Mode</th>
                            <th class="py-3 px-4 text-center">Masuk</th>
                            <th class="py-3 px-4 text-center">Pulang</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($presensiList as $p)
                            @php
                                $divisiTanggal = $magang->getDivisiAt($p->tanggal);
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-slate-700 font-medium">{{ $divisiTanggal?->nama_divisi ?? '-' }}</td>
                                <td class="py-3 px-4 text-center uppercase font-bold text-[10px] text-slate-600">{{ $p->mode_kerja }}</td>
                                <td class="py-3 px-4 text-center font-mono">{{ $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : '-' }}</td>
                                <td class="py-3 px-4 text-center font-mono">{{ $p->jam_keluar ? substr($p->jam_keluar, 0, 5) : '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'terlambat' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-slate-400">Belum ada data presensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 6: AKTIVITAS                           -->
    <!-- ========================================== -->
    <div id="tab-content-aktivitas" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <span>Log Aktivitas Harian Peserta</span>
                </h2>
                <a href="{{ route('admin.aktivitas.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    Lihat Semua di Monitoring Aktivitas &rarr;
                </a>
            </div>

            <!-- Stats Aktivitas -->
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                    <span class="text-2xl font-bold text-slate-900">{{ $aktivitasStats['total'] }}</span>
                    <span class="text-xs text-slate-500 block mt-1">Total Aktivitas</span>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                    <span class="text-2xl font-bold text-emerald-700">{{ $aktivitasStats['approve'] }}</span>
                    <span class="text-xs text-emerald-600 block mt-1">Disetujui</span>
                </div>
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-center">
                    <span class="text-2xl font-bold text-amber-700">{{ $aktivitasStats['pending'] }}</span>
                    <span class="text-xs text-amber-600 block mt-1">Pending</span>
                </div>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-center">
                    <span class="text-2xl font-bold text-rose-700">{{ $aktivitasStats['revisi'] }}</span>
                    <span class="text-xs text-rose-600 block mt-1">Revisi</span>
                </div>
            </div>

            <!-- 10 Aktivitas Terakhir -->
            <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Divisi Terkait</th>
                            <th class="py-3 px-4">Tugas / Pekerjaan</th>
                            <th class="py-3 px-4">Uraian Ringkas</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($aktivitasList as $a)
                            @php
                                $divisiAktivitas = $magang->getDivisiAt($a->tanggal);
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-slate-700 font-medium whitespace-nowrap">{{ $divisiAktivitas?->nama_divisi ?? '-' }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $a->pekerjaan?->judul ?? 'Pekerjaan Umum' }}</td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs truncate">{{ $a->isi }}</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $a->status === 'approve' ? 'bg-emerald-100 text-emerald-800' : ($a->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ ucfirst($a->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-slate-400">Belum ada catatan aktivitas harian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 7: PENILAIAN                           -->
    <!-- ========================================== -->
    <div id="tab-content-penilaian" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight pb-4 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                <span>Lembar Penilaian Akhir Magang</span>
            </h2>

            @if ($magang->penilaian)
                <div class="mt-6 p-6 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="space-y-1 text-center sm:text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full inline-block mb-1">Sudah Dinilai</span>
                        <h3 class="text-xl font-bold text-slate-900">Total Nilai: {{ $magang->penilaian->nilai_akhir ?? '-' }}</h3>
                        <p class="text-xs text-slate-500">
                            Predikat: <strong class="text-slate-800">{{ $magang->penilaian->grade ?? '-' }}</strong> &bull;
                            Dinilai pada: {{ $magang->penilaian->created_at->format('d M Y') }}
                        </p>
                    </div>
                    <a
                        href="{{ route('admin.penilaian.show', $magang->penilaian->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.42-2.18-.54-3.298M16.5 12c0 2.25-1.5 4.5-4.5 4.5S7.5 14.25 7.5 12 9 7.5 12 7.5s4.5 2.25 4.5 4.5zm0 0c.937 0 1.83.18 2.648.513" />
                        </svg>
                        <span>Lihat &amp; Cetak Lembar Penilaian</span>
                    </a>
                </div>
            @else
                <div class="mt-6 p-8 text-center text-slate-400">
                    <p class="font-semibold text-slate-600">Peserta belum mendapatkan penilaian akhir.</p>
                    <p class="text-xs text-slate-400 mt-1">Penilaian diisi oleh Pembimbing Lapangan menjelang berakhirnya masa magang.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL: TAMBAH PENEMPATAN DIVISI (Sesuai PRD Section 4)          -->
<!-- ============================================================== -->
<div id="modal-tambah-penempatan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-bold text-slate-900">Tambah Penempatan Divisi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan divisi penempatan kerja untuk {{ $magang->nama_lengkap }}</p>
            </div>
            <button type="button" onclick="closeTambahModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form action="{{ route('admin.magang.penempatan.store', $magang->id) }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Peserta Magang (Otomatis & Readonly) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Peserta Magang</label>
                <input
                    type="text"
                    value="{{ $magang->nama_lengkap }} ({{ $magang->no_induk ?? 'Tanpa NIM' }})"
                    readonly
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-600 text-xs font-semibold cursor-not-allowed"
                >
            </div>

            <!-- Divisi Dropdown -->
            <div>
                <label for="tambah_divisi_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Divisi Penempatan <span class="text-rose-500">*</span>
                </label>
                <select
                    name="divisi_id"
                    id="tambah_divisi_id"
                    required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800 bg-white"
                >
                    <option value="">-- Pilih Divisi --</option>
                    @foreach ($divisiList as $div)
                        <option value="{{ $div->id }}">{{ $div->nama_divisi }} (Pimpinan: {{ $div->nama_pimpinan ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Grid Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tambah_tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tambah_tanggal_mulai"
                        min="{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('Y-m-d') : '' }}"
                        max="{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('Y-m-d') : '' }}"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800"
                    >
                </div>
                <div>
                    <label for="tambah_tanggal_selesai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="tambah_tanggal_selesai"
                        min="{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('Y-m-d') : '' }}"
                        max="{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('Y-m-d') : '' }}"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800"
                    >
                </div>
            </div>

            <!-- Hint Periode Magang -->
            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 text-[11px] text-blue-700">
                <strong>Batas Periode Magang:</strong>
                {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d/m/Y') : '-' }}.
                Rentang tidak boleh bertabrakan dengan penempatan yang sudah ada.
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button
                    type="button"
                    onclick="closeTambahModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs"
                >
                    Simpan Penempatan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: EDIT PENEMPATAN DIVISI                                  -->
<!-- ============================================================== -->
<div id="modal-edit-penempatan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Penempatan Divisi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui divisi atau rentang waktu penempatan</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="form-edit-penempatan" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <!-- Divisi Dropdown -->
            <div>
                <label for="edit_divisi_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Divisi Penempatan <span class="text-rose-500">*</span>
                </label>
                <select
                    name="divisi_id"
                    id="edit_divisi_id"
                    required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800 bg-white"
                >
                    <option value="">-- Pilih Divisi --</option>
                    @foreach ($divisiList as $div)
                        <option value="{{ $div->id }}">{{ $div->nama_divisi }} (Pimpinan: {{ $div->nama_pimpinan ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Grid Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="edit_tanggal_mulai"
                        min="{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('Y-m-d') : '' }}"
                        max="{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('Y-m-d') : '' }}"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800"
                    >
                </div>
                <div>
                    <label for="edit_tanggal_selesai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="edit_tanggal_selesai"
                        min="{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('Y-m-d') : '' }}"
                        max="{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('Y-m-d') : '' }}"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs font-medium text-slate-800"
                    >
                </div>
            </div>

            <!-- Hint Periode Magang -->
            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 text-[11px] text-blue-700">
                <strong>Batas Periode Magang:</strong>
                {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d/m/Y') : '-' }}.
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tab switching functionality
    function switchTab(tabName) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
        });

        const activePane = document.getElementById('tab-content-' + tabName);
        const activeBtn = document.getElementById('tab-btn-' + tabName);

        if (activePane) activePane.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.add('border-blue-600', 'text-blue-600');
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
        }

        // Simpan tab di URL parameter tanpa reload halaman
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    // Modal Tambah
    function openTambahModal() {
        document.getElementById('modal-tambah-penempatan').classList.remove('hidden');
    }
    function closeTambahModal() {
        document.getElementById('modal-tambah-penempatan').classList.add('hidden');
    }

    // Modal Edit
    function openEditModal(item) {
        const modal = document.getElementById('modal-edit-penempatan');
        const form = document.getElementById('form-edit-penempatan');

        // Set action form
        form.action = `/admin/magang/{{ $magang->id }}/penempatan/${item.id}`;

        // Set values
        document.getElementById('edit_divisi_id').value = item.divisi_id;
        document.getElementById('edit_tanggal_mulai').value = item.tanggal_mulai ? item.tanggal_mulai.substring(0, 10) : '';
        document.getElementById('edit_tanggal_selesai').value = item.tanggal_selesai ? item.tanggal_selesai.substring(0, 10) : '';

        modal.classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modal-edit-penempatan').classList.add('hidden');
    }

    // Inisialisasi tab saat load
    document.addEventListener('DOMContentLoaded', function() {
        const initialTab = '{{ $activeTab ?? "penempatan" }}';
        switchTab(initialTab);
    });
</script>
@endpush
@endsection
