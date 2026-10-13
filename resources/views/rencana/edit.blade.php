@extends('layouts.mobile')
@section('title', 'Edit Rencana Kerja')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('rencana.index') }}"
       class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Edit Rencana Kerja</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $rencana->judul }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<form method="POST" action="{{ route('rencana.update', $rencana->id) }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Perbarui Rencana Kerja</h2>
            <p class="text-xs text-slate-500 font-medium">Pengubahan rencana kerja tidak akan merusak riwayat aktivitas yang telah tervalidasi.</p>
        </div>

        <div class="space-y-3.5">
            <label class="block text-xs sm:text-sm font-bold text-slate-800">Judul Rencana Kerja <span class="text-rose-500">*</span>
                <input type="text" name="judul" value="{{ old('judul', $rencana->judul) }}" required class="{{ $in }}">
            </label>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Status Rencana Kerja
                <select name="status" class="{{ $in }}">
                    <option value="aktif" @selected(old('status', $rencana->status) === 'aktif')>Aktif (Dapat dipilih di formulir aktivitas)</option>
                    <option value="selesai" @selected(old('status', $rencana->status) === 'selesai')>Selesai (Tugas sudah rampung)</option>
                    <option value="nonaktif" @selected(old('status', $rencana->status) === 'nonaktif')>Nonaktif (Diarsipkan)</option>
                </select>
            </label>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Keterangan / Deskripsi Tugas (Opsional)
                <textarea name="deskripsi" rows="4" class="{{ $in }}">{{ old('deskripsi', $rencana->deskripsi) }}</textarea>
            </label>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ route('rencana.index') }}" class="rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-center text-sm font-extrabold text-slate-700 shadow-xs transition">Batal</a>
        <button type="submit" :disabled="busy"
                class="btn-brand-primary rounded-2xl py-3.5 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:bg-slate-300 cursor-pointer">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan Perubahan'"></span>
        </button>
    </div>
</form>
@endsection
