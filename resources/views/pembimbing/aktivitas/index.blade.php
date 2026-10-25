@extends('layouts.pembimbing-mobile')
@section('title', 'Validasi Aktivitas')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaFilter = request()->hasAny(['q', 'tanggal', 'tanggal_mulai', 'tanggal_akhir', 'status', 'pekerjaan_id', 'magang_id']);
    $tgl = fn ($d) => $d->locale('id')->isoFormat('D MMM Y');
    $namaDepan = fn ($n) => \Illuminate\Support\Str::of((string) $n)->trim()->before(' ')->toString();

    $cetakUrl = route('pembimbing.aktivitas.cetak', request()->query());
    $fmt = fn ($d) => \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D MMM Y');
    $periodeCetak = request('tanggal_mulai') || request('tanggal_akhir')
        ? (request('tanggal_mulai') ? $fmt(request('tanggal_mulai')) : '…').' - '.(request('tanggal_akhir') ? $fmt(request('tanggal_akhir')) : '…')
        : (request('tanggal') ? $fmt(request('tanggal')) : 'bulan berjalan ('.now()->locale('id')->isoFormat('MMMM Y').')');
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Validasi &amp; Rekap Aktivitas</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Tinjau dan cetak catatan pengerjaan binaan</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

{{-- Filter --}}
<form method="GET" action="{{ route('pembimbing.aktivitas.index') }}" class="space-y-2.5">
    @foreach (['pekerjaan_id', 'tanggal'] as $tetap)
        @if (request($tetap))<input type="hidden" name="{{ $tetap }}" value="{{ request($tetap) }}">@endif
    @endforeach
    <label class="relative block">
        <span class="sr-only">Cari peserta</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari peserta" class="{{ $in }} pl-9">
    </label>
    <div class="grid grid-cols-2 gap-2.5">
        <label class="block"><span class="sr-only">Pilih Peserta Magang</span>
            <select name="magang_id" class="{{ $in }}">
                <option value="">Semua peserta</option>
                @foreach ($magangList as $sm)
                    <option value="{{ $sm->id }}" @selected(request('magang_id') == $sm->id)>{{ $sm->nama_lengkap }}</option>
                @endforeach
            </select>
        </label>
        <label class="block"><span class="sr-only">Status</span>
            <select name="status" class="{{ $in }}">
                <option value="">Semua status</option>
                <option value="pending" @selected(request('status') === 'pending')>Menunggu validasi</option>
                <option value="approve" @selected(request('status') === 'approve')>Disetujui</option>
                <option value="revisi" @selected(request('status') === 'revisi')>Perbaikan</option>
            </select>
        </label>
    </div>
    <div class="grid grid-cols-2 gap-2.5">
        <label class="block"><span class="sr-only">Tanggal mulai</span>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="{{ $in }}" title="Tanggal mulai">
        </label>
        <label class="block"><span class="sr-only">Tanggal selesai</span>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="{{ $in }}" title="Tanggal selesai">
        </label>
    </div>
    <button type="submit" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Terapkan Filter</button>
    @if ($adaFilter)
        <a href="{{ route('pembimbing.aktivitas.index') }}" class="block text-center text-xs font-bold text-slate-500 underline">Reset filter</a>
    @endif
</form>

<a href="{{ $cetakUrl }}" target="_blank" rel="noopener" class="mt-3 flex items-center justify-center gap-2 rounded-2xl bg-brand-ink py-3.5 text-sm font-extrabold text-white shadow-xs transition hover:bg-brand-ink/90 active:scale-[.99]">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
    <span>Cetak Rekapitulasi</span>
</a>
<p class="mt-1.5 text-center text-[11px] text-slate-500">Periode cetak: {{ $periodeCetak }}</p>

{{-- MENUNGGU VALIDASI --}}
<h2 class="mt-5 mb-3 text-base font-extrabold">{{ $menunggu->count() }} menunggu validasi <span class="sr-only">Foto &amp; Peserta Magang</span></h2>

<ul class="space-y-3">
    @forelse ($menunggu as $a)
        @php
            $m = $a->pengguna?->magang;
            $nama = $m?->nama_lengkap ?? ($a->pengguna->username ?? '-');
            $pk = $a->pekerjaan;
            $proyek = $pk && $pk->isProyek();
            $progPk = (int) ($pk->progress ?? $a->progress ?? 0);
            $hasDetails = $a->details->isNotEmpty();
            $judulUtama = $hasDetails
                ? $a->details->pluck('judul_rencana')->join(', ')
                : ($pk?->judul ?? ($a->judul ?? 'Aktivitas Harian'));
        @endphp
        <li x-data="{ open: {{ $loop->first ? 'true' : 'false' }}, alasan: '', err: false }" class="rounded-2xl border border-brand/30 bg-white p-4">
            <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">Menunggu validasi</span>
            <h3 class="mt-2 text-base font-extrabold leading-tight">{{ $nama }}</h3>
            <p x-show="!open" class="text-sm text-slate-600">{{ $tgl($a->tanggal) }} - {{ $judulUtama }}</p>
            <p x-show="open" x-cloak class="text-sm text-slate-600">{{ $tgl($a->tanggal) }} - Dikirim {{ $a->created_at->format('H.i') }} WITA</p>

            <div x-show="open" x-cloak>
                @if ($hasDetails)
                    <div class="mt-3 space-y-2.5">
                        @foreach ($a->details as $idx => $d)
                            <div class="rounded-xl bg-slate-50 p-3 border border-slate-200 text-xs">
                                <div class="flex items-center justify-between mb-1 gap-2">
                                    <span class="font-extrabold text-brand truncate">#{{ $idx + 1 }} {{ $d->judul_rencana }}</span>
                                    @if (in_array(strtolower(trim($d->progres)), ['selesai', 'belum']))
                                        <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ strtolower(trim($d->progres)) === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ strtolower(trim($d->progres)) === 'selesai' ? '✓ Selesai' : '⏳ Belum' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="whitespace-pre-line text-slate-800 leading-relaxed font-semibold">Status: {{ $d->progres }}</p>
                                @if (strtolower(trim($d->progres)) === 'belum' && $d->alasan)
                                    <div class="mt-2 pt-1.5 border-t border-slate-200">
                                        <span class="font-extrabold text-amber-900 block text-[11px]">Alasan Belum:</span>
                                        <p class="text-slate-700 italic bg-amber-50/70 p-2 rounded-lg border border-amber-200/60 mt-0.5">{{ $d->alasan }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <h4 class="mt-3 text-base font-extrabold leading-snug">{{ $judulUtama }}</h4>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-brand-dark">{{ $a->isi }}</p>
                @endif
            </div>

            <button type="button" x-show="!open" @click="open = true" class="mt-3 text-sm font-bold text-brand hover:underline">Buka untuk validasi →</button>

            <form x-show="open" x-cloak method="POST" action="{{ route('pembimbing.aktivitas.validasi', $a->id) }}" class="mt-4">
                @csrf

                @if ($proyek)
                    <label class="mb-3 block text-sm font-bold">Progres proyek setelah disetujui (%)
                        <input type="number" name="progress" min="0" max="100" step="5" value="{{ $progPk }}" class="{{ $in }} mt-1.5">
                    </label>
                @endif

                <label class="block text-sm font-bold" for="alasan-{{ $a->id }}">Alasan perbaikan</label>
                <textarea id="alasan-{{ $a->id }}" name="catatan_validasi" rows="3" maxlength="500" x-model="alasan" @input="err = false"
                          placeholder="Tulis bagian yang perlu diperbaiki… Wajib jika meminta perbaikan."
                          class="{{ $in }} mt-1.5"></textarea>
                <p x-show="err" x-cloak class="mt-1 text-xs font-bold text-rose-700" role="alert">Alasan perbaikan wajib diisi.</p>

                <div class="mt-3 space-y-2">
                    <button type="submit" name="status" value="approve" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Setujui aktivitas</button>
                    <button type="submit" name="status" value="revisi" @click="if (!alasan.trim()) { $event.preventDefault(); err = true }"
                            class="w-full rounded-2xl border border-slate-400 bg-white py-3.5 text-sm font-extrabold hover:bg-slate-50">Minta Perbaikan</button>
                </div>
            </form>
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
            <p class="text-sm font-bold">Tidak ada aktivitas yang menunggu</p>
            <p class="mt-1 text-xs text-slate-500">Semua catatan peserta sudah divalidasi.</p>
        </li>
    @endforelse
</ul>

{{-- KEPUTUSAN TERBARU --}}
<h2 class="mt-6 mb-3 text-base font-extrabold">Keputusan terbaru</h2>

<ul class="space-y-3">
    @forelse ($keputusan as $a)
        @php
            $m = $a->pengguna?->magang;
            $nama = $m?->nama_lengkap ?? ($a->pengguna->username ?? '-');
            $pk = $a->pekerjaan;
            $hasDetails = $a->details->isNotEmpty();
            $judulPk = $hasDetails
                ? $a->details->pluck('judul_rencana')->join(', ')
                : ($pk?->judul ?? ($a->judul ?? 'Aktivitas Harian'));
            $setuju = $a->status === 'approve';
            $pembimbingNama = $namaDepan($a->validator?->pembimbing?->nama_lengkap ?? $a->validator?->username);
        @endphp
        <li class="rounded-2xl border border-brand/30 bg-white p-4">
            <span class="inline-block rounded-full px-3 py-1 text-xs font-extrabold {{ $setuju ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $setuju ? 'Disetujui' : 'Perbaikan' }}</span>
            <h3 class="mt-2 text-base font-extrabold leading-tight">{{ $nama }}</h3>
            <p class="text-sm text-slate-600">{{ $tgl($a->tanggal) }} - {{ $judulPk }}</p>

            @if ($hasDetails)
                <div class="mt-2.5 space-y-1.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs text-slate-700">
                    @foreach ($a->details as $d)
                        <div class="leading-relaxed">
                            <span class="font-extrabold text-brand">• {{ $d->judul_rencana }}:</span>
                            <span>{{ $d->progres }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($a->catatan_validasi)
                <p class="mt-3 flex gap-2 rounded-xl p-3 text-sm leading-relaxed {{ $setuju ? 'bg-emerald-50 text-emerald-900' : 'bg-rose-50 text-rose-800' }}">
                    <span class="mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full border text-[10px] font-bold" aria-hidden="true">i</span>
                    <span>{{ $pembimbingNama ? $pembimbingNama.': ' : '' }}{{ $a->catatan_validasi }}</span>
                </p>
            @endif
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">Belum ada keputusan.</li>
    @endforelse
</ul>

@if ($keputusan->hasPages())
    <nav class="mt-4 flex items-center justify-between gap-3" aria-label="Halaman keputusan">
        @if ($keputusan->previousPageUrl())
            <a href="{{ $keputusan->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Lebih baru</a>
        @else <span></span> @endif
        <span class="text-xs text-slate-500">Hal. {{ $keputusan->currentPage() }} / {{ $keputusan->lastPage() }}</span>
        @if ($keputusan->nextPageUrl())
            <a href="{{ $keputusan->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Lebih lama →</a>
        @else <span></span> @endif
    </nav>
@endif
@endsection