@extends('layouts.mobile')
@section('title', 'Aktivitas Harian')

@section('content')
@php
    $dariPresensi = request('redirect_to') === 'presensi';
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaTugas = $pekerjaanList->isNotEmpty();
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}"
       class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Aktivitas harian</h1>
        <p class="text-xs text-slate-500">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@if ($dariPresensi)
    <div class="mb-4 flex gap-3 rounded-2xl border border-brand/30 bg-brand-soft/60 p-3.5 text-sm">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-amber-500 text-[11px] font-bold text-white">!</span>
        <div>
            <p class="font-bold">Lengkapi sebelum pulang</p>
            <p class="text-xs text-slate-600">Presensi pulang akan terbuka setelah aktivitas hari ini disimpan.</p>
        </div>
    </div>
@endif

@unless ($adaTugas)
    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 p-3.5 text-sm text-amber-900">
        Belum ada pekerjaan aktif dari pembimbing. Hubungi pembimbing Anda agar diberi tugas terlebih dahulu.
    </div>
@endunless

<form method="POST" action="{{ route('aktivitas.store') }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @if ($dariPresensi)<input type="hidden" name="redirect_to" value="presensi">@endif

    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <h2 class="text-xl font-extrabold">Catatan pekerjaan</h2>
        <p class="mb-4 text-xs text-slate-500">Dapat dilihat pembimbing lapangan</p>

        <div class="space-y-4">
            <label class="block text-sm font-bold">Tanggal aktivitas
                <input type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
            </label>

            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm font-bold">Waktu mulai
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required class="{{ $in }}">
                </label>
                <label class="block text-sm font-bold">Waktu selesai
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required class="{{ $in }}">
                </label>
            </div>

            <label class="block text-sm font-bold">Pekerjaan yang diberikan
                <select name="pekerjaan_id" required @disabled(! $adaTugas) class="{{ $in }}">
                    <option value="">Pilih pekerjaan</option>
                    @foreach ($pekerjaanList as $p)
                        <option value="{{ $p->id }}" @selected(old('pekerjaan_id') == $p->id)>
                            {{ $p->judul }}{{ $p->jenis ? ' ('.ucfirst($p->jenis).')' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-bold">Ringkasan aktivitas
                <textarea name="isi" rows="5" minlength="5" required class="{{ $in }}" placeholder="Tuliskan apa yang Anda kerjakan hari ini">{{ old('isi') }}</textarea>
            </label>
        </div>
    </section>

    <div class="flex gap-3 rounded-2xl border border-brand/30 bg-brand-soft/60 p-3.5 text-xs leading-relaxed text-slate-700">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-brand text-[11px] font-bold text-brand">i</span>
        <p><b>Alur validasi:</b> Aktivitas ini akan dikirimkan kepada pembimbing Anda dengan status Pending. Lalu pembimbing dapat menyetujui, memberikan progres capaian, atau memberi catatan revisi jika ada hal yang perlu dilengkapi.</p>
    </div>

    <button type="submit" :disabled="busy || {{ $adaTugas ? 'false' : 'true' }}"
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
        <span x-text="busy ? 'Menyimpan…' : '✓ {{ $dariPresensi ? 'Simpan aktivitas & buka presensi pulang' : 'Simpan aktivitas' }}'"></span>
    </button>
</form>
@endsection