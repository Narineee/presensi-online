@extends('layouts.mobile')
@section('title', 'Detail Pengajuan')

@section('content')
@php
    $badge = match ($izin->status_approval) {
        'disetujui' => ['Disetujui pembimbing', 'bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
        'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-500'],
        default => ['Menunggu persetujuan', 'bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500'],
    };
    $isImage = $izin->bukti_file && in_array(strtolower(pathinfo($izin->bukti_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Detail pengajuan</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ ucfirst($izin->jenis_izin) }} · {{ $izin->jumlah_hari }} hari</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold border {{ $badge[1] }}">
            <span class="h-2 w-2 rounded-full {{ $badge[2] }}"></span>{{ $badge[0] }}
        </span>

        <dl class="mt-4 space-y-4 text-xs sm:text-sm">
            <div class="grid grid-cols-2 gap-2.5">
                <div class="min-w-0"><dt class="text-xs text-slate-500 font-medium truncate">Mulai</dt><dd class="font-bold text-slate-900 truncate mt-0.5">{{ $izin->tanggal_mulai->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
                <div class="min-w-0"><dt class="text-xs text-slate-500 font-medium truncate">Selesai</dt><dd class="font-bold text-slate-900 truncate mt-0.5">{{ $izin->tanggal_selesai->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
            </div>
            <div>
                <dt class="text-xs text-slate-500 font-medium">Alasan/keterangan</dt>
                <dd class="mt-1 whitespace-pre-line rounded-2xl glass-subcard p-4 leading-relaxed text-slate-800 border border-white break-words">{{ $izin->alasan }}</dd>
            </div>
            <div>
                <dt class="mb-1 text-xs text-slate-500 font-medium">Lampiran</dt>
                @if ($izin->bukti_file_url)
                    @if ($isImage)
                        <img src="{{ $izin->bukti_file_url }}" alt="Lampiran" class="mb-2 max-h-72 w-full rounded-2xl bg-white/80 object-contain border border-slate-200/80 shadow-2xs">
                    @endif
                    <a href="{{ $izin->bukti_file_url }}" target="_blank" class="text-xs sm:text-sm font-bold text-brand hover:underline break-words">Buka berkas ({{ basename($izin->bukti_file) }})</a>
                @else
                    <dd class="text-xs italic text-slate-400">Tidak ada lampiran.</dd>
                @endif
            </div>
        </dl>
    </section>

    @if ($izin->status_approval !== 'pending')
        <section class="rounded-3xl glass-card p-5 shadow-sm">
            <h2 class="text-sm font-extrabold text-slate-900">Verifikasi pembimbing</h2>
            <p class="mt-1.5 text-xs text-slate-500">
                Oleh <b class="text-slate-900">{{ $izin->nama_validator }}</b>
                · {{ $izin->validated_at ? $izin->validated_at->isoFormat('D MMM Y, HH:mm') : '-' }}
            </p>
            <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-700 break-words">{{ $izin->status_approval === 'disetujui' ? 'Pengajuan disetujui dan kehadiran pada rentang tanggal ini tidak dihitung alpa.' : 'Pengajuan ini tidak disetujui oleh pembimbing.' }}</p>
        </section>
    @endif

    @if ($izin->canBeEdited())
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('izin.edit', $izin->id) }}" class="btn-brand-primary rounded-2xl py-3.5 text-center text-sm font-extrabold text-white shadow-sm cursor-pointer">Edit pengajuan</a>
            <form method="POST" action="{{ route('izin.destroy', $izin->id) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full rounded-2xl border border-rose-300 bg-rose-50/90 hover:bg-rose-100 py-3.5 text-sm font-bold text-rose-700 shadow-2xs transition cursor-pointer">Batalkan</button>
            </form>
        </div>
    @endif
</div>
@endsection