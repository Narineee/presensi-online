@extends('layouts.mobile')
@section('title', 'Edit Pengajuan')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('izin.show', $izin->id) }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Edit pengajuan</h1>
        <p class="text-xs text-slate-500">Hanya bisa diubah selama masih menunggu persetujuan</p>
    </div>
</header>

<form method="POST" action="{{ route('izin.update', $izin->id) }}" enctype="multipart/form-data" class="space-y-4"
      x-data="{ jenis: @js(old('jenis_izin', $izin->jenis_izin)), mulai: @js(old('tanggal_mulai', $izin->tanggal_mulai->format('Y-m-d'))), busy: false }"
      @submit="busy = true">
    @csrf
    @method('PUT')

    <div>
        <p class="mb-2 text-base font-extrabold">Jenis permohonan</p>
        <div class="grid grid-cols-3 gap-2" role="radiogroup">
            @foreach (['sakit' => 'Sakit', 'izin' => 'Izin', 'cuti' => 'Cuti'] as $k => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="jenis_izin" value="{{ $k }}" x-model="jenis" class="peer sr-only" required>
                    <span class="block rounded-2xl border py-3 text-center text-sm font-extrabold transition peer-focus-visible:ring-2 peer-focus-visible:ring-brand peer-focus-visible:ring-offset-2"
                          :class="jenis === '{{ $k }}' ? 'border-brand bg-brand text-white shadow-sm' : 'border-slate-300 bg-white text-slate-600'">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <section class="space-y-4 rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <div class="grid grid-cols-2 gap-3">
            <label class="block text-sm font-bold">Mulai
                <input type="date" name="tanggal_mulai" x-model="mulai" required class="{{ $in }}">
            </label>
            <label class="block text-sm font-bold">Selesai
                <input type="date" name="tanggal_selesai" :min="mulai" value="{{ old('tanggal_selesai', $izin->tanggal_selesai->format('Y-m-d')) }}" required class="{{ $in }}">
            </label>
        </div>

        <label class="block text-sm font-bold">Alasan/keterangan tidak hadir
            <textarea name="alasan" rows="5" minlength="5" required class="{{ $in }}">{{ old('alasan', $izin->alasan) }}</textarea>
        </label>

        <label class="block text-sm font-bold">Lampiran/Dokumen Pendukung
            @if ($izin->bukti_file_url)
                <span class="mt-1 block text-xs font-normal text-slate-500">Berkas saat ini: <a href="{{ $izin->bukti_file_url }}" target="_blank" class="font-bold text-brand underline">lihat</a>. Kosongkan jika tidak ingin mengganti.</span>
            @endif
            <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" @required(! $izin->bukti_file)
                   class="{{ $in }} file:mr-3 file:rounded-lg file:border-0 file:bg-slate-200 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-brand-ink">
            <span class="mt-1.5 block text-[11px] font-normal text-slate-500">Format yang didukung: JPG, PNG, atau PDF. Maksimal 2MB.</span>
        </label>
    </section>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('izin.show', $izin->id) }}" class="rounded-2xl border border-slate-300 bg-white py-4 text-center text-sm font-extrabold">Batal</a>
        <button type="submit" :disabled="busy" class="rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm disabled:bg-slate-300 disabled:text-slate-500">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan perubahan'"></span>
        </button>
    </div>
</form>
@endsection