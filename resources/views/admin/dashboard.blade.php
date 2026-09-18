@extends('layouts.admin')

@section('title', 'Dashboard Super Admin')

@section('content')
<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 rounded-2xl p-6 sm:p-8 text-white shadow-xl shadow-blue-500/10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Tahap 1: Autentikasi Siap</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
                Selamat Datang, {{ Auth::user()->username }}!
            </h1>
            <p class="text-blue-100 text-sm mt-1.5 max-w-2xl leading-relaxed">
                Anda berhasil masuk ke sesi sistem sebagai <strong class="text-white">Super Admin</strong>. Akun ini beroperasi secara mandiri tanpa terikat profil master data, siap digunakan untuk mendaftarkan master data dan akun pengguna lainnya.
            </p>
        </div>

        <div class="shrink-0 flex items-center gap-2">
            <div class="px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 text-center">
                <div class="text-[11px] uppercase tracking-wider text-blue-200 font-semibold">Role Akun</div>
                <div class="text-base font-bold text-white uppercase">{{ Auth::user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview Grid -->
<div>
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">Status Data Saat Ini</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Card 1: Total Pengguna -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Akun</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_pengguna'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-emerald-600 mt-1">
                    Aktif (Admin)
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Divisi (Aktif) -->
        <a href="{{ route('admin.divisi.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-blue-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Data Divisi</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_divisi'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-blue-600 mt-1">
                    Kelola Divisi &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
        </a>

        <!-- Card 3: Pembimbing (Aktif) -->
        <a href="{{ route('admin.pembimbing.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-purple-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pembimbing</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_pembimbing'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-purple-600 mt-1">
                    Kelola Pembimbing &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
        </a>

        <!-- Card 4: Magang (Aktif) -->
        <a href="{{ route('admin.magang.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-amber-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Magang</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_magang'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-amber-600 mt-1">
                    Kelola Magang &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                </svg>
            </div>
        </a>

        <!-- Card 5: CS (Aktif) -->
        <a href="{{ route('admin.cs.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-teal-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">CS</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_cs'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-teal-600 mt-1">
                    Kelola CS &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
            </div>
        </a>

        <!-- Card 6: Kriteria Penilaian (Aktif) -->
        <a href="{{ route('admin.kriteria.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-indigo-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kriteria</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_kriteria'] }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-indigo-600 mt-1">
                    Kelola Kriteria &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
            </div>
        </a>
    </div>
</div>

<!-- Architecture & Next Steps Plan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Next Steps Checklist -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Rencana Implementasi Sesuai PRD</span>
            </h3>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">Tahap 1 Selesai</span>
        </div>

        <div class="space-y-3">
            <!-- Step 1 (Done) -->
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                    &check;
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-900">Langkah 1: Menu Login & Akun Default Super Admin</h4>
                    <p class="text-xs text-emerald-800 mt-1">
                        Sistem login mandiri untuk Super Admin telah berjalan tanpa ketergantungan profil master data. Proteksi brute-force (rate limiting) dan validasi telah aktif.
                    </p>
                </div>
            </div>

            <!-- Step 2 (Upcoming) -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                    2
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Langkah 2: Master Data & Pembuatan Akun oleh Admin</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        Admin akan menginput master data: Divisi, Pembimbing, Magang, serta CS & Shift. Pada saat penambahan data Pembimbing/Magang/CS, sistem sekaligus men-generate akun pengguna (username & password) terkait.
                    </p>
                </div>
            </div>

            <!-- Step 3 (Upcoming) -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                    3
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Langkah 3: Modul Operasional (Presensi, Izin, & Aktivitas)</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        Presensi onsite/WFH, geolokasi, upload berkas izin sakit/cuti, serta pelaporan aktivitas harian untuk Magang & CS beserta validasi oleh Pembimbing.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
        <h3 class="font-bold text-slate-900 text-base">Detail Akun Sedang Login</h3>
        <div class="divide-y divide-slate-100 text-xs">
            <div class="py-2.5 flex justify-between">
                <span class="text-slate-400">Username:</span>
                <span class="font-bold text-slate-800">{{ Auth::user()->username }}</span>
            </div>
            <div class="py-2.5 flex justify-between">
                <span class="text-slate-400">Hak Akses (Role):</span>
                <span class="font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 capitalize">{{ Auth::user()->role }}</span>
            </div>
            <div class="py-2.5 flex justify-between">
                <span class="text-slate-400">Status Akun:</span>
                <span class="font-bold text-emerald-600">Aktif</span>
            </div>
            <div class="py-2.5 flex justify-between">
                <span class="text-slate-400">Tipe Entitas:</span>
                <span class="font-medium text-slate-600">Super Admin (Stand-alone)</span>
            </div>
            <div class="py-2.5 flex justify-between">
                <span class="text-slate-400">Autentikasi:</span>
                <span class="font-medium text-slate-600">Session Guard</span>
            </div>
        </div>

        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 leading-relaxed">
            <span class="font-bold">Konsep Sesuai PRD:</span><br>
            Tidak ada pendaftaran mandiri (self-register). Akun pengguna lain hanya dapat dibuat oleh Admin melalui panel ini.
        </div>
    </div>
</div>
@endsection
