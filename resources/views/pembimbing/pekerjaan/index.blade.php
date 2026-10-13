@extends('layouts.pembimbing-mobile')
@section('title', 'Pekerjaan Binaan')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaFilter = request()->hasAny(['q', 'search', 'tanggal_mulai', 'tanggal_akhir', 'status', 'magang_id']);
    $badge = [
        'aktif' => ['Dikerjakan', 'bg-emerald-100 text-emerald-800'],
        'selesai' => ['Selesai', 'bg-slate-200 text-slate-700'],
        'nonaktif' => ['Nonaktif', 'bg-amber-100 text-amber-800'],
    ];
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-lg font-extrabold leading-tight">Pekerjaan binaan</h1>
        <p class="text-xs text-slate-500">Pantau tugas, progres, dan tenggat</p>
    </div>
</header>

{{-- Filter --}}
<form method="GET" action="{{ route('pembimbing.pekerjaan.index') }}" class="space-y-2.5">
    @if (request('magang_id'))<input type="hidden" name="magang_id" value="{{ request('magang_id') }}">@endif
    <label class="relative block">
        <span class="sr-only">Cari peserta</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
        <input type="search" name="q" value="{{ request('q', request('search')) }}" placeholder="Cari peserta atau judul" class="{{ $in }} pl-9">
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
            <option value="aktif" @selected(request('status') === 'aktif')>Dikerjakan</option>
            <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
    </label>
    <button type="submit" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Terapkan Filter</button>
    @if ($adaFilter)
        <a href="{{ route('pembimbing.pekerjaan.index') }}" class="block text-center text-xs font-bold text-slate-500 underline">Reset filter</a>
    @endif
</form>

<h2 class="mt-5 mb-3 text-base font-extrabold">{{ $pekerjaanList->total() }} pekerjaan</h2>

<ul class="space-y-3">
    @forelse ($pekerjaanList as $p)
        @php
            $proyek = $p->isProyek();
            $progress = (int) ($p->progress ?? 0);
            $b = $badge[$p->status] ?? $badge['aktif'];
            $akt = $terbaru->get($p->id);
            $tenggat = $p->target_selesai ? \Carbon\Carbon::parse($p->target_selesai) : null;
            $lewat = $tenggat && $p->status === 'aktif' && $tenggat->lt(today());
        @endphp
        <li class="rounded-2xl border border-brand/30 bg-white p-4">
            <span class="inline-block rounded-full px-3 py-1 text-xs font-extrabold {{ $b[1] }}">{{ $b[0] }}</span>
            <h3 class="mt-2 text-base font-extrabold leading-tight">{{ $p->judul }} ({{ $proyek ? 'Proyek' : 'Rutin' }})</h3>
            <p class="mt-0.5 text-sm text-slate-600">
                {{ $p->magang->nama_lengkap ?? '-' }} -
                <span class="{{ $lewat ? 'font-bold text-rose-700' : '' }}">{{ $tenggat ? 'Tenggat '.$tenggat->locale('id')->isoFormat('D MMM Y') : 'Tanpa tenggat' }}</span>
            </p>

            @if ($proyek)
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div>
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ $progress }}% selesai</p>
            @endif

            <p class="mt-3 text-sm text-slate-700">
                <span class="font-semibold">Uraian aktivitas terbaru :</span>
                {{ $akt ? \Illuminate\Support\Str::limit($akt->isi, 90) : 'Belum ada aktivitas.' }}
            </p>

            <div class="mt-3 flex items-end justify-between gap-3">
                <a href="{{ route('pembimbing.pekerjaan.show', $p->id) }}" class="text-sm font-bold text-brand hover:underline">Lihat pekerjaan &amp; Detail aktivitas →</a>
                <a href="{{ route('pembimbing.pekerjaan.edit', $p->id) }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-200 text-brand-ink hover:bg-brand-soft" aria-label="Edit pekerjaan">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                </a>
            </div>
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
            <p class="text-sm font-bold">Belum ada pekerjaan</p>
            <p class="mt-1 text-xs text-slate-500">Tambahkan pekerjaan agar peserta bisa mencatat aktivitas hariannya.</p>
        </li>
    @endforelse
</ul>

@if ($pekerjaanList->hasPages())
    <nav class="mt-4 flex items-center justify-between gap-3" aria-label="Halaman pekerjaan">
        @if ($pekerjaanList->previousPageUrl())
            <a href="{{ $pekerjaanList->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Sebelumnya</a>
        @else <span></span> @endif
        <span class="text-xs text-slate-500">Hal. {{ $pekerjaanList->currentPage() }} / {{ $pekerjaanList->lastPage() }}</span>
        @if ($pekerjaanList->nextPageUrl())
            <a href="{{ $pekerjaanList->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Berikutnya →</a>
        @else <span></span> @endif
    </nav>
@endif

{{-- Tombol tambah (menempel di atas navigasi bawah pada HP) --}}
<div class="sticky bottom-24 z-30 mt-6 lg:bottom-6">
    <a href="{{ route('pembimbing.pekerjaan.create', request('magang_id') ? ['magang_id' => request('magang_id')] : []) }}"
       class="block rounded-2xl bg-brand-ink py-4 text-center text-sm font-extrabold text-white shadow-lg shadow-black/20 active:scale-[.99]">Tambah pekerjaan binaan</a>
</div>
@endsection