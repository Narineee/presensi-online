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
    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
        {{-- 1. Ringkasan Progres Periode (Atas) --}}
        <div>
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Progres periode</p>
                    <p class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5 truncate">
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 font-medium break-words">
                        {{ $stats['hari_kerja_berlalu'] }} dari {{ $stats['target_hari'] }} hari kerja berlalu
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-2xl sm:text-3xl font-black text-brand tracking-tight">{{ $stats['progres_periode'] }}%</span>
                </div>
            </div>

            {{-- Progress Bar Progres Periode --}}
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200/80 p-0.5">
                <div class="h-full rounded-full bg-brand transition-all duration-500" style="width: {{ $stats['progres_periode'] }}%"></div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-200/80"></div>

        {{-- 2. Ringkasan Kehadiran dan Jam Magang (Bawah) --}}
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Kehadiran & Jam Magang</h3>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5">
                {{-- A. Kehadiran Berdasarkan Hari --}}
                <div class="rounded-2xl glass-subcard p-3 sm:p-4 border border-white/80 space-y-1 min-w-0">
                    <p class="text-xs text-slate-500 font-bold truncate">Kehadiran</p>
                    <p class="text-xl sm:text-2xl font-black text-slate-900 truncate">{{ $stats['persen_kehadiran'] }}%</p>
                    <p class="text-[10px] sm:text-xs text-slate-600 font-medium leading-tight break-words">
                        {{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari
                    </p>
                </div>

                {{-- B. Jam Magang --}}
                <div class="rounded-2xl glass-subcard p-3 sm:p-4 border border-white/80 space-y-2 min-w-0">
                    <div>
                        <p class="text-xs text-slate-500 font-bold truncate">Jam Magang</p>
                        <p class="text-xl sm:text-2xl font-black text-slate-900 truncate">{{ $stats['persen_jam'] }}%</p>
                        <p class="text-[10px] sm:text-xs text-slate-600 font-medium leading-tight break-words">
                            {{ $stats['jam_aktual'] }} dari {{ $stats['target_jam'] }} jam
                        </p>
                    </div>

                    {{-- Progress Bar Jam Magang --}}
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200/80">
                        <div class="h-full rounded-full bg-emerald-600 transition-all duration-500" style="width: {{ $stats['persen_jam'] }}%"></div>
                    </div>
                </div>
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

            {{-- 5. Cetak Sertifikat Magang --}}
            <a href="{{ route('magang.sertifikat.index') }}" class="group flex items-center justify-between rounded-2xl glass-card p-4 shadow-2xs hover:border-brand/40 transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-soft text-brand group-hover:scale-105 transition shadow-2xs">
                        <svg class="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition truncate">Cetak sertifikat</h3>
                        <p class="text-xs text-slate-500 truncate">Sertifikat kelulusan magang.</p>
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
