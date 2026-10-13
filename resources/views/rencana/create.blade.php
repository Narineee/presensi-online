@extends('layouts.mobile')
@section('title', 'Tambah Rencana Kerja')

@section('content')
@php
    $dariAktivitas = request('redirect_to') === 'aktivitas';
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ $dariAktivitas ? route('aktivitas.create') : route('rencana.index') }}"
       class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Tambah Rencana Kerja</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Buat rencana kerja mandiri Anda</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

@if ($dariAktivitas)
    <div class="mb-4 flex gap-3 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-3.5 text-sm shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-amber-500 text-[11px] font-bold text-white">!</span>
        <div class="min-w-0 flex-1">
            <p class="font-bold text-amber-950">Tambah rencana baru untuk aktivitas</p>
            <p class="text-xs text-amber-800 break-words">Setelah rencana kerja disimpan, Anda akan diarahkan kembali ke formulir pengisian aktivitas.</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('rencana.store') }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @if ($dariAktivitas)<input type="hidden" name="redirect_to" value="aktivitas">@endif

    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Formulir Rencana Kerja</h2>
            <p class="text-xs text-slate-500 font-medium">Rencana ini dapat dipilih pada hari-hari pengisian aktivitas berikutnya.</p>
        </div>

        <div class="space-y-3.5">
            <label class="block text-xs sm:text-sm font-bold text-slate-800">Judul Rencana Kerja <span class="text-rose-500">*</span>
                <input type="text" name="judul" value="{{ old('judul') }}" required placeholder="Contoh: Melayani pegawai, Membuat desain UI, Membantu administrasi" class="{{ $in }}">
            </label>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Keterangan / Deskripsi Tugas (Opsional)
                <textarea name="deskripsi" rows="4" placeholder="Tuliskan lingkup tugas atau target rencana kerja ini jika diperlukan..." class="{{ $in }}">{{ old('deskripsi') }}</textarea>
            </label>
        </div>
    </section>

    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <p class="break-words"><b>Tips:</b> Buat rencana kerja secara umum atau spesifik sesuai arahan pembimbing. Rencana kerja tidak perlu dibuat ulang setiap hari, cukup pilih rencana kerja yang sesuai saat Anda melaporkan progres harian.</p>
    </div>

    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ $dariAktivitas ? route('aktivitas.create') : route('rencana.index') }}" class="rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-center text-sm font-extrabold text-slate-700 shadow-xs transition">Batal</a>
        <button type="submit" :disabled="busy"
                class="btn-brand-primary rounded-2xl py-3.5 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:bg-slate-300 cursor-pointer">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan Rencana'"></span>
        </button>
    </div>
</form>
@endsection
