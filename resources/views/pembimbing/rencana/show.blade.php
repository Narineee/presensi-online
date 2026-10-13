@extends('layouts.pembimbing-mobile')
@section('title', 'Detail Rencana Kerja Binaan')

@section('content')
@php
    $stBadge = match($rencana->status) {
        'aktif' => ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'selesai' => ['Selesai', 'bg-blue-50 text-blue-700 border-blue-200'],
        default => ['Nonaktif', 'bg-slate-100 text-slate-600 border-slate-200'],
    };
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.rencana.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base sm:text-lg font-extrabold leading-tight text-slate-900 truncate">Detail Rencana Kerja</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $rencana->magang?->nama_lengkap ?? 'Peserta' }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- Detail Card --}}
    <section class="rounded-3xl bg-white p-5 border border-slate-200 shadow-xs space-y-4">
        <div>
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block">
                        {{ $rencana->magang?->divisi?->nama_divisi ?? '-' }}
                    </span>
                    <h2 class="text-lg font-extrabold text-slate-900 mt-1 leading-snug break-words">
                        {{ $rencana->judul }}
                    </h2>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold border {{ $stBadge[1] }}">
                    {{ $stBadge[0] }}
                </span>
            </div>
            <div class="mt-3 border-t border-slate-100"></div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Peserta Magang:</span>
                <span class="font-bold text-slate-900 block mt-0.5 truncate">{{ $rencana->magang?->nama_lengkap }}</span>
                <span class="text-[11px] text-slate-500 block truncate">{{ $rencana->magang?->no_induk }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Tanggal Dibuat / Diperbarui:</span>
                <span class="font-bold text-slate-900 block mt-0.5">
                    {{ $rencana->created_at->locale('id')->isoFormat('D MMMM Y') }}
                </span>
                <span class="text-[11px] text-slate-500 block">
                    Update: {{ $rencana->updated_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} WITA
                </span>
            </div>
        </div>

        @if($rencana->deskripsi)
            <div>
                <span class="text-xs font-bold text-slate-700 block mb-1">Deskripsi / Catatan Rencana:</span>
                <div class="whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-xs font-medium leading-relaxed text-slate-800 border border-slate-200 break-words">
{{ $rencana->deskripsi }}
                </div>
            </div>
        @endif
    </section>

    {{-- Riwayat Progres Aktivitas --}}
    <section class="rounded-3xl bg-white p-5 border border-slate-200 shadow-xs space-y-3">
        <h3 class="text-sm font-extrabold text-slate-900">
            Riwayat Progres Aktivitas ({{ $riwayatAktivitas->total() }})
        </h3>
        <p class="text-xs text-slate-500">Daftar laporan aktivitas harian peserta yang mencantumkan rencana kerja ini.</p>

        @if($riwayatAktivitas->isEmpty())
            <div class="rounded-2xl bg-slate-50 p-6 text-center text-xs text-slate-500 border border-slate-200">
                Belum ada laporan progres aktivitas untuk rencana kerja ini.
            </div>
        @else
            <div class="space-y-3 pt-1">
                @foreach($riwayatAktivitas as $detail)
                    @php
                        $akt = $detail->aktivitas;
                        $aktBadge = match($akt?->status) {
                            'approve' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'revisi' => ['Perlu Revisi', 'bg-rose-50 text-rose-700 border-rose-200'],
                            default => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200'],
                        };
                    @endphp
                    <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-extrabold text-slate-800">
                                📅 {{ $akt?->tanggal ? \Carbon\Carbon::parse($akt->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $aktBadge[1] }}">
                                {{ $aktBadge[0] }}
                            </span>
                        </div>
                        <div class="whitespace-pre-line rounded-xl bg-white p-3 text-xs leading-relaxed text-slate-800 border border-slate-100 break-words">
{{ $detail->progres }}
                        </div>
                    </div>
                @endforeach
            </div>

            @if($riwayatAktivitas->hasPages())
                <div class="mt-4 pt-2">
                    {{ $riwayatAktivitas->links() }}
                </div>
            @endif
        @endif
    </section>

</div>
@endsection
