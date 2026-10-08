@extends('layouts.mobile')
@section('title', 'Rekap Presensi')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-2.5 text-xs font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Rekap">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Rekap Presensi Anda</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- Filter Card --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <form method="GET" action="{{ route('presensi.riwayat') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-2.5">
                <div class="min-w-0">
                    <label class="block text-xs font-bold text-slate-700 truncate">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="{{ $in }}">
                </div>

                <div class="min-w-0">
                    <label class="block text-xs font-bold text-slate-700 truncate">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="{{ $in }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Mode Kerja</label>
                <select name="mode_kerja" class="{{ $in }}">
                    <option value="">Semua Mode Kerja</option>
                    <option value="onsite" {{ $modeKerja === 'onsite' ? 'selected' : '' }}>Onsite (Kantor)</option>
                    <option value="wfh" {{ $modeKerja === 'wfh' ? 'selected' : '' }}>WFH (Rumah)</option>
                    <option value="tugas_luar" {{ $modeKerja === 'tugas_luar' ? 'selected' : '' }}>Tugas Luar (Dinas)</option>
                </select>
            </div>

            <div class="pt-1 flex gap-2">
                <button type="submit" class="btn-brand-primary flex-1 rounded-2xl py-3 text-center text-xs font-extrabold cursor-pointer">
                    Terapkan Filter
                </button>
                @if($tanggalAwal || $tanggalSelesai || $modeKerja)
                    <a href="{{ route('presensi.riwayat') }}" class="px-4 py-3 rounded-2xl bg-white border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center justify-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Riwayat Presensi Saya --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Riwayat Presensi Saya</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $presensiList->total() }} catatan</span>
        </div>

        @if($presensiList->isEmpty())
            <div class="rounded-2xl glass-subcard p-8 text-center">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada catatan presensi</p>
                <p class="text-xs text-slate-500 mt-0.5">Tidak ditemukan data presensi sesuai periode atau filter yang dipilih.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($presensiList as $item)
                    @php
                        $jamMasuk = $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : '-';
                        $jamKeluar = $item->jam_keluar ? substr($item->jam_keluar, 0, 5) : '-';
                        $isTerlambat = $item->jam_masuk && $item->jam_masuk > '08:00:00';
                        $modeBadge = match($item->mode_kerja) {
                            'wfh' => ['WFH', 'bg-blue-50 text-blue-700 border-blue-200'],
                            'tugas_luar' => ['Tugas Luar', 'bg-purple-50 text-purple-700 border-purple-200'],
                            default => ['Onsite', 'bg-emerald-50 text-emerald-700 border-emerald-200']
                        };
                    @endphp
                    <div class="rounded-2xl glass-subcard p-4 shadow-2xs space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-extrabold text-slate-900 truncate">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $modeBadge[1] }}">
                                    {{ $modeBadge[0] }}
                                </span>
                                @if($isTerlambat)
                                    <span class="rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 text-[10px] font-bold">
                                        Terlambat
                                    </span>
                                @else
                                    <span class="rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold">
                                        Tepat Waktu
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60 text-xs">
                            <div class="bg-white/80 rounded-xl p-2.5 border border-white">
                                <span class="text-[11px] text-slate-500 block">Jam Masuk</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $jamMasuk }} WITA</span>
                            </div>
                            <div class="bg-white/80 rounded-xl p-2.5 border border-white">
                                <span class="text-[11px] text-slate-500 block">Jam Keluar</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $jamKeluar }} WITA</span>
                            </div>
                        </div>

                        @if($item->mode_kerja === 'tugas_luar' && $item->pengajuanTugasLuar)
                            <div class="rounded-xl bg-purple-50/80 p-2 text-xs text-purple-900 border border-purple-100">
                                📍 Tugas Luar: <b>{{ $item->pengajuanTugasLuar->tujuan }}</b>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($presensiList->hasPages())
                <div class="mt-4 pt-2">
                    {{ $presensiList->links() }}
                </div>
            @endif
        @endif
    </section>

    {{-- Cetak Action Button --}}
    <div class="pt-1">
        <a href="{{ route('presensi.cetak', request()->all()) }}" target="_blank"
           class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-sm font-extrabold text-slate-800 shadow-xs transition active:scale-[.99] cursor-pointer">
            <span>🖨️ Cetak Rekapitulasi</span>
        </a>
    </div>

</div>
@endsection
