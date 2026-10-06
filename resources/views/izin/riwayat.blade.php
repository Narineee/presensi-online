@extends('layouts.mobile')
@section('title', 'Riwayat Permohonan Ketidakhadiran')

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
            <p class="text-xs text-slate-500">Riwayat Permohonan Ketidakhadiran</p>
        </div>
    </div>
    <a href="{{ route('izin.create') }}" class="rounded-xl bg-brand px-3 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-dark transition flex items-center gap-1">
        <span>+ Ajukan</span>
    </a>
</header>

<div class="space-y-5">

    {{-- 4 Stat Pills --}}
    <div class="grid grid-cols-4 gap-2">
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">TOTAL</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">DISETUJUI</p>
            <p class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['disetujui'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">MENUNGGU</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-2xl bg-[#ECEEEF]/90 p-3 text-center border border-slate-200/80 shadow-xs">
            <p class="text-[9px] font-bold uppercase text-slate-600 tracking-wider">DITOLAK</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ $stats['ditolak'] }}</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <section class="rounded-3xl bg-[#ECEEEF]/80 p-5 ring-1 ring-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('izin.riwayat') }}" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Jenis Izin</label>
                    <select name="jenis_izin" class="{{ $in }}">
                        <option value="">Semua Jenis</option>
                        <option value="sakit" {{ request('jenis_izin') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ request('jenis_izin') === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="cuti" {{ request('jenis_izin') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Status Persetujuan</label>
                    <select name="status_approval" class="{{ $in }}">
                        <option value="">Semua Status</option>
                        <option value="disetujui" {{ request('status_approval') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="pending" {{ request('status_approval') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="ditolak" {{ request('status_approval') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="pt-1 flex gap-2">
                <button type="submit" class="flex-1 rounded-2xl bg-slate-600 hover:bg-slate-700 py-3 text-center text-xs font-extrabold text-white transition active:scale-[.99] shadow-xs">
                    Terapkan Filter
                </button>
                @if(request()->filled('jenis_izin') || request()->filled('status_approval'))
                    <a href="{{ route('izin.riwayat') }}" class="px-4 py-3 rounded-2xl bg-white border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Riwayat Permohonan Ketidakhadiran --}}
    <section class="rounded-3xl bg-[#ECEEEF]/80 p-5 ring-1 ring-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Riwayat Ketidakhadiran</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $pengajuanIzin->total() }} catatan</span>
        </div>

        @if($pengajuanIzin->isEmpty())
            <div class="rounded-2xl bg-white p-8 text-center border border-slate-200/80">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada riwayat permohonan</p>
                <p class="text-xs text-slate-500 mt-0.5">Ajukan permohonan izin atau sakit jika berhalangan hadir.</p>
                <a href="{{ route('izin.create') }}" class="mt-3 inline-block rounded-xl bg-brand px-4 py-2 text-xs font-bold text-white shadow-xs">
                    Ajukan Permohonan Baru
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($pengajuanIzin as $item)
                    @php
                        $st = match($item->status_approval) {
                            'disetujui' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                            'ditolak' => ['Ditolak', 'bg-rose-100 text-rose-800 border-rose-200'],
                            default => ['Menunggu', 'bg-amber-100 text-amber-800 border-amber-200'],
                        };
                        $jenisBadge = match($item->jenis_izin) {
                            'sakit' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'cuti' => 'bg-purple-50 text-purple-700 border-purple-200',
                            default => 'bg-blue-50 text-blue-700 border-blue-200',
                        };
                        $tanggalMulaiText = $item->tanggal_mulai ? $item->tanggal_mulai->locale('id')->isoFormat('D MMM Y') : '-';
                        $tanggalSelesaiText = $item->tanggal_selesai ? $item->tanggal_selesai->locale('id')->isoFormat('D MMM Y') : '-';
                    @endphp
                    <div class="rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border uppercase tracking-wider {{ $jenisBadge }}">
                                    {{ $item->jenis_izin }}
                                </span>
                                <span class="text-xs font-bold text-slate-700">
                                    {{ $item->jumlah_hari }} hari kerja
                                </span>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $st[1] }}">
                                {{ $st[0] }}
                            </span>
                        </div>

                        <p class="text-xs font-semibold text-slate-800">
                            📅 {{ $tanggalMulaiText }} s/d {{ $tanggalSelesaiText }}
                        </p>

                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 rounded-xl p-2.5">
                            {{ $item->alasan }}
                        </p>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[11px]">
                            <span class="text-slate-400">
                                {{ $item->nama_validator ? 'Diverifikasi: ' . $item->nama_validator : 'Menunggu verifikasi' }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('izin.show', $item->id) }}" class="rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition">
                                    Detail
                                </a>
                                @if($item->canBeEdited())
                                    <a href="{{ route('izin.edit', $item->id) }}" class="rounded-xl bg-brand-soft text-brand hover:bg-brand hover:text-white px-3 py-1.5 text-xs font-bold transition">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($pengajuanIzin->hasPages())
                <div class="mt-4 pt-2">
                    {{ $pengajuanIzin->links() }}
                </div>
            @endif
        @endif
    </section>

</div>
@endsection
