@extends('layouts.mobile')
@section('title', 'Edit Aktivitas')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Edit aktivitas</h1>
        <p class="text-xs text-slate-500">{{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@if ($aktivitas->status === 'revisi')
    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-bold">Catatan revisi dari pembimbing</p>
        <p class="mt-1 rounded-xl bg-white/70 p-3 text-xs leading-relaxed">{{ $aktivitas->catatan_validasi ?? 'Harap melengkapi uraian aktivitas Anda.' }}</p>
        <p class="mt-2 text-[11px] text-amber-800">Setelah disimpan, status kembali menjadi <b>Pending</b> untuk divalidasi ulang.</p>
    </div>
@endif

<form method="POST" action="{{ route('aktivitas.update', $aktivitas->id) }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <h2 class="text-xl font-extrabold">Catatan pekerjaan</h2>
        <p class="mb-4 text-xs text-slate-500">Dapat dilihat pembimbing lapangan</p>

        <div class="space-y-4">
            <label class="block text-sm font-bold">Tanggal aktivitas
                <input type="date" name="tanggal" value="{{ old('tanggal', $aktivitas->tanggal->format('Y-m-d')) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
            </label>

            <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm font-bold">Waktu mulai
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', substr((string) $aktivitas->waktu_mulai, 0, 5)) }}" class="{{ $in }}">
                </label>
                <label class="block text-sm font-bold">Waktu selesai
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', substr((string) $aktivitas->waktu_selesai, 0, 5)) }}" class="{{ $in }}">
                </label>
            </div>

            <label class="block text-sm font-bold">Pekerjaan yang diberikan
                <select name="pekerjaan_id" required class="{{ $in }}">
                    @foreach ($pekerjaanList as $p)
                        <option value="{{ $p->id }}" @selected(old('pekerjaan_id', $aktivitas->pekerjaan_id) == $p->id)>
                            {{ $p->judul }}{{ $p->jenis ? ' ('.ucfirst($p->jenis).')' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-bold">Ringkasan aktivitas
                <textarea name="isi" rows="5" minlength="5" required class="{{ $in }}">{{ old('isi', $aktivitas->isi) }}</textarea>
            </label>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="rounded-2xl border border-slate-300 bg-white py-4 text-center text-sm font-extrabold">Batal</a>
        <button type="submit" :disabled="busy" class="rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm disabled:bg-slate-300 disabled:text-slate-500">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan perubahan'"></span>
        </button>
    </div>
</form>
@endsection