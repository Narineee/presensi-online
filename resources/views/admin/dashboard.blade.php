@extends('layouts.admin')

@section('title', 'Dashboard Super Admin')

@section('content')
<div class="space-y-6">

    <!-- Top Section: Detail Akun Login & Ringkasan Peran (Sesuai PRD tampilan.md) -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-md border border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Sesi Aktif Administrator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ Auth::user()->username }}!
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Anda mengelola sistem presensi, monitoring aktivitas harian, perizinan, dan plotting penempatan peserta magang secara terpusat.
                </p>
            </div>

            <!-- Detail Akun yang Sedang Login Card (Sesuai PRD) -->
            <div class="shrink-0 bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/15 min-w-[280px]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-blue-300 mb-2">Detail Akun Login</div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Username:</span>
                        <span class="font-bold text-white font-mono">{{ Auth::user()->username }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Peran Sistem:</span>
                        <span class="font-bold uppercase px-2 py-0.5 rounded bg-blue-600 text-white text-[10px]">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Status Akun:</span>
                        <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Aktif &bull; Terverifikasi
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-white/10">
                        <span class="text-slate-400 text-[11px]">Hak Akses:</span>
                        <span class="text-blue-200 font-semibold text-[11px]">Super Admin Penuh</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Ringkasan Operasional Hari Ini -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Aktivitas &amp; Kehadiran Hari Ini ({{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }})
            </h2>
            <span class="text-[11px] text-slate-400 font-medium">Realtime sync</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Presensi Hari Ini -->
            <a href="{{ route('admin.presensi.index') }}" class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-blue-400 hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Presensi Masuk Hari Ini</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['presensi_today'] ?? 0 }} <span class="text-xs font-semibold text-slate-400">Data</span></p>
                    <span class="text-[11px] font-semibold text-blue-600 mt-1 inline-flex items-center gap-1">
                        Buka Monitoring Presensi &rarr;
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </a>

            <!-- Aktivitas Hari Ini -->
            <a href="{{ route('admin.aktivitas.index') }}" class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-emerald-400 hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Log Aktivitas Hari Ini</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['aktivitas_today'] ?? 0 }} <span class="text-xs font-semibold text-slate-400">Laporan</span></p>
                    <span class="text-[11px] font-semibold text-emerald-600 mt-1 inline-flex items-center gap-1">
                        Buka Monitoring Aktivitas &rarr;
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </a>

            <!-- Izin Menunggu -->
            <a href="{{ route('admin.izin.index') }}" class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-rose-400 hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Pengajuan Izin Pending</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['izin_pending'] ?? 0 }} <span class="text-xs font-semibold text-slate-400">Pengajuan</span></p>
                    <span class="text-[11px] font-semibold text-rose-600 mt-1 inline-flex items-center gap-1">
                        Buka Monitoring Izin &rarr;
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
            </a>
        </div>
    </div>

    <!-- Section: Ringkasan Jumlah Seluruh Data Master yang Dikelola (Sesuai PRD tampilan.md) -->
    <div>
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
            Ringkasan Master Data yang Dikelola
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- 1. Data Pembimbing -->
            <a href="{{ route('admin.pembimbing.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-purple-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pembimbing</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_pembimbing'] }}</p>
                <span class="text-[11px] text-purple-600 font-semibold mt-1 inline-block">Kelola &rarr;</span>
            </a>

            <!-- 2. Data Magang & Plotting -->
            <a href="{{ route('admin.magang.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-amber-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peserta Magang</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_magang'] }}</p>
                <span class="text-[11px] text-amber-600 font-semibold mt-1 inline-block">Plotting &rarr;</span>
            </a>

            <!-- 3. Data Divisi -->
            <a href="{{ route('admin.divisi.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-blue-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Divisi / Bagian</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_divisi'] }}</p>
                <span class="text-[11px] text-blue-600 font-semibold mt-1 inline-block">Kelola &rarr;</span>
            </a>

            <!-- 4. Data Kriteria Penilaian -->
            <a href="{{ route('admin.kriteria.index') }}" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:border-indigo-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kriteria Nilai</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_kriteria'] }}</p>
                <span class="text-[11px] text-indigo-600 font-semibold mt-1 inline-block">Kelola &rarr;</span>
            </a>

            <!-- 5. Total Seluruh Akun Login -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Akun</p>
                <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_pengguna'] }}</p>
                <span class="text-[11px] text-slate-400 font-medium mt-1 inline-block">Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Section: Akses Cepat Navigasi Fitur Sistem -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Manajemen & Plotting Cepat -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Aksi Tambah Data Cepat (Admin)</span>
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Admin menambahkan akun, menentukan divisi penempatan, pembimbing lapangan, dan periode pelaksanaan. Rincian profil lainnya dilengkapi mandiri oleh peserta magang.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <a href="{{ route('admin.magang.create') }}" class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 hover:bg-amber-100/60 transition flex items-center gap-3 text-xs font-bold text-amber-900">
                    <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base">+</span>
                    <span>Tambah Magang &amp; Plotting</span>
                </a>
                <a href="{{ route('admin.pembimbing.create') }}" class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-200/80 hover:bg-purple-100/60 transition flex items-center gap-3 text-xs font-bold text-purple-900">
                    <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base">+</span>
                    <span>Tambah Pembimbing</span>
                </a>
                <a href="{{ route('admin.divisi.create') }}" class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200/80 hover:bg-blue-100/60 transition flex items-center gap-3 text-xs font-bold text-blue-900">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base">+</span>
                    <span>Tambah Divisi Baru</span>
                </a>
            </div>
        </div>

        <!-- Rekapitulasi & Pengaturan Sistem -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>Cetak Rekapitulasi &amp; Konfigurasi TTD</span>
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Akses dokumen cetak rekap kehadiran &amp; aktivitas, serta atur nama pejabat pimpinan instansi untuk tanda tangan resmi.
            </p>
            <div class="space-y-2.5 pt-1">
                <a href="{{ route('admin.presensi.cetak') }}" target="_blank" class="p-3 rounded-2xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/40 transition flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">📄</span>
                        <span class="font-semibold text-slate-800">Cetak Rekapitulasi Presensi Seluruh Peserta</span>
                    </div>
                    <span class="text-blue-600 font-bold">&rarr;</span>
                </a>

                <a href="{{ route('admin.aktivitas.cetak') }}" target="_blank" class="p-3 rounded-2xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/40 transition flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">📋</span>
                        <span class="font-semibold text-slate-800">Cetak Rekapitulasi Log Aktivitas Harian</span>
                    </div>
                    <span class="text-emerald-600 font-bold">&rarr;</span>
                </a>

                <a href="{{ route('admin.pengaturan.index') }}" class="p-3 rounded-2xl border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-[11px]">⚙️</span>
                        <div>
                            <span class="font-bold text-slate-900 block">Pengaturan Pimpinan Instansi &amp; TTD</span>
                            <span class="text-[10px] text-slate-500">Nama Pimpinan, NIP, Jabatan &amp; Nama Instansi</span>
                        </div>
                    </div>
                    <span class="text-slate-600 font-bold">&rarr;</span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
