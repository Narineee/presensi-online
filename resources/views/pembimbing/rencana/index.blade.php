@extends('layouts.pembimbing-mobile')
@section('title', 'Rencana Kerja Binaan')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaFilter = request()->hasAny(['q', 'magang_id', 'status']);
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Rencana Kerja Binaan</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Informasi rencana kerja mandiri peserta binaan</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- Info Card Read-only --}}
    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-900">Informasi Rencana Kerja Peserta</p>
            <p class="text-slate-600 mt-0.5">Rencana kerja dibuat dan dikelola mandiri oleh peserta magang. Halaman ini bersifat informatif untuk mengetahui tugas/fokus yang direncanakan peserta binaan Anda. Validasi dilakukan pada laporan Aktivitas harian.</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('pembimbing.rencana.index') }}" class="space-y-2.5 rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
        <label class="relative block">
            <span class="sr-only">Cari peserta atau judul rencana</span>
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari peserta / judul rencana" class="{{ $in }} pl-9">
        </label>

        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <select name="magang_id" class="{{ $in }}">
                    <option value="">Semua Peserta</option>
                    @foreach ($supervisedMagang as $sm)
                        <option value="{{ $sm->id }}" @selected(request('magang_id') == $sm->id)>{{ $sm->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="{{ $in }}">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full rounded-2xl bg-brand py-3 text-xs font-extrabold text-white shadow-sm active:scale-[.99] cursor-pointer">
            Terapkan Filter
        </button>
        @if ($adaFilter)
            <a href="{{ route('pembimbing.rencana.index') }}" class="block text-center text-xs font-bold text-slate-500 underline pt-1">
                Reset filter
            </a>
        @endif
    </form>

    {{-- List Rencana Kerja --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900">Total: {{ $rencanaList->total() }} Rencana Kerja</h2>
        </div>

        @if($rencanaList->isEmpty())
            <div class="rounded-2xl bg-white p-8 text-center border border-slate-200">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Tidak ada rencana kerja</p>
                <p class="text-xs text-slate-500 mt-0.5">Peserta binaan belum menambahkan rencana kerja yang sesuai filter.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($rencanaList as $rk)
                    @php
                        $stBadge = match($rk->status) {
                            'aktif' => ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'selesai' => ['Selesai', 'bg-blue-50 text-blue-700 border-blue-200'],
                            default => ['Nonaktif', 'bg-slate-100 text-slate-600 border-slate-200'],
                        };
                    @endphp
                    <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-2xs space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] font-bold text-slate-500 block truncate">
                                    {{ $rk->magang?->nama_lengkap ?? '-' }} · {{ $rk->magang?->divisi?->nama_divisi ?? '-' }}
                                </span>
                                <h3 class="text-sm font-extrabold text-slate-900 mt-0.5 leading-snug break-words">
                                    {{ $rk->judul }}
                                </h3>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $stBadge[1] }}">
                                {{ $stBadge[0] }}
                            </span>
                        </div>

                        @if($rk->deskripsi)
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100 break-words">
                                {{ $rk->deskripsi }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                            <span class="text-[11px] text-slate-500">
                                📅 Dibuat {{ $rk->created_at->locale('id')->isoFormat('D MMM Y') }} · {{ $rk->aktivitas_details_count }} progres
                            </span>
                            <a href="{{ route('pembimbing.rencana.show', $rk->id) }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition">
                                Detail Riwayat
                            </a>
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
