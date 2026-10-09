@extends('layouts.pembimbing-mobile')
@section('title', 'Riwayat Presensi')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaFilter = request()->hasAny(['q', 'tanggal_mulai', 'tanggal_akhir', 'status']);

    $cetakUrl = route('pembimbing.presensi.cetak', request()->only(['q', 'tanggal_mulai', 'tanggal_akhir', 'status', 'magang_id']));
    $fmt = fn ($d) => \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D MMM Y');
    $periodeCetak = request('tanggal_mulai') || request('tanggal_akhir')
        ? (request('tanggal_mulai') ? $fmt(request('tanggal_mulai')) : '…').' - '.(request('tanggal_akhir') ? $fmt(request('tanggal_akhir')) : '…')
        : 'bulan berjalan ('.now()->locale('id')->isoFormat('MMMM Y').')';

    $warna = [
        'hadir' => 'bg-emerald-100 text-emerald-800',
        'tl' => 'bg-sky-100 text-sky-800',
        'sakit' => 'bg-rose-100 text-rose-800',
        'izin' => 'bg-blue-100 text-blue-800',
        'cuti' => 'bg-purple-100 text-purple-800',
        'alpa' => 'bg-red-100 text-red-800',
    ];
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-lg font-extrabold leading-tight">Riwayat presensi</h1>
        <p class="text-xs text-slate-500">Catatan harian peserta binaan</p>
    </div>
</header>

{{-- Filter --}}
<form method="GET" action="{{ route('pembimbing.presensi.index') }}" class="space-y-2.5">
    @if (request('magang_id'))<input type="hidden" name="magang_id" value="{{ request('magang_id') }}">@endif
    <label class="relative block">
        <span class="sr-only">Cari peserta</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari peserta" class="{{ $in }} pl-9">
    </label>
    <div class="grid grid-cols-2 gap-2.5">
        <label class="block"><span class="sr-only">Tanggal mulai</span>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="{{ $in }}" title="Tanggal mulai">
        </label>
        <label class="block"><span class="sr-only">Tanggal selesai</span>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="{{ $in }}" title="Tanggal selesai">
        </label>
    </div>
    <label class="block"><span class="sr-only">Status</span>
        <select name="status" class="{{ $in }}">
            <option value="">Semua status</option>
            @foreach (['hadir' => 'Hadir', 'tl' => 'Tugas Luar (TL)', 'sakit' => 'Sakit', 'izin' => 'Izin', 'cuti' => 'Cuti', 'alpa' => 'Alpa'] as $k => $l)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
            @endforeach
        </select>
    </label>

    <button type="submit" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Terapkan Filter</button>
    @if ($adaFilter)
        <a href="{{ route('pembimbing.presensi.index') }}" class="block text-center text-xs font-bold text-slate-500 underline">Reset filter</a>
    @endif
</form>

<a href="{{ $cetakUrl }}" target="_blank" rel="noopener" class="mt-3 flex items-center justify-center gap-2 rounded-2xl bg-brand-ink py-3.5 text-sm font-extrabold text-white">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
    Cetak Rekapitulasi
</a>
<p class="mt-1.5 text-center text-[11px] text-slate-500">Periode cetak: {{ $periodeCetak }}</p>

<p class="mt-3 text-xs leading-relaxed text-slate-500">Catatan per tanggal, bukan rekap periode. Jam keluar kosong berarti belum tercatat.</p>

<h2 class="mt-3 mb-3 text-base font-extrabold">Catatan terbaru</h2>

<ul class="space-y-3">
    @forelse ($presensi as $c)
        <li class="rounded-2xl border border-brand/30 bg-white p-4">
            <div class="flex items-center gap-3">
                @if ($c['magang'])
                    @include('pembimbing.binaan.avatar', ['magang' => $c['magang']])
                @else
                    <span class="h-12 w-12 shrink-0 rounded-full bg-slate-200" aria-hidden="true"></span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-extrabold leading-tight">{{ $c['nama'] }}</p>
                    <p class="text-xs text-slate-500">{{ $c['tanggal']->locale('id')->isoFormat('D MMM Y') }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $warna[$c['badge_kunci']] ?? 'bg-slate-200 text-slate-700' }}">{{ $c['badge'] }}</span>
            </div>

            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Masuk</dt>
                    <dd class="font-extrabold">
                        {{ $c['jam_masuk'] }}
                        @if ($c['terlambat']) <span class="text-xs font-bold text-rose-700">(Terlambat {{ $c['terlambat'] }} menit)</span> @endif
                    </dd>
                </div>
                <div><dt class="text-xs text-slate-500">Keluar</dt><dd class="font-extrabold">{{ $c['jam_keluar'] }}</dd></div>
            </dl>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm text-slate-600">Bukti Foto</span>
                <span class="flex flex-wrap gap-1.5">
                    @if ($c['lampiran_url'])
                        <a href="{{ $c['lampiran_url'] }}" target="_blank" rel="noopener" class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-brand-ink hover:bg-brand-soft">Lampiran</a>
                    @endif
                    @if ($c['foto_masuk_url'])
                        <a href="{{ $c['foto_masuk_url'] }}" target="_blank" rel="noopener" class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-brand-ink hover:bg-brand-soft">Masuk</a>
                    @endif
                    @if ($c['foto_keluar_url'])
                        <a href="{{ $c['foto_keluar_url'] }}" target="_blank" rel="noopener" class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-brand-ink hover:bg-brand-soft">Pulang</a>
                    @endif
                    @if (! $c['lampiran_url'] && ! $c['foto_masuk_url'] && ! $c['foto_keluar_url'])
                        <span class="text-xs text-slate-400">Tidak ada</span>
                    @endif
                </span>
            </div>

            <p class="mt-3 text-sm text-slate-700">{{ $c['keterangan'] }}</p>
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
            <p class="text-sm font-bold">Belum ada catatan presensi</p>
            <p class="mt-1 text-xs text-slate-500">Ubah kata kunci atau rentang tanggal.</p>
        </li>
    @endforelse
</ul>

@if ($presensi->hasPages())
    <nav class="mt-4 flex items-center justify-between gap-3" aria-label="Halaman riwayat">
        @if ($presensi->previousPageUrl())
            <a href="{{ $presensi->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Lebih baru</a>
        @else <span></span> @endif
        <span class="text-xs text-slate-500">Hal. {{ $presensi->currentPage() }} / {{ $presensi->lastPage() }}</span>
        @if ($presensi->nextPageUrl())
            <a href="{{ $presensi->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Lebih lama →</a>
        @else <span></span> @endif
    </nav>
@endif
@endsection