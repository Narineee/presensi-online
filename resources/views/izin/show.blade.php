@extends('layouts.mobile')
@section('title', 'Detail Pengajuan')

@section('content')
@php
    $badge = match ($izin->status_approval) {
        'disetujui' => ['Disetujui pembimbing', 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
        'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-200', 'bg-rose-500'],
        default => ['Menunggu persetujuan', 'bg-amber-50 text-amber-700 ring-amber-200', 'bg-amber-500'],
    };
    $isImage = $izin->bukti_file && in_array(strtolower(pathinfo($izin->bukti_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Detail pengajuan</h1>
        <p class="text-xs text-slate-500">{{ ucfirst($izin->jenis_izin) }} · {{ $izin->jumlah_hari }} hari</p>
    </div>
</header>

<div class="space-y-4">
    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $badge[1] }}">
            <span class="h-2 w-2 rounded-full {{ $badge[2] }}"></span>{{ $badge[0] }}
        </span>

        <dl class="mt-4 space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-3">
                <div><dt class="text-xs text-slate-500">Mulai</dt><dd class="font-bold">{{ $izin->tanggal_mulai->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
                <div><dt class="text-xs text-slate-500">Selesai</dt><dd class="font-bold">{{ $izin->tanggal_selesai->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Alasan/keterangan</dt>
                <dd class="mt-1 whitespace-pre-line rounded-2xl bg-slate-50 p-4 leading-relaxed ring-1 ring-slate-200">{{ $izin->alasan }}</dd>
            </div>
            <div>
                <dt class="mb-1 text-xs text-slate-500">Lampiran</dt>
                @if ($izin->bukti_file_url)
                    @if ($isImage)
                        <img src="{{ $izin->bukti_file_url }}" alt="Lampiran" class="mb-2 max-h-72 w-full rounded-2xl bg-slate-100 object-contain ring-1 ring-slate-200">
                    @endif
                    <a href="{{ $izin->bukti_file_url }}" target="_blank" class="text-sm font-bold text-brand underline">Buka berkas ({{ basename($izin->bukti_file) }})</a>
                @else
                    <dd class="text-xs italic text-slate-400">Tidak ada lampiran.</dd>
                @endif
            </div>
        </dl>
    </section>

    @if ($izin->status_approval !== 'pending')
        <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
            <h2 class="text-sm font-extrabold">Verifikasi pembimbing</h2>
            <p class="mt-2 text-xs text-slate-500">
                Oleh <b class="text-brand-ink">{{ $izin->nama_validator }}</b>
                · {{ $izin->validated_at ? $izin->validated_at->isoFormat('D MMM Y, HH:mm') : '-' }}
            </p>
            <p class="mt-2 text-sm">{{ $izin->status_approval === 'disetujui' ? 'Pengajuan disetujui dan kehadiran pada rentang tanggal ini tidak dihitung alpa.' : 'Pengajuan ini tidak disetujui oleh pembimbing.' }}</p>
        </section>
    @endif

    @if ($izin->canBeEdited())
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('izin.edit', $izin->id) }}" class="rounded-2xl bg-brand py-3.5 text-center text-sm font-extrabold text-white">Edit pengajuan</a>
            <form method="POST" action="{{ route('izin.destroy', $izin->id) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                @csrf @method('DELETE')
                <button class="w-full rounded-2xl border border-rose-300 py-3.5 text-sm font-extrabold text-rose-700">Batalkan</button>
            </form>
        </div>
    @else
        <p class="rounded-2xl bg-slate-100 px-4 py-3 text-center text-xs text-slate-500">Pengajuan yang sudah diverifikasi tidak dapat diubah atau dibatalkan.</p>
    @endif
</div>
@endsection