@extends('layouts.pembimbing-mobile')
@section('title', 'Detail Binaan')

@section('content')
@php
    $namaDepan = \Illuminate\Support\Str::of((string) $magang->nama_lengkap)->trim()->before(' ')->toString();
    $fmt = fn ($d, $f) => $d ? \Carbon\Carbon::parse($d)->locale('id')->isoFormat($f) : '-';
    $cakupan = $fmt($rekap['cakupan_mulai'], 'D MMM').'–'.$fmt($rekap['cakupan_selesai'], 'D MMM Y');
    $jam = fn ($t) => $t ? str_replace(':', '.', substr($t, 0, 5)) : '-';

    $stat = [
        ['Hadir', $rekap['total_hadir']], ['Sakit', $rekap['total_sakit']], ['Izin', $rekap['total_izin']],
        ['Cuti', $rekap['total_cuti']], ['TL', $rekap['total_tugas_luar']], ['Alfa', $rekap['total_alpa']],
    ];

    $p = $presensiHariIni;
    $badgeHariIni = ! $p ? 'Belum presensi'
        : (in_array($p->status, ['sakit', 'izin', 'cuti']) ? ucfirst($p->status) : ($p->is_tugas_luar ? 'Tugas Luar' : 'Hadir'));
    $lokasiHariIni = $p && $p->mode_kerja === 'onsite' ? ($magang->getDivisiAt(today())?->nama_divisi ?? 'Kantor')
        : ($p ? strtoupper(str_replace('_', ' ', (string) $p->mode_kerja)) : null);
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.binaan.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Detail Binaan</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Daftar binaan / {{ $namaDepan }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">
    @include('pembimbing.binaan.profil-ringkas', ['magang' => $magang])

    {{-- Rekap presensi individual --}}
    <h2 class="pt-1 text-base font-extrabold">Rekap Presensi Individual</h2>
    <section class="rounded-3xl border border-brand/40 bg-white p-4">
        <p class="text-base font-extrabold">{{ $rekap['hari_kerja_berjalan'] }} total hari magang</p>
        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $cakupan }} · Hari kerja yang sudah berjalan, tidak termasuk hari ini. Hadir + Sakit + Izin + Cuti + Alfa = {{ $rekap['hari_kerja_berjalan'] }} hari.</p>

        <dl class="mt-3 grid grid-cols-6 gap-1 rounded-2xl bg-slate-100 px-2 py-3 text-center">
            @foreach ($stat as [$label, $nilai])
                <div>
                    <dt class="text-[11px] text-slate-500">{{ $label }}</dt>
                    <dd class="text-xl font-extrabold {{ $nilai ? '' : 'text-slate-400' }} {{ $label === 'Alfa' && $nilai ? 'text-rose-700' : '' }}">{{ $nilai }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-2 text-[11px] text-slate-500">TL = Tugas luar (sudah termasuk Hadir), Alfa = Tanpa keterangan</p>

        <a href="{{ route('pembimbing.binaan.riwayat', $magang->id) }}" class="mt-3 block rounded-2xl border border-brand py-3 text-center text-sm font-extrabold text-brand hover:bg-brand-soft">Buka riwayat presensi harian</a>
    </section>

    {{-- Presensi hari ini --}}
    <h2 class="pt-1 text-base font-extrabold">Presensi hari ini</h2>
    <section class="rounded-3xl bg-white p-4 ring-1 ring-slate-200">
        <span class="inline-block rounded-full px-3 py-1 text-xs font-extrabold {{ $p ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $badgeHariIni }}</span>
        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-xs text-slate-500">Masuk</dt><dd class="text-lg font-extrabold">{{ $jam($p?->jam_masuk) }}</dd></div>
            <div><dt class="text-xs text-slate-500">Keluar</dt><dd class="text-lg font-extrabold">{{ $jam($p?->jam_keluar) }}</dd></div>
        </dl>
        @if ($p && $lokasiHariIni)
            <p class="mt-3 text-sm">{{ now()->locale('id')->isoFormat('D MMM') }} - {{ $lokasiHariIni }}</p>
            @if (! is_null($p->face_distance_masuk))
                <p class="text-xs font-semibold text-emerald-700">Lokasi &amp; wajah terverifikasi</p>
            @endif
        @else
            <p class="mt-3 text-xs text-slate-500">Belum ada presensi hari ini.</p>
        @endif
    </section>

    {{-- Tindak lanjut --}}
    <h2 class="pt-1 text-base font-extrabold">Tindak lanjut peserta</h2>
    <section class="rounded-3xl bg-white p-4 ring-1 {{ $aktivitasPending->isNotEmpty() ? 'ring-brand/40' : 'ring-slate-200' }}">
        @if ($aktivitasPending->isNotEmpty())
            @php $a = $aktivitasPending->first(); @endphp
            <p class="text-sm font-extrabold">{{ $aktivitasPending->count() }} Aktivitas menunggu validasi</p>
            <p class="mt-1 text-xs text-slate-600">{{ $a->tanggal->locale('id')->isoFormat('D MMM') }} - {{ \Illuminate\Support\Str::limit($a->isi, 70) }}</p>
            <a href="{{ route('pembimbing.aktivitas.index', ['magang_id' => $magang->id, 'status' => 'pending']) }}" class="mt-3 block rounded-2xl border border-brand py-3 text-center text-sm font-extrabold text-brand hover:bg-brand-soft">Tinjau aktivitas</a>
        @else
            <p class="text-sm font-bold">Tidak ada aktivitas yang menunggu validasi</p>
        @endif
    </section>

    @if (isset($rencanaAktif) && $rencanaAktif->isNotEmpty())
        <div class="rounded-3xl bg-white p-4 ring-1 ring-slate-200 space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-xs font-extrabold uppercase text-slate-500 tracking-wider">Rencana Kerja Aktif</p>
                <a href="{{ route('pembimbing.rencana.index', ['magang_id' => $magang->id]) }}" class="text-xs font-bold text-brand hover:underline">Semua Rencana</a>
            </div>
            <ul class="space-y-2">
                @foreach ($rencanaAktif->take(3) as $rk)
                    <li>
                        <a href="{{ route('pembimbing.rencana.show', $rk->id) }}" class="block rounded-2xl bg-slate-50 p-3 hover:bg-slate-100 transition border border-slate-100">
                            <p class="text-xs font-extrabold text-slate-900">{{ $rk->judul }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">Dibuat {{ $fmt($rk->created_at, 'D MMM Y') }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif ($pekerjaanAktif)
        <a href="{{ route('pembimbing.rencana.index', ['magang_id' => $magang->id]) }}" class="block rounded-3xl bg-white p-4 ring-1 ring-slate-200 hover:ring-brand/40">
            <p class="text-sm font-extrabold">{{ $pekerjaanAktif->judul }}</p>
            <p class="mt-1 text-xs text-slate-600">Pekerjaan aktif</p>
        </a>
    @endif
</div>
@endsection