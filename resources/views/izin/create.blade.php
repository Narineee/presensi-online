@extends('layouts.mobile')
@section('title', 'Pengajuan Ketidakhadiran')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Pengajuan Ketidakhadiran</h1>
        <p class="text-xs text-slate-500">Ajukan ketidakhadiran magang</p>
    </div>
</header>

<form method="POST" action="{{ route('izin.store') }}" enctype="multipart/form-data" class="space-y-4"
      x-data="{ jenis: @js(old('jenis_izin', '')), mulai: @js(old('tanggal_mulai', '')), selesai: @js(old('tanggal_selesai', '')), alasan: @js(old('alasan', '')), file: false, busy: false }"
      @submit="busy = true">
    @csrf

    {{-- Jenis permohonan --}}
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
                <input type="date" name="tanggal_selesai" x-model="selesai" :min="mulai" required class="{{ $in }}">
            </label>
        </div>

        <label class="block text-sm font-bold">Alasan/keterangan tidak hadir
            <textarea name="alasan" rows="5" minlength="5" x-model="alasan" required class="{{ $in }}" placeholder="Jelaskan alasan ketidakhadiran Anda"></textarea>
        </label>

        <label class="block text-sm font-bold">Lampiran/Dokumen Pendukung
            <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" required @change="file = $event.target.files.length > 0"
                   class="{{ $in }} file:mr-3 file:rounded-lg file:border-0 file:bg-slate-200 file:px-3 file:py-1.5 file:text-sm file:font-bold file:text-brand-ink">
            <span class="mt-1.5 block text-[11px] font-normal text-slate-500">Format yang didukung: JPG, PNG, atau PDF. Maksimal 2MB.</span>
        </label>
    </section>

    <div class="flex gap-3 rounded-2xl border border-brand/30 bg-brand-soft/60 p-3.5 text-xs leading-relaxed text-slate-700">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-brand text-[11px] font-bold text-brand">i</span>
        <p>Pengajuan akan diperiksa pembimbing. Status <b>Menunggu persetujuan</b> tidak dihitung alpa.</p>
    </div>

    <button type="submit"
            :disabled="busy || !(jenis && mulai && selesai && selesai >= mulai && alasan.trim().length >= 5 && file)"
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
        <span x-text="busy ? 'Mengirim…' : '➤ Kirim pengajuan'"></span>
    </button>
</form>

{{-- Pengajuan terakhir --}}
@if ($terakhir->isNotEmpty())
    <h2 class="mt-6 mb-2 text-sm font-extrabold">Pengajuan terakhir</h2>
    <ul class="space-y-2">
        @foreach ($terakhir as $item)
            @php
                $st = match ($item->status_approval) {
                    'disetujui' => ['Disetujui', 'bg-emerald-100 text-emerald-800', 'Disetujui oleh '.$item->nama_validator],
                    'ditolak' => ['Ditolak', 'bg-rose-100 text-rose-800', 'Ditolak oleh '.$item->nama_validator],
                    default => ['Menunggu', 'bg-amber-100 text-amber-800', 'Menunggu persetujuan pembimbing'],
                };
            @endphp
            <li>
                <a href="{{ route('izin.show', $item->id) }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-200 transition hover:ring-brand/40">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold">{{ ucfirst($item->jenis_izin) }} {{ \Illuminate\Support\Str::limit($item->alasan, 28) }} - {{ $item->tanggal_mulai->locale('id')->isoFormat('D MMM') }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $st[2] }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $st[1] }}">{{ $st[0] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
@endsection