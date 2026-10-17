@extends('layouts.pembimbing-mobile')
@section('title', 'Riwayat Presensi')

@section('content')
@php
    $namaDepan = \Illuminate\Support\Str::of((string) $magang->nama_lengkap)->trim()->before(' ')->toString();
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.binaan.show', $magang->id) }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Detail Binaan</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Riwayat presensi harian / {{ $namaDepan }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">
    @include('pembimbing.binaan.profil-ringkas', ['magang' => $magang])

    <h2 class="pt-1 text-base font-extrabold">Catatan terbaru</h2>

    <ul class="space-y-3">
        @forelse ($presensi as $c)
            <li class="rounded-2xl border border-brand/30 bg-white p-4">
                <dl class="grid grid-cols-2 gap-x-3 gap-y-3 text-sm">
                    <div><dt class="text-xs text-slate-500">Tanggal</dt><dd class="font-extrabold">{{ $c['tanggal']->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Mode</dt><dd class="font-extrabold">{{ $c['mode'] }}</dd></div>
                    <div>
                        <dt class="text-xs text-slate-500">Jam Masuk</dt>
                        <dd class="font-extrabold">
                            {{ $c['jam_masuk'] }}
                            @if ($c['terlambat']) <span class="block text-xs font-bold text-rose-700">Terlambat {{ $c['terlambat'] }} menit</span> @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Jam Pulang</dt>
                        <dd class="font-extrabold text-slate-900">
                            {{ $c['jam_pulang'] }}@if (!empty($c['catatan_pulang'])) <span class="text-xs font-bold text-amber-700">({{ $c['catatan_pulang'] }})</span>@endif
                        </dd>
                    </div>
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
            </li>
        @empty
            <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
                <p class="text-sm font-bold">Belum ada catatan presensi</p>
            </li>
        @endforelse
    </ul>

    @if ($presensi->hasPages())
        <nav class="flex items-center justify-between gap-3 pt-1" aria-label="Halaman riwayat">
            @if ($presensi->previousPageUrl())
                <a href="{{ $presensi->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Lebih baru</a>
            @else <span></span> @endif
            <span class="text-xs text-slate-500">Hal. {{ $presensi->currentPage() }} / {{ $presensi->lastPage() }}</span>
            @if ($presensi->nextPageUrl())
                <a href="{{ $presensi->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Lebih lama →</a>
            @else <span></span> @endif
        </nav>
    @endif
</div>
@endsection