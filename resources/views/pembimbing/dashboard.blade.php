@extends('layouts.pembimbing')

@section('title', 'Dashboard Pembimbing')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-purple-500/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-purple-100 text-xs font-semibold backdrop-blur-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Portal Pembimbing & Validator Lapangan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->username }}!
                </h1>
                <p class="text-purple-100 text-sm mt-1 max-w-xl leading-relaxed">
                    Pantau dan validasi aktivitas harian, presensi, serta evaluasi penilaian akhir anak magang dan Customer Service binaan Anda.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <div class="px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
                    <div class="text-[11px] uppercase tracking-wider text-purple-200 font-semibold">NIP Pembimbing</div>
                    <div class="text-base font-bold text-white font-mono">{{ Auth::user()->pembimbing->nip ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    @php
        $pembimbing = Auth::user()->pembimbing;
        $magangCount = $pembimbing ? $pembimbing->magang()->count() : 0;
        $csCount = $pembimbing ? $pembimbing->cs()->count() : 0;
        $magangUserIds = $pembimbing ? $pembimbing->magang()->pluck('pengguna_id') : collect();
        $csUserIds = $pembimbing ? $pembimbing->cs()->pluck('pengguna_id') : collect();
        $allIds = $magangUserIds->merge($csUserIds);
        $pendingAktivitas = \App\Models\Aktivitas::whereIn('pengguna_id', $allIds)->where('status', 'pending')->count();
        $pendingIzin = \App\Models\PengajuanIzin::whereIn('pengguna_id', $allIds)->where('status_approval', 'pending')->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Aktivitas Pending -->
        <a href="{{ route('pembimbing.aktivitas.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-purple-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aktivitas Menunggu</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingAktivitas }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-purple-600 mt-1">
                    Validasi Sekarang &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⏳
            </div>
        </a>

        <!-- Card 2: Izin & Sakit Pending -->
        <a href="{{ route('pembimbing.izin.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-purple-300 hover:shadow-md transition">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Izin Menunggu</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $pendingIzin }}</p>
                <span class="inline-flex items-center text-[11px] font-medium text-purple-600 mt-1">
                    Verifikasi Izin &rarr;
                </span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                🩺
            </div>
        </a>

        <!-- Card 3: Anak Magang Binaan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Anak Magang</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $magangCount }} Orang</p>
                <span class="text-[11px] text-slate-400">Peserta aktif</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                🎓
            </div>
        </div>

        <!-- Card 4: CS Binaan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Customer Service</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $csCount }} Orang</p>
                <span class="text-[11px] text-slate-400">Petugas CS</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                🎧
            </div>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-4">
        <h2 class="text-base font-bold text-slate-900">Tugas & Tanggung Jawab Pembimbing (Sesuai PRD)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('pembimbing.aktivitas.index') }}" class="p-5 rounded-2xl bg-slate-50 hover:bg-purple-50/50 border border-slate-200 hover:border-purple-300 transition flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center shrink-0">
                    📝
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Validasi Aktivitas</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Tinjau uraian tugas harian, setujui atau minta revisi.
                    </p>
                </div>
            </a>

            <a href="{{ route('pembimbing.izin.index') }}" class="p-5 rounded-2xl bg-slate-50 hover:bg-purple-50/50 border border-slate-200 hover:border-purple-300 transition flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 font-bold flex items-center justify-center shrink-0">
                    🩺
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Verifikasi Izin & Sakit</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Periksa surat dokter dan berkas pendukung ketidakhadiran.
                    </p>
                </div>
            </a>

            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-4 opacity-75">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0">
                    ⭐
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Penilaian Akhir Magang</h3>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded font-semibold">Tahap Berikutnya</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Evaluasi nilai akhir anak magang setelah masa praktik selesai.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
