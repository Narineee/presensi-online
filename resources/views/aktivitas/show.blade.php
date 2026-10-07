@extends('layouts.mobile')
@section('title', 'Detail Aktivitas Harian')

@section('content')
@php
    $badge = match ($aktivitas->status) {
        'approve' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-300'],
        'revisi' => ['Perlu Revisi', 'bg-rose-100 text-rose-800 border-rose-300'],
        default => ['Menunggu', 'bg-slate-200 text-slate-800 border-slate-300'],
    };
    $waktuText = ($aktivitas->waktu_mulai && $aktivitas->waktu_selesai)
        ? substr((string) $aktivitas->waktu_mulai, 0, 5) . ' - ' . substr((string) $aktivitas->waktu_selesai, 0, 5) . ' WITA'
        : null;
    $validatorName = $aktivitas->nama_validator ?? ($aktivitas->validator->pembimbing->nama_lengkap ?? ($aktivitas->validator->name ?? null));
    $feedbackNote = $aktivitas->catatan_validasi ?? ($aktivitas->catatan_revisi ?? null);
@endphp

<header class="sticky-top-nav -mx-4 mb-5 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('aktivitas.index') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali ke Rekap Aktivitas">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight text-slate-900">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500">Detail aktivitas harian</p>
    </div>
</header>

<div class="space-y-4">
    {{-- Main Detail Card (Sesuai PRD detail-aktivitas (1).png) --}}
    <section class="rounded-3xl bg-[#ECEEEF]/90 p-5 ring-1 ring-slate-300/80 shadow-xs space-y-4">

        {{-- Tanggal Pelaksanaan & Status --}}
        <div>
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">TANGGAL PELAKSANAAN</p>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5">
                        {{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    @if($waktuText)
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">🕒 {{ $waktuText }}</p>
                    @endif
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold border {{ $badge[1] }}">
                    {{ $badge[0] }}
                </span>
            </div>
            <div class="mt-3 border-t border-slate-300/80"></div>
        </div>

        {{-- Capaian Progres Pekerjaan --}}
        <div>
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                <span class="text-slate-700 font-semibold">Capaian Progres Pekerjaan</span>
                <span class="text-brand font-extrabold">{{ $aktivitas->progress }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-300">
                <div class="h-full rounded-full bg-brand transition-all duration-300" style="width: {{ $aktivitas->progress }}%"></div>
            </div>
        </div>

        {{-- Pekerjaan yang Diberikan --}}
        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                PEKERJAAN YANG DIBERIKAN
            </label>
            <div class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-xs">
                {{ $aktivitas->pekerjaan?->judul ?? ($aktivitas->judul ?? 'Aktivitas Harian') }}
            </div>
        </div>

        {{-- Uraian Aktivitas yang Diberikan --}}
        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                URAIAN AKTIVITAS YANG DIBERIKAN
            </label>
            <div class="w-full min-h-[140px] whitespace-pre-line rounded-2xl border border-slate-300 bg-white p-4 text-xs font-normal leading-relaxed text-slate-800 shadow-xs">
{{ $aktivitas->isi }}
            </div>
        </div>

        {{-- Informasi Validasi Pembimbing --}}
        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                INFORMASI VALIDASI PEMBIMBING
            </label>
            <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs space-y-3">
                <div class="text-xs">
                    <span class="text-slate-500 font-medium block">Divalidasi oleh:</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block">
                        {{ $validatorName ?: 'Belum divalidasi' }}
                    </span>
                    @if($aktivitas->validated_at)
                        <span class="text-[11px] text-slate-400 block mt-0.5">
                            Waktu validasi: {{ $aktivitas->validated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA
                        </span>
                    @endif
                </div>

                <div class="border-t border-slate-200"></div>

                <div class="text-xs">
                    <span class="text-slate-500 font-medium block">Catatan Feedback:</span>
                    <p class="font-medium text-slate-800 mt-1 leading-relaxed whitespace-pre-line bg-slate-50 rounded-xl p-3 border border-slate-200/80">
                        {{ $feedbackNote ?: ($aktivitas->status === 'pending' ? 'Menunggu peninjauan dan masukan dari pembimbing lapangan.' : 'Tidak ada catatan tambahan.') }}
                    </p>
                </div>
            </div>
        </div>

    </section>

    {{-- Tombol Tindakan Edit/Hapus jika masih bisa diedit --}}
    @if ($aktivitas->canBeEdited())
        <div class="flex gap-2 pt-1">
            <a href="{{ route('aktivitas.edit', $aktivitas->id) }}" class="flex-1 rounded-2xl bg-brand py-3.5 text-center text-sm font-extrabold text-white shadow-xs hover:bg-brand-dark transition">
                ✏️ Edit Aktivitas Ini
            </a>
            <form method="POST" action="{{ route('aktivitas.destroy', $aktivitas->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan aktivitas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3.5 text-sm font-bold text-rose-700 hover:bg-rose-100 transition">
                    Hapus
                </button>
            </form>
        </div>
    @endif
</div>
@endsection