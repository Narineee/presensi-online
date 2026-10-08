@extends('layouts.mobile')
@section('title', 'Rekap Aktivitas')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-2.5 text-xs font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <div class="flex items-center gap-3 min-w-0 flex-1 mr-2">
        <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Rekap">
            <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Rekapitulasi & Cetak Dokumen</h1>
            <p class="text-xs text-slate-500 font-medium truncate">Rekap Aktivitas Harian Anda</p>
        </div>
    </div>
    <a href="{{ route('aktivitas.create') }}" class="btn-brand-primary shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-xs flex items-center gap-1 cursor-pointer">
        <span>+ Catat</span>
    </a>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- 4 Stat Pills --}}
    <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
        <div class="rounded-2xl glass-card p-2 sm:p-2.5 text-center shadow-2xs min-w-0 overflow-hidden flex flex-col justify-center items-center">
            <p class="text-[8px] sm:text-[9px] font-extrabold uppercase text-slate-500 tracking-tight leading-tight block text-center break-words">TOTAL AKTIVITAS</p>
            <p class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5 truncate">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl glass-card p-2 sm:p-2.5 text-center shadow-2xs min-w-0 overflow-hidden flex flex-col justify-center items-center">
            <p class="text-[8px] sm:text-[9px] font-extrabold uppercase text-slate-500 tracking-tight leading-tight block text-center break-words">DISETUJUI</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5 truncate">{{ $stats['approve'] }}</p>
        </div>
        <div class="rounded-2xl glass-card p-2 sm:p-2.5 text-center shadow-2xs min-w-0 overflow-hidden flex flex-col justify-center items-center">
            <p class="text-[8px] sm:text-[9px] font-extrabold uppercase text-slate-500 tracking-tight leading-tight block text-center break-words">MENUNGGU</p>
            <p class="text-xl sm:text-2xl font-black text-amber-600 mt-0.5 truncate">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-2xl glass-card p-2 sm:p-2.5 text-center shadow-2xs min-w-0 overflow-hidden flex flex-col justify-center items-center">
            <p class="text-[8px] sm:text-[9px] font-extrabold uppercase text-slate-500 tracking-tight leading-tight block text-center break-words">PERLU REVISI</p>
            <p class="text-xl sm:text-2xl font-black text-rose-600 mt-0.5 truncate">{{ $stats['revisi'] }}</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <form method="GET" action="{{ route('aktivitas.riwayat') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-2.5">
                <div class="min-w-0">
                    <label class="block text-xs font-bold text-slate-700 truncate">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="{{ $in }}">
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-bold text-slate-700 truncate">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="{{ $in }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Status</label>
                <select name="status" class="{{ $in }}">
                    <option value="">Semua Status</option>
                    <option value="approve" {{ $status === 'approve' ? 'selected' : '' }}>Disetujui</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Validasi</option>
                    <option value="revisi" {{ $status === 'revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kata kunci uraian..." class="flex-1 rounded-2xl border border-slate-300 bg-white/90 px-4 py-2.5 text-xs font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs min-w-0">
                <button type="submit" class="btn-brand-primary shrink-0 rounded-2xl px-5 py-2.5 text-xs font-bold text-white shadow-xs cursor-pointer">
                    Cari
                </button>
                @if($tanggalAwal || $tanggalSelesai || $status || $q)
                    <a href="{{ route('aktivitas.riwayat') }}" class="shrink-0 rounded-2xl bg-white border border-slate-300 px-3.5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition" title="Reset filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Riwayat Aktivitas Saya --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Riwayat Aktivitas Saya</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $aktivitasList->total() }} catatan</span>
        </div>

        @if($aktivitasList->isEmpty())
            <div class="rounded-2xl glass-subcard p-8 text-center">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada catatan aktivitas</p>
                <p class="text-xs text-slate-500 mt-0.5">Mulai dengan mencatat aktivitas harian pekerjaan Anda.</p>
                <a href="{{ route('aktivitas.create') }}" class="btn-brand-primary mt-3 inline-block rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer">
                    Catat Aktivitas Baru
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($aktivitasList as $item)
                    @php
                        $st = match($item->status) {
                            'approve' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'revisi' => ['Perlu Revisi', 'bg-rose-50 text-rose-700 border-rose-200'],
                            default => ['Menunggu Validasi', 'bg-amber-50 text-amber-700 border-amber-200'],
                        };
                        $waktuText = ($item->waktu_mulai && $item->waktu_selesai)
                            ? substr($item->waktu_mulai, 0, 5) . ' - ' . substr($item->waktu_selesai, 0, 5)
                            : '-';
                    @endphp
                    <div class="rounded-2xl glass-subcard p-4 shadow-2xs space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold text-slate-500 truncate">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </p>
                                <h3 class="text-sm font-extrabold text-slate-900 mt-0.5 truncate">
                                    {{ $item->judul ?? $item->pekerjaan->judul ?? 'Aktivitas Harian' }}
                                </h3>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $st[1] }}">
                                {{ $st[0] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                🕒 {{ $waktuText }}
                            </span>
                            @if($item->pekerjaan && $item->pekerjaan->isProyek())
                                <span class="text-slate-300">•</span>
                                <span class="font-extrabold text-brand">Progres: {{ $item->progress }}%</span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed bg-white/80 p-2.5 rounded-xl border border-white break-words">
                            {{ $item->isi }}
                        </p>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <span class="text-[11px] text-slate-500 truncate mr-2">
                                {{ $item->validator ? 'Divalidasi oleh ' . ($item->validator->pembimbing->nama_lengkap ?? $item->validator->name) : 'Menunggu validasi' }}
                            </span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('aktivitas.show', $item->id) }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition">
                                    Aksi
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($aktivitasList->hasPages())
                <div class="mt-4 pt-2">
                    {{ $aktivitasList->links() }}
                </div>
            @endif
        @endif
    </section>

    {{-- Cetak Rekapitulasi --}}
    <div class="pt-1">
        <a href="{{ route('aktivitas.cetak', request()->all()) }}" target="_blank"
           class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-sm font-extrabold text-slate-800 shadow-xs transition active:scale-[.99] cursor-pointer">
            <span>🖨️ Cetak Rekapitulasi</span>
        </a>
    </div>

</div>
@endsection
