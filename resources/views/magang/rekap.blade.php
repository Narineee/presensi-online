@extends('layouts.mobile')
@section('title', 'Rekapitulasi & Cetak Dokumen')

@section('content')
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Presensi">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Pantau dan cetak rekapitulasi</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-5">

    {{-- Ringkasan Metrik Periode --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <div class="grid grid-cols-3 gap-2 pb-4 border-b border-slate-200/80">
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium truncate">Kehadiran</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 truncate">{{ $stats['persen_kehadiran'] }}%</p>
                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 leading-tight break-words">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium truncate">Jam Magang</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 truncate">{{ $stats['jam_magang'] }}j</p>
                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 leading-tight break-words">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 font-medium truncate">Aktivitas</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 truncate">{{ $stats['total_aktivitas'] }}</p>
                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 leading-tight break-words">{{ $stats['total_aktivitas'] > 0 ? 'Semua terisi' : 'Belum terisi' }}</p>
            </div>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                <span class="text-slate-700">Progres periode</span>
                <span class="text-brand font-black">{{ $stats['progres_periode'] }}%</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200/80 p-0.5">
                <div class="h-full rounded-full bg-brand transition-all duration-500" style="width: {{ $stats['progres_periode'] }}%"></div>
            </div>
        </div>
    </section>

    {{-- Daftar Menu Riwayat --}}
    <div>
        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-3 tracking-tight">Riwayat Anda</h2>

        <div class="space-y-3">
            {{-- 1. Riwayat Presensi --}}
            <a href="{{ route('presensi.riwayat') }}" class="group flex items-center justify-between rounded-2xl glass-card p-4 shadow-2xs hover:border-brand/40 transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand group-hover:scale-105 transition shadow-2xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition truncate">Riwayat Presensi</h3>
                        <p class="text-xs text-slate-500 truncate">Kehadiran, jam masuk, pulang, izin. (Rekap Presensi)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition shrink-0 ml-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 2. Riwayat Aktivitas --}}
            <a href="{{ route('aktivitas.riwayat') }}" class="group flex items-center justify-between rounded-2xl glass-card p-4 shadow-2xs hover:border-brand/40 transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand group-hover:scale-105 transition shadow-2xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            <path d="M19.5 7.125L16.875 4.5" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition truncate">Riwayat Aktivitas</h3>
                        <p class="text-xs text-slate-500 truncate">Catatan aktivitas harian lengkap. (Rekap Aktivitas)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition shrink-0 ml-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 3. Riwayat Permohonan Ketidakhadiran --}}
            <a href="{{ route('izin.riwayat') }}" class="group flex items-center justify-between rounded-2xl glass-card p-4 shadow-2xs hover:border-brand/40 transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand group-hover:scale-105 transition shadow-2xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition truncate">Riwayat Permohonan Ketidakhadiran</h3>
                        <p class="text-xs text-slate-500 truncate">Permohonan ketidakhadiran (Sakit, Izin, Cuti)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition shrink-0 ml-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 4. Lembar Nilai Magang --}}
            <a href="{{ route('magang.penilaian.index') }}" class="group flex items-center justify-between rounded-2xl glass-card p-4 shadow-2xs hover:border-brand/40 transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand group-hover:scale-105 transition shadow-2xs">
                        <svg class="h-6 w-6 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition truncate">Lembar nilai magang</h3>
                        <p class="text-xs text-slate-500 truncate">Evaluasi pembimbing lapangan.</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition shrink-0 ml-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
