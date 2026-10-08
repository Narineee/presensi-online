@extends('layouts.mobile')
@section('title', 'Detail Aktivitas Harian')

@section('content')
@php
    $badge = match ($aktivitas->status) {
        'approve' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'revisi' => ['Perlu Revisi', 'bg-rose-50 text-rose-700 border-rose-200'],
        default => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200'],
    };
    $waktuText = ($aktivitas->waktu_mulai && $aktivitas->waktu_selesai)
        ? substr((string) $aktivitas->waktu_mulai, 0, 5) . ' - ' . substr((string) $aktivitas->waktu_selesai, 0, 5) . ' WITA'
        : null;
    $validatorName = $aktivitas->nama_validator ?? ($aktivitas->validator->pembimbing->nama_lengkap ?? ($aktivitas->validator->name ?? null));
    $feedbackNote = $aktivitas->catatan_validasi ?? ($aktivitas->catatan_revisi ?? null);
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('aktivitas.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Rekap Aktivitas">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Detail aktivitas harian</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">
    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">

        {{-- Tanggal Pelaksanaan & Status --}}
        <div>
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 truncate">TANGGAL PELAKSANAAN</p>
                    <p class="text-base font-extrabold text-slate-900 mt-0.5 truncate">
                        {{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    @if($waktuText)
                        <p class="text-xs text-slate-600 mt-0.5 font-medium truncate">🕒 {{ $waktuText }}</p>
                    @endif
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold border {{ $badge[1] }} shrink-0">
                    {{ $badge[0] }}
                </span>
            </div>
            <div class="mt-3 border-t border-slate-200/80"></div>
        </div>

        {{-- Capaian Progres Pekerjaan --}}
        <div>
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                <span class="text-slate-700">Capaian Progres Pekerjaan</span>
                <span class="text-brand font-black">{{ $aktivitas->progress }}%</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200/80 p-0.5">
                <div class="h-full rounded-full bg-brand transition-all duration-300" style="width: {{ $aktivitas->progress }}%"></div>
            </div>
        </div>

        {{-- Pekerjaan yang Diberikan --}}
        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                PEKERJAAN YANG DIBERIKAN
            </label>
            <div class="w-full rounded-2xl glass-subcard px-4 py-3 text-sm font-bold text-slate-900 border border-white break-words">
                {{ $aktivitas->pekerjaan?->judul ?? ($aktivitas->judul ?? 'Aktivitas Harian') }}
            </div>
        </div>

        {{-- Uraian Aktivitas yang Diberikan --}}
        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                URAIAN AKTIVITAS YANG DIBERIKAN
            </label>
            <div class="w-full min-h-[120px] whitespace-pre-line rounded-2xl glass-subcard p-4 text-xs font-medium leading-relaxed text-slate-800 border border-white break-words">
{{ $aktivitas->isi }}
            </div>
        </div>

        {{-- Informasi Validasi Pembimbing --}}
        <div>
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                INFORMASI VALIDASI PEMBIMBING
            </label>
            <div class="rounded-2xl glass-subcard p-4 border border-white space-y-3">
                <div class="text-xs">
                    <span class="text-slate-500 font-medium block">Divalidasi oleh:</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block truncate">
                        {{ $validatorName ?: 'Belum divalidasi' }}
                    </span>
                    @if($aktivitas->validated_at)
                        <span class="text-[11px] text-slate-400 block mt-0.5 truncate">
                            Waktu validasi: {{ $aktivitas->validated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA
                        </span>
                    @endif
                </div>

                <div class="border-t border-slate-200/60"></div>

                <div class="text-xs">
                    <span class="text-slate-500 font-medium block">Catatan Feedback:</span>
                    <p class="font-medium text-slate-800 mt-1 leading-relaxed whitespace-pre-line bg-white/80 rounded-xl p-3 border border-white break-words">
                        {{ $feedbackNote ?: ($aktivitas->status === 'pending' ? 'Menunggu peninjauan dan masukan dari pembimbing lapangan.' : 'Tidak ada catatan tambahan.') }}
                    </p>
                </div>
            </div>
        </div>

    </section>

    {{-- Tombol Tindakan Edit/Hapus jika masih bisa diedit --}}
    @if ($aktivitas->canBeEdited())
        <div class="flex gap-2.5 pt-1">
            <a href="{{ route('aktivitas.edit', $aktivitas->id) }}" class="btn-brand-primary flex-1 rounded-2xl py-3.5 text-center text-sm font-extrabold text-white shadow-sm cursor-pointer">
                ✏️ Edit Aktivitas Ini
            </a>
            <form method="POST" action="{{ route('aktivitas.destroy', $aktivitas->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan aktivitas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-2xl border border-rose-300 bg-rose-50/90 hover:bg-rose-100 px-5 py-3.5 text-sm font-bold text-rose-700 shadow-2xs transition cursor-pointer">
                    Hapus
                </button>
            </form>
        </div>
    @endif
</div>
@endsection