@extends('layouts.mobile')
@section('title', 'Rekapitulasi & Cetak Dokumen')

@section('content')
@php
    $target = $stats['target_hari'];
    $kosong = $stats['aktivitas_kosong'] ?? 0;

    // url null = halaman belum dibuat (kartu tampil nonaktif dengan label "Segera")
    $menu = [
        ['Riwayat Presensi', 'Kehadiran, jam masuk, pulang, izin.', route('presensi.riwayat'),
            'M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Riwayat Aktivitas', 'Catatan aktivitas harian lengkap.', null,
            'M9 12.75L11.25 15 15 9.75M10.5 3.75h3a1.5 1.5 0 011.5 1.5v.75h1.5a2.25 2.25 0 012.25 2.25v11.25a2.25 2.25 0 01-2.25 2.25H7.5a2.25 2.25 0 01-2.25-2.25V8.25A2.25 2.25 0 017.5 6H9v-.75a1.5 1.5 0 011.5-1.5z'],
        ['Riwayat Permohonan Ketidakhadiran', 'Permohonan ketidakhadiran', null,
            'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6-9.75-6'],
        ['Lembar nilai magang', 'Evaluasi pembimbing lapangan.', route('magang.penilaian.index'),
            'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
    ];
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Rekapitulasi &amp; Cetak Dokumen</h1>
        <p class="text-xs text-slate-500">Pantau dan cetak rekapitulasi</p>
    </div>
</header>

{{-- Ringkasan --}}
<section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
    <div class="grid grid-cols-3 gap-2">
        <div>
            <p class="text-xs text-slate-500">Kehadiran</p>
            <p class="text-2xl font-extrabold">{{ $stats['persen_kehadiran'] }}%</p>
            <p class="text-[11px] text-slate-500">{{ $stats['total_hadir'] }} dari {{ $target }} hari</p>
        </div>
        <div>
            <p class="text-xs text-slate-500">Jam Magang</p>
            <p class="text-2xl font-extrabold">{{ $stats['jam_magang'] }}j</p>
            <p class="text-[11px] text-slate-500">dari {{ $target * 8 }} jam</p>
        </div>
        <div>
            <p class="text-xs text-slate-500">Aktivitas</p>
            <p class="text-2xl font-extrabold">{{ $stats['total_aktivitas'] }}</p>
            <p class="text-[11px] {{ $kosong ? 'font-semibold text-amber-700' : 'text-slate-500' }}">{{ $kosong ? $kosong.' hari belum terisi' : 'Semua terisi' }}</p>
        </div>
    </div>
    <div class="mt-4 flex items-center justify-between text-xs">
        <span class="text-slate-500">Progres periode</span>
        <span class="font-bold text-brand">{{ $stats['progres_periode'] }}%</span>
    </div>
    <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $stats['progres_periode'] }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full bg-brand" style="width: {{ $stats['progres_periode'] }}%"></div>
    </div>
</section>

<h2 class="mt-6 mb-3 text-xl font-extrabold">Riwayat Anda</h2>
<ul class="space-y-3">
    @foreach ($menu as [$judul, $desk, $url, $icon])
        <li>
            @if ($url)
                <a href="{{ $url }}" class="flex items-center gap-3 rounded-2xl border border-brand/30 bg-white p-4 transition hover:border-brand hover:shadow-sm">
            @else
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/60 p-4 opacity-70" aria-disabled="true">
            @endif
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-extrabold leading-tight">{{ $judul }}</span>
                    <span class="block text-xs text-slate-500">{{ $desk }}</span>
                </span>
                @if ($url)
                    <span class="text-xl text-brand-ink" aria-hidden="true">›</span>
                @else
                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[10px] font-bold text-slate-600">Segera</span>
                @endif
            @if ($url) </a> @else </div> @endif
        </li>
    @endforeach
</ul>
@endsection