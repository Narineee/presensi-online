@extends('layouts.mobile')
@section('title', 'Aktivitas Harian')

@section('content')
@php
    $dariPresensi = request('redirect_to') === 'presensi';
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
    $adaTugas = $pekerjaanList->isNotEmpty();
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}"
       class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Aktivitas harian</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

@if ($dariPresensi)
    <div class="mb-4 flex gap-3 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-3.5 text-sm shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-amber-500 text-[11px] font-bold text-white">!</span>
        <div class="min-w-0 flex-1">
            <p class="font-bold text-amber-950">Lengkapi sebelum pulang</p>
            <p class="text-xs text-amber-800 break-words">Presensi pulang akan terbuka setelah aktivitas hari ini disimpan.</p>
        </div>
    </div>
@endif

@unless ($adaTugas)
    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-3.5 text-sm text-amber-900 shadow-2xs">
        Belum ada pekerjaan aktif dari pembimbing. Hubungi pembimbing Anda agar diberi tugas terlebih dahulu.
    </div>
@endunless

<form method="POST" action="{{ route('aktivitas.store') }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @if ($dariPresensi)<input type="hidden" name="redirect_to" value="presensi">@endif

    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Catatan pekerjaan</h2>
        <p class="mb-4 text-xs text-slate-500 font-medium">Dapat dilihat pembimbing lapangan</p>

        <div class="space-y-3.5">
            <label class="block text-xs sm:text-sm font-bold text-slate-800">Tanggal aktivitas
                <input type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
            </label>

            <div class="grid grid-cols-2 gap-2.5">
                <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Waktu mulai
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required class="{{ $in }}">
                </label>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Waktu selesai
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required class="{{ $in }}">
                </label>
            </div>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Pekerjaan yang diberikan
                <select name="pekerjaan_id" required @disabled(! $adaTugas) class="{{ $in }}">
                    <option value="">Pilih pekerjaan</option>
                    @foreach ($pekerjaanList as $p)
                        <option value="{{ $p->id }}" @selected(old('pekerjaan_id') == $p->id)>
                            {{ $p->judul }}{{ $p->jenis ? ' ('.ucfirst($p->jenis).')' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Ringkasan aktivitas
                <textarea name="isi" rows="5" minlength="5" required class="{{ $in }}" placeholder="Tuliskan apa yang Anda kerjakan hari ini">{{ old('isi') }}</textarea>
            </label>
        </div>
    </section>

    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <p class="break-words"><b>Alur validasi:</b> Aktivitas ini akan dikirimkan kepada pembimbing Anda dengan status Pending. Lalu pembimbing dapat menyetujui, memberikan progres capaian, atau memberi catatan revisi jika ada hal yang perlu dilengkapi.</p>
    </div>

    <button type="submit" :disabled="busy || {{ $adaTugas ? 'false' : 'true' }}"
            class="btn-brand-primary flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
        <span x-text="busy ? 'Menyimpan…' : '✓ {{ $dariPresensi ? 'Simpan aktivitas & buka presensi pulang' : 'Simpan aktivitas' }}'"></span>
    </button>
</form>
@endsection