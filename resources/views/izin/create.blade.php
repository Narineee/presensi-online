@extends('layouts.mobile')
@section('title', 'Pengajuan Ketidakhadiran')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Pengajuan Ketidakhadiran</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Ajukan ketidakhadiran magang</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<form method="POST" action="{{ route('izin.store') }}" enctype="multipart/form-data" class="space-y-4"
      x-data="{ jenis: @js(old('jenis_izin', '')), mulai: @js(old('tanggal_mulai', '')), selesai: @js(old('tanggal_selesai', '')), alasan: @js(old('alasan', '')), file: false, busy: false }"
      @submit="busy = true">
    @csrf

    {{-- Jenis permohonan --}}
    <div id="step-card-1">
        <span id="tracker-step-1" class="hidden"></span>
        <p class="mb-2 text-sm sm:text-base font-extrabold text-slate-900">Jenis permohonan</p>
        <div class="grid grid-cols-3 gap-2" role="radiogroup">
            @foreach (['sakit' => 'Sakit', 'izin' => 'Izin', 'cuti' => 'Cuti'] as $k => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="jenis_izin" value="{{ $k }}" x-model="jenis" class="peer sr-only" required>
                    <span class="block rounded-2xl border py-3 text-center text-xs sm:text-sm font-extrabold transition peer-focus-visible:ring-2 peer-focus-visible:ring-brand peer-focus-visible:ring-offset-2 truncate px-1"
                          :class="jenis === '{{ $k }}' ? 'border-brand bg-brand text-white shadow-sm' : 'border-slate-200/90 bg-white/80 backdrop-blur-sm text-slate-700 hover:bg-white'">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <section class="space-y-4 rounded-3xl glass-card p-5 shadow-sm">
        <div id="step-card-2" class="grid grid-cols-2 gap-2.5" :class="!jenis ? 'opacity-40 select-none' : ''">
            <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Mulai
                <input type="date" name="tanggal_mulai" x-model="mulai" :disabled="!jenis" required class="{{ $in }}">
            </label>
            <label class="block text-xs sm:text-sm font-bold text-slate-800 min-w-0">Selesai
                <input type="date" name="tanggal_selesai" x-model="selesai" :min="mulai" :disabled="!jenis" required class="{{ $in }}">
            </label>
        </div>

        <div id="step-card-3" :class="!(mulai && selesai && selesai >= mulai) ? 'opacity-40 select-none' : ''">
            <label class="block text-xs sm:text-sm font-bold text-slate-800">Alasan/keterangan tidak hadir
                <textarea name="alasan" rows="5" minlength="5" x-model="alasan" :disabled="!(mulai && selesai && selesai >= mulai)" required class="{{ $in }}" placeholder="Jelaskan alasan ketidakhadiran Anda"></textarea>
            </label>
        </div>

        <div id="step-card-4" :class="alasan.trim().length < 5 ? 'opacity-40 select-none' : ''">
            <span id="tracker-step-4" class="hidden"></span>
            <label class="block text-xs sm:text-sm font-bold text-slate-800">
                <span class="flex items-center justify-between gap-1 mb-1">
                    <span class="truncate">Lampiran Bukti (Surat Dokter / Dokumen Pendukung)</span>
                    <span class="text-[11px] font-bold text-rose-600 shrink-0">Wajib dilampirkan</span>
                </span>
                <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" required :disabled="alasan.trim().length < 5" @change="file = $event.target.files.length > 0"
                       class="{{ $in }} file:mr-3 file:rounded-xl file:border-0 file:bg-brand-soft file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand">
                <span class="mt-1.5 block text-[11px] font-normal text-slate-500">Format yang didukung: JPG, PNG, atau PDF. Maksimal 2MB.</span>
            </label>
        </div>
    </section>

    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <p class="break-words">Pengajuan akan diperiksa pembimbing. Status <b>Menunggu persetujuan</b> tidak dihitung alpa.</p>
    </div>

    <button type="submit" id="btn-submit-izin"
            :disabled="busy || !(jenis && mulai && selesai && selesai >= mulai && alasan.trim().length >= 5 && file)"
            class="btn-brand-primary flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
        <span x-show="busy">Mengirim…</span>
        <span x-show="!busy && !(jenis && mulai && selesai && selesai >= mulai && alasan.trim().length >= 5 && file)">Lengkapi Semua Langkah</span>
        <span x-show="!busy && (jenis && mulai && selesai && selesai >= mulai && alasan.trim().length >= 5 && file)">➤ Kirim pengajuan</span>
    </button>
</form>

{{-- Pengajuan terakhir --}}
@if ($terakhir->isNotEmpty())
    <h2 class="mt-6 mb-2 text-sm font-extrabold text-slate-900">Pengajuan terakhir</h2>
    <ul class="space-y-2.5">
        @foreach ($terakhir as $item)
            @php
                $st = match ($item->status_approval) {
                    'disetujui' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200', 'Disetujui oleh '.$item->nama_validator],
                    'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200', 'Ditolak oleh '.$item->nama_validator],
                    default => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200', 'Menunggu persetujuan pembimbing'],
                };
            @endphp
            <li>
                <a href="{{ route('izin.show', $item->id) }}" class="flex items-center justify-between gap-3 rounded-2xl glass-card px-4 py-3 shadow-2xs hover:border-brand/40 transition">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs sm:text-sm font-bold text-slate-900">{{ ucfirst($item->jenis_izin) }} {{ \Illuminate\Support\Str::limit($item->alasan, 28) }} - {{ $item->tanggal_mulai->locale('id')->isoFormat('D MMM') }}</p>
                        <p class="truncate text-[11px] text-slate-500 mt-0.5">{{ $st[2] }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $st[1] }}">{{ $st[0] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
@endsection