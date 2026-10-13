@extends('layouts.pembimbing-mobile')
@section('title', 'Tambah Pekerjaan')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.pekerjaan.index') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-lg font-extrabold leading-tight">Tambah Pekerjaan</h1>
        <p class="text-xs text-slate-500">Pekerjaan binaan</p>
    </div>
</header>

<form method="POST" action="{{ route('pembimbing.pekerjaan.store') }}" x-data="{ jenis: @js(old('jenis', '')), busy: false }" @submit="busy = true">
    @csrf

    <section class="space-y-4 rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <label class="block text-sm font-bold">Peserta Penerima Tugas <span class="text-rose-600" aria-hidden="true">*</span>
            <select name="magang_id" required class="{{ $in }}">
                <option value="">Pilih peserta binaan</option>
                @foreach ($supervisedMagang as $m)
                    <option value="{{ $m->id }}" @selected(old('magang_id', $selectedMagangId) == $m->id)>
                        {{ $m->nama_lengkap }}{{ $m->divisi?->nama_divisi ? ' - '.$m->divisi->nama_divisi : '' }}
                    </option>
                @endforeach
            </select>
        </label>

        <fieldset>
            <legend class="text-sm font-bold">Jenis Pekerjaan <span class="text-rose-600" aria-hidden="true">*</span></legend>
            <div class="mt-1.5 grid grid-cols-2 gap-3">
                @foreach (['proyek' => ['Proyek', 'Ada target & progres 0-100%'], 'rutin' => ['Rutin', 'Berulang, tanpa progres']] as $k => [$label, $ket])
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="{{ $k }}" x-model="jenis" class="peer sr-only" required>
                        <span class="block rounded-xl border px-3 py-3 text-center transition peer-focus-visible:ring-2 peer-focus-visible:ring-brand peer-focus-visible:ring-offset-2"
                              :class="jenis === '{{ $k }}' ? 'border-brand bg-brand text-white' : 'border-slate-300 bg-white text-slate-700'">
                            <span class="block text-sm font-extrabold">{{ $label }}</span>
                            <span class="block text-[11px] leading-tight opacity-80">{{ $ket }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <label class="block text-sm font-bold">Judul Pekerjaan <span class="text-rose-600" aria-hidden="true">*</span>
            <input type="text" name="judul" value="{{ old('judul') }}" maxlength="255" required class="{{ $in }}" placeholder="Contoh: Rekap data layanan">
        </label>

        <label class="block text-sm font-bold">Deskripsi / Petunjuk Tugas <span class="text-rose-600" aria-hidden="true">*</span>
            <textarea name="deskripsi" rows="5" required class="{{ $in }}" placeholder="Jelaskan ruang lingkup dan hasil yang diharapkan">{{ old('deskripsi') }}</textarea>
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-sm font-bold">Mulai
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" class="{{ $in }}">
            </label>
            <label class="block text-sm font-bold">Selesai
                <input type="date" name="target_selesai" value="{{ old('target_selesai') }}" class="{{ $in }}">
            </label>
        </div>

        <p x-show="jenis === 'proyek'" x-cloak class="rounded-xl bg-brand-soft/70 p-3 text-xs leading-relaxed text-slate-700">
            Progres proyek dimulai dari 0% dan diperbarui otomatis saat Anda menyetujui aktivitas harian peserta.
        </p>

        <div class="flex justify-end gap-2.5 pt-1">
            <a href="{{ route('pembimbing.pekerjaan.index') }}" class="rounded-full bg-slate-200 px-6 py-2.5 text-sm font-extrabold">Batal</a>
            <button type="submit" :disabled="busy" class="rounded-full bg-brand px-6 py-2.5 text-sm font-extrabold text-white disabled:bg-slate-300 disabled:text-slate-500">
                <span x-text="busy ? 'Menyimpan…' : 'Simpan'"></span>
            </button>
        </div>
    </section>
</form>
@endsection