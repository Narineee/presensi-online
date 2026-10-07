@extends('layouts.mobile')
@section('title', 'Rekapitulasi & Cetak Dokumen')

@section('content')
<header class="sticky-top-nav -mx-4 mb-5 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali ke Presensi">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight text-slate-900">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500">Pantau dan cetak rekapitulasi</p>
    </div>
</header>

<div class="space-y-6">

    {{-- Ringkasan Metrik Periode --}}
    <section class="rounded-3xl bg-[#ECEEEF]/80 p-5 ring-1 ring-slate-200/80 shadow-xs">
        <div class="grid grid-cols-3 gap-2 pb-4 border-b border-slate-300/60">
            <div>
                <p class="text-xs text-slate-600 font-medium">Kehadiran</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['persen_kehadiran'] }}%</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div>
                <p class="text-xs text-slate-600 font-medium">Jam Magang</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['jam_magang'] }}j</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div>
                <p class="text-xs text-slate-600 font-medium">Aktivitas</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total_aktivitas'] }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $stats['total_aktivitas'] > 0 ? 'Semua terisi' : 'Belum terisi' }}</p>
            </div>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                <span class="text-slate-700">Progres periode</span>
                <span class="text-brand font-extrabold">{{ $stats['progres_periode'] }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-300/70">
                <div class="h-full rounded-full bg-brand transition-all duration-500" style="width: {{ $stats['progres_periode'] }}%"></div>
            </div>
        </div>
    </section>

    {{-- Daftar Menu Riwayat --}}
    <div>
        <h2 class="text-lg font-extrabold text-slate-900 mb-3 tracking-tight">Riwayat Anda</h2>

        <div class="space-y-3">
            {{-- 1. Riwayat Presensi --}}
            <a href="{{ route('presensi.riwayat') }}" class="group flex items-center justify-between rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs hover:border-brand/40 hover:shadow-sm transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-200/70 text-slate-700 group-hover:bg-brand-soft group-hover:text-brand transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition">Riwayat Presensi</h3>
                        <p class="text-xs text-slate-500 truncate">Kehadiran, jam masuk, pulang, izin. (Rekap Presensi)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 2. Riwayat Aktivitas --}}
            <a href="{{ route('aktivitas.riwayat') }}" class="group flex items-center justify-between rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs hover:border-brand/40 hover:shadow-sm transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-200/70 text-slate-700 group-hover:bg-brand-soft group-hover:text-brand transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            <path d="M19.5 7.125L16.875 4.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition">Riwayat Aktivitas</h3>
                        <p class="text-xs text-slate-500 truncate">Catatan aktivitas harian lengkap. (Rekap Aktivitas)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 3. Riwayat Permohonan Ketidakhadiran --}}
            <a href="{{ route('izin.riwayat') }}" class="group flex items-center justify-between rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs hover:border-brand/40 hover:shadow-sm transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-200/70 text-slate-700 group-hover:bg-brand-soft group-hover:text-brand transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition">Riwayat Permohonan Ketidakhadiran</h3>
                        <p class="text-xs text-slate-500 truncate">Permohonan ketidakhadiran (Sakit, Izin, Cuti)</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>

            {{-- 4. Lembar Nilai Magang --}}
            <a href="{{ route('magang.penilaian.index') }}" class="group flex items-center justify-between rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs hover:border-brand/40 hover:shadow-sm transition active:scale-[.99]">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-200/70 text-slate-700 group-hover:bg-brand-soft group-hover:text-brand transition">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-brand transition">Lembar nilai magang</h3>
                        <p class="text-xs text-slate-500 truncate">Evaluasi pembimbing lapangan.</p>
                    </div>
                </div>
                <div class="text-slate-400 group-hover:text-brand group-hover:translate-x-0.5 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
