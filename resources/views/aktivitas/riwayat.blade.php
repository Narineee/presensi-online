@extends('layouts.mobile')
@section('title', 'Rekap Aktivitas')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25 transition';
@endphp

<header class="-mx-4 -mt-5 mb-5 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <div class="flex items-center gap-3">
        <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali ke Rekap">
            <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-base font-extrabold leading-tight text-slate-900">Rekapitulasi & Cetak Dokumen</h1>
            <p class="text-xs text-slate-500">Rekap Aktivitas Harian Anda</p>
        </div>
    </div>
    <a href="{{ route('aktivitas.create') }}" class="rounded-xl bg-brand px-3 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-dark transition flex items-center gap-1">
        <span>+ Catat</span>
    </a>
</header>

<div class="space-y-5">

    {{-- 4 Stat Pills (Sesuai PRD rekap-aktivitas (1).png) --}}
    <div class="grid grid-cols-4 gap-2">
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">TOTAL AKTIVITAS</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">DISETUJUI</p>
            <p class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['approve'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">MENUNGGU</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">PERLU REVISI</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ $stats['revisi'] }}</p>
        </div>
    </div>

    {{-- Filter Card (Sesuai PRD rekap-aktivitas (1).png) --}}
    <section class="rounded-3xl bg-[#ECEEEF]/80 p-5 ring-1 ring-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('aktivitas.riwayat') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="{{ $in }}">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Tanggal Awal</label>
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
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kata kunci uraian..." class="flex-1 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25 transition">
                <button type="submit" class="rounded-xl bg-slate-600 hover:bg-slate-700 px-5 py-2.5 text-xs font-bold text-white transition active:scale-[.99] shadow-xs">
                    Cari
                </button>
                @if($tanggalAwal || $tanggalSelesai || $status || $q)
                    <a href="{{ route('aktivitas.riwayat') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition" title="Reset filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Riwayat Aktivitas Saya --}}
    <section class="rounded-3xl bg-[#ECEEEF]/80 p-5 ring-1 ring-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Riwayat Aktivitas Saya</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $aktivitasList->total() }} catatan</span>
        </div>

        @if($aktivitasList->isEmpty())
            <div class="rounded-2xl bg-white p-8 text-center border border-slate-200/80">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada catatan aktivitas</p>
                <p class="text-xs text-slate-500 mt-0.5">Mulai dengan mencatat aktivitas harian pekerjaan Anda.</p>
                <a href="{{ route('aktivitas.create') }}" class="mt-3 inline-block rounded-xl bg-brand px-4 py-2 text-xs font-bold text-white shadow-xs">
                    Catat Aktivitas Baru
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($aktivitasList as $item)
                    @php
                        $st = match($item->status) {
                            'approve' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                            'revisi' => ['Perlu Revisi', 'bg-rose-100 text-rose-800 border-rose-200'],
                            default => ['Menunggu Validasi', 'bg-amber-100 text-amber-800 border-amber-200'],
                        };
                        $waktuText = ($item->waktu_mulai && $item->waktu_selesai)
                            ? substr($item->waktu_mulai, 0, 5) . ' - ' . substr($item->waktu_selesai, 0, 5)
                            : '-';
                    @endphp
                    <div class="rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-[11px] font-bold text-slate-500">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </p>
                                <h3 class="text-sm font-extrabold text-slate-900 mt-0.5">
                                    {{ $item->judul ?? $item->pekerjaan->judul ?? 'Aktivitas Harian' }}
                                </h3>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $st[1] }}">
                                {{ $st[0] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                🕒 {{ $waktuText }}
                            </span>
                            @if($item->pekerjaan && $item->pekerjaan->isProyek())
                                <span class="text-slate-400">•</span>
                                <span class="font-bold text-brand">Progres: {{ $item->progress }}%</span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 rounded-xl p-2.5">
                            {{ $item->isi }}
                        </p>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                            <span class="text-[11px] text-slate-400">
                                {{ $item->validator ? 'Divalidasi oleh ' . ($item->validator->pembimbing->nama_lengkap ?? $item->validator->name) : 'Menunggu validasi' }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('aktivitas.show', $item->id) }}" class="rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition">
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

    {{-- Cetak Rekapitulasi (Sesuai PRD rekap-aktivitas (1).png) --}}
    <div class="pt-2">
        <a href="{{ route('aktivitas.cetak', request()->all()) }}" target="_blank"
           class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-600 hover:bg-slate-700 py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99]">
            <span>🖨️ Cetak Rekapitulasi</span>
        </a>
    </div>

</div>
@endsection
