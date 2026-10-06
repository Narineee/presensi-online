@extends('layouts.mobile')
@section('title', 'Detail Aktivitas')

@section('content')
@php
    $badge = match ($aktivitas->status) {
        'approve' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
        'revisi' => ['Perlu revisi', 'bg-rose-50 text-rose-700 ring-rose-200', 'bg-rose-500'],
        default => ['Menunggu validasi', 'bg-amber-50 text-amber-700 ring-amber-200', 'bg-amber-500'],
    };
    $mulai = substr((string) $aktivitas->waktu_mulai, 0, 5);
    $selesai = substr((string) $aktivitas->waktu_selesai, 0, 5);
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div>
        <h1 class="text-base font-extrabold leading-tight">Detail aktivitas</h1>
        <p class="text-xs text-slate-500">{{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

<div class="space-y-4">
    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $badge[1] }}">
            <span class="h-2 w-2 rounded-full {{ $badge[2] }}"></span>{{ $badge[0] }}
        </span>

        <dl class="mt-4 space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-3">
                <div><dt class="text-xs text-slate-500">Waktu mulai</dt><dd class="font-bold">{{ $mulai ?: '-' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Waktu selesai</dt><dd class="font-bold">{{ $selesai ?: '-' }}</dd></div>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Pekerjaan yang diberikan</dt>
                <dd class="font-bold">{{ $aktivitas->pekerjaan?->judul ?? ($aktivitas->judul ?? '-') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Ringkasan aktivitas</dt>
                <dd class="mt-1 whitespace-pre-line rounded-2xl bg-slate-50 p-4 leading-relaxed ring-1 ring-slate-200">{{ $aktivitas->isi }}</dd>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-xs"><dt class="text-slate-500">Progres capaian</dt><dd class="font-bold text-brand">{{ $aktivitas->progress }}%</dd></div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand" style="width: {{ $aktivitas->progress }}%"></div></div>
            </div>
        </dl>
    </section>

    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <h2 class="text-sm font-extrabold">Validasi pembimbing</h2>
        @if ($aktivitas->status === 'pending')
            <p class="mt-2 text-xs text-slate-500">Belum divalidasi. Mohon menunggu tinjauan dari pembimbing Anda.</p>
        @else
            <p class="mt-2 text-xs text-slate-500">
                Oleh <b class="text-brand-ink">{{ $aktivitas->nama_validator }}</b>
                · {{ $aktivitas->validated_at ? $aktivitas->validated_at->isoFormat('D MMM Y, HH:mm') : '-' }}
            </p>
            @if ($aktivitas->catatan_validasi)
                <p class="mt-3 rounded-2xl p-3.5 text-sm leading-relaxed ring-1 {{ $aktivitas->status === 'approve' ? 'bg-emerald-50 text-emerald-900 ring-emerald-200' : 'bg-amber-50 text-amber-900 ring-amber-200' }}">{{ $aktivitas->catatan_validasi }}</p>
            @endif
        @endif
    </section>

    @if ($aktivitas->canBeEdited())
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('aktivitas.edit', $aktivitas->id) }}" class="rounded-2xl bg-brand py-3.5 text-center text-sm font-extrabold text-white">Edit aktivitas</a>
            <form method="POST" action="{{ route('aktivitas.destroy', $aktivitas->id) }}" onsubmit="return confirm('Hapus catatan aktivitas ini?')">
                @csrf @method('DELETE')
                <button class="w-full rounded-2xl border border-rose-300 py-3.5 text-sm font-extrabold text-rose-700">Hapus</button>
            </form>
        </div>
    @else
        <p class="rounded-2xl bg-slate-100 px-4 py-3 text-center text-xs text-slate-500">Aktivitas yang sudah disetujui tidak dapat diubah atau dihapus.</p>
    @endif
</div>
@endsection