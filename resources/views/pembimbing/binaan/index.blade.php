@extends('layouts.pembimbing-mobile')
@section('title', 'Daftar Binaan')

@section('content')
@php
    $cakupan = $cakupan ?? ['mulai' => null, 'selesai' => null, 'total_hari' => 0];
    $adaFilter = request()->hasAny(['q', 'tanggal_mulai', 'tanggal_akhir', 'status']);
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';

    $fmt = fn ($d, $f) => $d ? \Carbon\Carbon::parse($d)->locale('id')->isoFormat($f) : null;
    $cakupanTeks = $cakupan['mulai'] && $cakupan['selesai']
        ? $fmt($cakupan['mulai'], 'D MMM').' - '.$fmt($cakupan['selesai'], 'D MMM Y')
        : 'Belum ada data';

    $cetakUrl = route('pembimbing.presensi.cetak', array_filter([
        'tanggal_mulai' => request('tanggal_mulai', $cakupan['mulai']),
        'tanggal_akhir' => request('tanggal_akhir', $cakupan['selesai']),
    ]));
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-lg font-extrabold leading-tight">Daftar Binaan</h1>
        <p class="text-xs text-slate-500">Rekap presensi sepanjang magang</p>
    </div>
</header>

{{-- Filter --}}
<form method="GET" action="{{ route('pembimbing.binaan.index') }}" class="space-y-2.5">
    <label class="relative block">
        <span class="sr-only">Cari peserta</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari peserta" class="{{ $in }} pl-9">
    </label>
    <div class="grid grid-cols-2 gap-2.5">
        <label class="block"><span class="sr-only">Tanggal mulai</span>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" onchange="this.form.submit()" class="{{ $in }}" title="Tanggal mulai">
        </label>
        <label class="block"><span class="sr-only">Tanggal selesai</span>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" onchange="this.form.submit()" class="{{ $in }}" title="Tanggal selesai">
        </label>
    </div>
    <div class="flex gap-2.5">
        <label class="block flex-1"><span class="sr-only">Status</span>
            <select name="status" onchange="this.form.submit()" class="{{ $in }}">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>
        </label>
        @if ($adaFilter)
            <a href="{{ route('pembimbing.binaan.index') }}" class="grid place-items-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-600">Reset</a>
        @endif
    </div>
</form>

{{-- Cakupan rekap --}}
<section class="mt-4 rounded-3xl bg-brand p-5 text-white shadow-lg shadow-brand/20">
    <p class="text-base font-extrabold">Cakupan rekap: {{ $cakupanTeks }}</p>
    <p class="text-sm text-white/80">Total hari magang = {{ $cakupan['total_hari'] }} hari kerja</p>
</section>

<a href="{{ $cetakUrl }}" target="_blank" rel="noopener"
   class="mt-3 flex items-center justify-center gap-2 rounded-2xl bg-brand-ink py-3.5 text-sm font-extrabold text-white">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
    Cetak Rekapitulasi
</a>

<h2 class="mt-5 mb-3 text-base font-extrabold">
    {{ $summaryToday['binaan_aktif'] }} Peserta aktif
    @if ($summaryToday['binaan_selesai'])<span class="text-xs font-semibold text-slate-500">· {{ $summaryToday['binaan_selesai'] }} selesai</span>@endif
</h2>

<ul class="space-y-3">
    @forelse ($binaanList as $m)
        @php
            $r = $m->rekap;
            $aktif = $m->is_binaan_aktif;
            $stat = [
                ['Hadir', $r['total_hadir']], ['Sakit', $r['total_sakit']], ['Izin', $r['total_izin']],
                ['Cuti', $r['total_cuti']], ['TL', $r['total_tugas_luar']], ['Alfa', $r['total_alpa']],
            ];
        @endphp
        <li class="rounded-2xl border border-brand/30 bg-white p-4">
            <div class="flex items-center gap-3">
                @include('pembimbing.binaan.avatar', ['magang' => $m])
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-extrabold leading-tight">{{ $m->nama_lengkap }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $m->instansi_pendidikan ?? '-' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $aktif ? 'Aktif' : 'Selesai' }}</span>
            </div>

            <div class="mt-3 flex items-center justify-between text-sm">
                <span class="text-slate-600">Total hari magang</span>
                <span class="font-extrabold">{{ $r['hari_kerja_berjalan'] }} hari</span>
            </div>

            <dl class="mt-2 grid grid-cols-6 gap-1 rounded-2xl bg-slate-100 px-2 py-3 text-center">
                @foreach ($stat as [$label, $nilai])
                    <div>
                        <dt class="text-[11px] text-slate-500">{{ $label }}</dt>
                        <dd class="text-lg font-extrabold {{ $nilai ? '' : 'text-slate-400' }} {{ $label === 'Alfa' && $nilai ? 'text-rose-700' : '' }}">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>

            <a href="{{ route('pembimbing.binaan.show', $m->id) }}" class="mt-3 inline-block text-sm font-bold text-brand hover:underline">Detail &amp; Riwayat harian →</a>
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
            <p class="text-sm font-bold">Tidak ada peserta yang cocok</p>
            <p class="mt-1 text-xs text-slate-500">Ubah kata kunci atau filter di atas.</p>
        </li>
    @endforelse
</ul>
@endsection