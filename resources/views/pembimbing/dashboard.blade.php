@extends('layouts.pembimbing-mobile')
@section('title', 'Beranda')

@section('content')
@if (! $pembimbing)
    <div class="rounded-3xl bg-white p-6 text-center ring-1 ring-slate-200">
        <p class="text-lg font-extrabold">Profil pembimbing belum tersedia</p>
        <p class="mt-1 text-sm text-slate-500">Akun Anda belum terhubung dengan data pembimbing. Hubungi admin sistem.</p>
    </div>
@else
@php
    $h = now()->hour;
    $salam = $h < 11 ? 'Pagi' : ($h < 15 ? 'Siang' : ($h < 18 ? 'Sore' : 'Malam'));
    $statistik = [
        ['Hadir', $hari['hadir']], ['Sakit', $hari['sakit']], ['Izin', $hari['izin']],
        ['Cuti', $hari['cuti']], ['TL', $hari['tl']],
    ];
@endphp

{{-- Sapaan --}}
<header class="-mx-4 -mt-5 mb-4 border-b border-slate-200 bg-white px-4 py-4 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <h1 class="text-2xl font-extrabold leading-tight">Selamat {{ $salam }}, {{ $sapaan }}</h1>
    <p class="text-sm text-slate-500">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
</header>

{{-- Binaan aktif --}}
<a href="{{ route('pembimbing.binaan.index') }}" class="flex items-center justify-between rounded-3xl bg-brand p-5 text-white shadow-lg shadow-brand/20">
    <div>
        <p class="text-sm text-white/75">Binaan Aktif</p>
        <p class="text-3xl font-extrabold">{{ $jumlahAktif }} Peserta</p>
    </div>
    <svg class="h-14 w-14 text-white/90" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.5 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zm7 0a3 3 0 100-6 3 3 0 000 6zM8.5 13C5.7 13 2 14.4 2 17v2h13v-2c0-2.6-3.7-4-6.5-4zm7 0c-.4 0-.8 0-1.2.1 1.3.9 2.2 2.1 2.2 3.9v2h6.5v-2c0-2.2-3.4-4-7.5-4z"/></svg>
</a>

{{-- Presensi hari ini --}}
<div class="mt-6 mb-2 flex items-center justify-between">
    <h2 class="text-base font-extrabold">Presensi Hari Ini</h2>
    <a href="{{ route('pembimbing.presensi.index') }}" class="text-sm font-bold text-brand">Lihat Riwayat</a>
</div>
<dl class="grid grid-cols-5 gap-1 rounded-3xl bg-white px-2 py-4 text-center ring-1 ring-slate-200">
    @foreach ($statistik as [$label, $nilai])
        <div>
            <dt class="text-xs text-slate-500">{{ $label }}</dt>
            <dd class="text-2xl font-extrabold {{ $nilai ? '' : 'text-slate-400' }}">{{ $nilai }}</dd>
        </div>
    @endforeach
</dl>

{{-- Perlu ditindaklanjuti --}}
<h2 class="mt-6 mb-2 text-base font-extrabold">Perlu ditindaklanjuti</h2>
<ul class="space-y-3">
    @foreach ($perluTindak as $item)
        <li>
            <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-2xl border bg-white p-4 transition hover:shadow-sm {{ $item['jumlah'] ? 'border-brand/40' : 'border-slate-200' }}">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-extrabold {{ $item['jumlah'] ? 'bg-brand text-white' : 'bg-slate-200 text-slate-500' }}">{{ $item['jumlah'] }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-extrabold leading-tight">{{ $item['judul'] }}</span>
                    <span class="block truncate text-xs text-slate-500">{{ $item['ringkas'] }}</span>
                </span>
                <span class="text-xl text-brand-ink" aria-hidden="true">›</span>
            </a>
        </li>
    @endforeach
</ul>

{{-- Pekerjaan binaan --}}
<div class="mt-6 mb-2 flex items-center justify-between">
    <h2 class="text-base font-extrabold">Pekerjaan Binaan</h2>
    <a href="{{ route('pembimbing.pekerjaan.index') }}" class="text-sm font-bold text-brand">Lihat Semua</a>
</div>
<div class="rounded-3xl bg-white p-4 ring-1 ring-slate-200">
    @if ($pekerjaanDekat->isEmpty())
        <p class="text-sm text-slate-500">Tidak ada pekerjaan yang mendekati tenggat.</p>
    @else
        <p class="text-sm font-extrabold">{{ $pekerjaanDekat->count() }} Pekerjaan Mendekati Tenggat</p>
        <ul class="mt-2 space-y-2">
            @foreach ($pekerjaanDekat->take(2) as $p)
                <li class="text-xs text-slate-600">
                    <span class="block font-semibold text-brand-ink">{{ $p['judul'] }} - {{ $p['nama'] }}</span>
                    <span class="{{ $p['terlambat'] ? 'font-bold text-rose-700' : '' }}">
                        {{ $p['terlambat'] ? 'Lewat tenggat' : 'Tenggat' }} {{ $p['tenggat']->locale('id')->isoFormat('D MMM') }}
                        @if (! is_null($p['progress'])) - Progres {{ $p['progress'] }}% @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- Menu lainnya (HP; di desktop sudah ada di sidebar) --}}
<h2 class="mt-6 mb-2 text-base font-extrabold lg:hidden">Menu Lainnya</h2>
<ul class="divide-y divide-slate-200 overflow-hidden rounded-3xl border border-slate-300 bg-white lg:hidden">
    @include('pembimbing.menu-lainnya', ['variant' => 'card'])
</ul>
@endif
@endsection