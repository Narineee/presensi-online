@extends('layouts.mobile')
@section('title', 'Edit Aktivitas')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Edit aktivitas</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

@if ($aktivitas->status === 'revisi')
    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-4 text-sm text-amber-900 shadow-2xs">
        <p class="font-bold">Catatan revisi dari pembimbing</p>
        <p class="mt-1 rounded-xl bg-white/80 p-3 text-xs leading-relaxed border border-amber-200 break-words">{{ $aktivitas->catatan_validasi ?? 'Harap melengkapi uraian aktivitas Anda.' }}</p>
        <p class="mt-2 text-[11px] text-amber-800">Setelah disimpan, status kembali menjadi <b>Pending</b> untuk divalidasi ulang.</p>
    </div>
@endif

<form method="POST" action="{{ route('aktivitas.update', $aktivitas->id) }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Catatan pekerjaan</h2>
        <p class="mb-4 text-xs text-slate-500 font-medium">Dapat dilihat pembimbing lapangan</p>

        <div class="space-y-3.5">
            <label class="block text-xs sm:text-sm font-bold text-slate-800">Tanggal aktivitas
                <input type="date" name="tanggal" value="{{ old('tanggal', $aktivitas->tanggal->format('Y-m-d')) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
            </label>

            <div class="grid grid-cols-2 gap-2.5">
                <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Waktu mulai
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', substr((string) $aktivitas->waktu_mulai, 0, 5)) }}" class="{{ $in }}">
                </label>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Waktu selesai
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', substr((string) $aktivitas->waktu_selesai, 0, 5)) }}" class="{{ $in }}">
                </label>
            </div>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Pekerjaan yang diberikan
                <select name="pekerjaan_id" required class="{{ $in }}">
                    @foreach ($pekerjaanList as $p)
                        <option value="{{ $p->id }}" @selected(old('pekerjaan_id', $aktivitas->pekerjaan_id) == $p->id)>
                            {{ $p->judul }}{{ $p->jenis ? ' ('.ucfirst($p->jenis).')' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-xs sm:text-sm font-bold text-slate-800">Ringkasan aktivitas
                <textarea name="isi" rows="5" minlength="5" required class="{{ $in }}">{{ old('isi', $aktivitas->isi) }}</textarea>
            </label>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-center text-sm font-extrabold text-slate-700 shadow-xs transition">Batal</a>
        <button type="submit" :disabled="busy" class="btn-brand-primary rounded-2xl py-3.5 text-sm font-extrabold text-white shadow-sm disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan perubahan'"></span>
        </button>
    </div>
</form>
@endsection