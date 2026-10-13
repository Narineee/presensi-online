@extends('layouts.mobile')
@section('title', 'Rencana Kerja')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-2.5 text-xs font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <div class="flex items-center gap-3 min-w-0 flex-1 mr-2">
        <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Presensi">
            <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-base sm:text-lg font-extrabold leading-tight text-slate-900 truncate">Rencana Kerja</h1>
            <p class="text-xs text-slate-500 font-medium truncate">Kelola rencana kerja mandiri Anda</p>
        </div>
    </div>
    <a href="{{ route('rencana.create') }}" class="btn-brand-primary shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-xs flex items-center gap-1.5 cursor-pointer">
        <span>+ Tambah</span>
    </a>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- Info Card Rencana Mandiri --}}
    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-900">Rencana Kerja Mandiri</p>
            <p class="text-slate-600 mt-0.5">Rencana kerja dibuat sendiri oleh Anda dan dapat digunakan berulang kali saat mengisi aktivitas harian. Satu rencana kerja bisa memiliki banyak riwayat progres di tanggal berbeda.</p>
        </div>
    </div>

    {{-- Stat Pills --}}
    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-2xl glass-card p-3 text-center shadow-2xs">
            <p class="text-[9px] font-extrabold uppercase text-slate-500 tracking-wider">TOTAL RENCANA</p>
            <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl glass-card p-3 text-center shadow-2xs">
            <p class="text-[9px] font-extrabold uppercase text-slate-500 tracking-wider">AKTIF</p>
            <p class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['aktif'] }}</p>
        </div>
        <div class="rounded-2xl glass-card p-3 text-center shadow-2xs">
            <p class="text-[9px] font-extrabold uppercase text-slate-500 tracking-wider">SELESAI</p>
            <p class="text-2xl font-black text-slate-600 mt-0.5">{{ $stats['selesai'] }}</p>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <section class="rounded-3xl glass-card p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('rencana.index') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Status</label>
                    <select name="status" class="{{ $in }}">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Kata Kunci</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul..." class="{{ $in }}">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" class="btn-brand-primary flex-1 rounded-2xl py-2.5 text-xs font-bold text-white shadow-xs cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->filled('status') || request()->filled('q'))
                    <a href="{{ route('rencana.index') }}" class="shrink-0 rounded-2xl bg-white border border-slate-300 px-3.5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition" title="Reset filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Daftar Rencana Kerja --}}
    <section class="rounded-3xl glass-card p-4 sm:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Daftar Rencana Kerja</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $rencanaList->total() }} data</span>
        </div>

        @if($rencanaList->isEmpty())
            <div class="rounded-2xl glass-subcard p-8 text-center">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada rencana kerja</p>
                <p class="text-xs text-slate-500 mt-0.5">Buat rencana kerja pertama Anda untuk mulai mencatat aktivitas harian.</p>
                <a href="{{ route('rencana.create') }}" class="btn-brand-primary mt-3 inline-block rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs cursor-pointer">
                    + Tambah Rencana Baru
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($rencanaList as $item)
                    @php
                        $badge = match($item->status) {
                            'aktif' => ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'selesai' => ['Selesai', 'bg-blue-50 text-blue-700 border-blue-200'],
                            default => ['Nonaktif', 'bg-slate-100 text-slate-600 border-slate-300'],
                        };
                    @endphp
                    <div class="rounded-2xl glass-subcard p-4 shadow-2xs space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-extrabold text-slate-900 leading-snug break-words">
                                    {{ $item->judul }}
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Dibuat {{ $item->created_at->locale('id')->isoFormat('D MMMM Y') }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $badge[1] }}">
                                {{ $badge[0] }}
                            </span>
                        </div>

                        @if($item->deskripsi)
                            <p class="text-xs text-slate-700 leading-relaxed bg-white/80 p-2.5 rounded-xl border border-white break-words">
                                {{ $item->deskripsi }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-xs">
                            <span class="text-[11px] font-semibold text-slate-500">
                                Digunakan pada <b class="text-brand">{{ $item->aktivitas_details_count }}</b> aktivitas
                            </span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('rencana.edit', $item->id) }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition">
                                    ✏️ Edit
                                </a>
                                @if($item->canBeDeleted())
                                    <form method="POST" action="{{ route('rencana.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana kerja ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 bg-rose-50/90 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-700 shadow-2xs transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($rencanaList->hasPages())
                <div class="mt-4 pt-2">
                    {{ $rencanaList->links() }}
                </div>
            @endif
        @endif
    </section>

</div>
@endsection
