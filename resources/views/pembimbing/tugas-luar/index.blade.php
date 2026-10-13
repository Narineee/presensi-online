@extends('layouts.pembimbing-mobile')
@section('title', 'Validasi Tugas Luar')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $adaFilter = request()->hasAny(['q', 'tanggal_mulai', 'tanggal_akhir', 'tanggal', 'status_verifikasi', 'status']);
    $namaDepan = fn ($n) => \Illuminate\Support\Str::of((string) $n)->trim()->before(' ')->toString();
    $fmtJam = fn ($w) => $w ? str_replace(':', '.', substr($w, 0, 5)) : null;

    $formatWaktu = function ($item) use ($fmtJam) {
        $rentangTgl = $item->rentang_tanggal_teks;
        $mulai = $fmtJam($item->waktu_mulai);
        $selesai = $fmtJam($item->waktu_selesai);

        if ($mulai && $selesai) {
            return "{$rentangTgl} · {$mulai}–{$selesai}";
        } elseif ($mulai) {
            return "{$rentangTgl} · Mulai {$mulai}";
        }

        return $rentangTgl;
    };

    $formatWaktuKeputusan = function ($item) use ($fmtJam) {
        $rentangTgl = $item->rentang_tanggal_teks;
        $mulai = $fmtJam($item->waktu_mulai);
        $selesai = $fmtJam($item->waktu_selesai);

        if ($mulai && $selesai) {
            return "{$rentangTgl} ({$mulai} - {$selesai})";
        } elseif ($mulai) {
            return "{$rentangTgl} ({$mulai})";
        }

        return $rentangTgl;
    };
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-lg font-extrabold leading-tight">Validasi Tugas Luar</h1>
        <p class="text-xs text-slate-500">Permohonan tugas luar</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

{{-- Filter --}}
<form method="GET" action="{{ route('pembimbing.tugas-luar.index') }}" class="space-y-2.5">
    <label class="relative block">
        <span class="sr-only">Cari peserta</span>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
        </svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="cari peserta" class="{{ $in }} pl-9">
    </label>

    <div class="grid grid-cols-2 gap-2.5">
        <label class="block">
            <span class="sr-only">Tanggal mulai</span>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="{{ $in }}" title="Tanggal mulai" placeholder="tanggal mulai">
        </label>
        <label class="block">
            <span class="sr-only">Tanggal selesai</span>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="{{ $in }}" title="Tanggal selesai" placeholder="tanggal selesai">
        </label>
    </div>

    <label class="block">
        <span class="sr-only">Status</span>
        <select name="status_verifikasi" class="{{ $in }}">
            <option value="">Semua status</option>
            <option value="menunggu" @selected(request('status_verifikasi', request('status')) === 'menunggu')>Menunggu verifikasi</option>
            <option value="disetujui" @selected(request('status_verifikasi', request('status')) === 'disetujui')>Disetujui</option>
            <option value="ditolak" @selected(request('status_verifikasi', request('status')) === 'ditolak')>Ditolak</option>
        </select>
    </label>

    <button type="submit" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Terapkan Filter</button>
    @if ($adaFilter)
        <a href="{{ route('pembimbing.tugas-luar.index') }}" class="block text-center text-xs font-bold text-slate-500 underline">Reset filter</a>
    @endif
</form>

{{-- MENUNGGU VERIFIKASI --}}
<h2 class="mt-5 mb-3 text-base font-extrabold">{{ $menunggu->count() }} menunggu verifikasi</h2>

<ul class="space-y-3">
    @forelse ($menunggu as $item)
        @php
            $nama = $item->nama_lengkap ?? ($item->pengguna?->username ?? '-');
            $keperluan = $item->keperluan;
            $tujuan = $item->tujuan;
            $waktuTeks = $formatWaktu($item);
            $hasBukti = ! empty($item->bukti);
            $buktiUrl = $item->bukti_url;
            $buktiNama = $item->bukti ? basename($item->bukti) : '';
            $buktiExt = $item->bukti ? strtoupper(pathinfo($item->bukti, PATHINFO_EXTENSION)) : '';
            $buktiSize = ($item->bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->bukti))
                ? round(\Illuminate\Support\Facades\Storage::disk('public')->size($item->bukti) / 1024).' KB'
                : null;
            $buktiInfo = trim(($buktiExt ?: 'DOKUMEN').($buktiSize ? ' · '.$buktiSize : ''));
        @endphp
        <li x-data="{ open: {{ $loop->first ? 'true' : 'false' }}, catatan: '', err: false }" class="rounded-2xl border border-brand/30 bg-white p-4">
            <span class="inline-block rounded-full bg-slate-200 px-3 py-1 text-xs font-extrabold text-slate-700">Menunggu verifikasi</span>
            <h3 class="mt-2 text-base font-extrabold leading-tight text-slate-900">{{ $nama }}</h3>

            <p class="mt-2 text-xs font-semibold text-slate-500">Penugasan</p>
            <p class="text-sm font-extrabold leading-snug text-slate-900">{{ $keperluan }}</p>

            <p class="mt-2 text-xs font-semibold text-slate-500">Lokasi Tujuan</p>
            <p class="text-sm font-bold text-slate-800">{{ $tujuan }}</p>

            <p class="mt-2 text-xs font-semibold text-slate-500">Tanggal & Waktu</p>
            <p class="text-sm font-bold text-slate-800">{{ $waktuTeks }}</p>

            @if ($hasBukti)
                <a href="{{ $buktiUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-100 p-3 hover:bg-slate-200/70 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white border border-slate-200 text-slate-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-extrabold text-slate-900">{{ $buktiNama }}</p>
                            <p class="text-[11px] font-medium text-slate-500">{{ $buktiInfo }}</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                </a>
            @endif

            <button type="button" x-show="!open" @click="open = true" class="mt-3 text-sm font-bold text-brand hover:underline">Tinjau permohonan →</button>

            <form x-show="open" x-cloak method="POST" action="{{ route('pembimbing.tugas-luar.validasi', $item->id) }}" class="mt-4 pt-3 border-t border-slate-100">
                @csrf
                <label class="block text-sm font-bold text-slate-900" for="catatan-{{ $item->id }}">Catatan keputusan</label>
                <textarea id="catatan-{{ $item->id }}" name="catatan_pembimbing" rows="3" maxlength="500" x-model="catatan" @input="err = false"
                          placeholder="Tulis catatan. Alasan wajib jika ditolak."
                          class="{{ $in }} mt-1.5"></textarea>
                <p x-show="err" x-cloak class="mt-1 text-xs font-bold text-rose-700" role="alert">Alasan penolakan wajib diisi jika menolak permohonan.</p>

                <div class="mt-3 space-y-2">
                    <button type="submit" name="status_verifikasi" value="disetujui" class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white shadow-sm active:scale-[.99]">Setujui tugas luar</button>
                    <button type="submit" name="status_verifikasi" value="ditolak" @click="if (!catatan.trim()) { $event.preventDefault(); err = true }"
                            class="w-full rounded-2xl border border-slate-400 bg-white py-3.5 text-sm font-extrabold text-slate-800 hover:bg-slate-50">Tolak dengan alasan</button>
                </div>
            </form>
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center ring-1 ring-slate-200">
            <p class="text-sm font-bold">Tidak ada permohonan yang menunggu</p>
            <p class="mt-1 text-xs text-slate-500">Semua permohonan tugas luar sudah diverifikasi.</p>
        </li>
    @endforelse
</ul>

{{-- KEPUTUSAN TERBARU --}}
<h2 class="mt-6 mb-3 text-base font-extrabold">Keputusan terbaru</h2>

<ul class="space-y-3">
    @forelse ($keputusan as $item)
        @php
            $nama = $item->nama_lengkap ?? ($item->pengguna?->username ?? '-');
            $keperluan = $item->keperluan;
            $tujuan = $item->tujuan;
            $setuju = $item->status_verifikasi === 'disetujui';
            $pembimbingNama = $namaDepan($item->validator?->pembimbing?->nama_lengkap ?? $item->validator?->username);
            $tglValidasi = $item->verified_at ? \Carbon\Carbon::parse($item->verified_at)->locale('id')->isoFormat('D MMM') : '';
            $waktuKeputusan = $formatWaktuKeputusan($item);
            $hasBukti = ! empty($item->bukti);
            $buktiUrl = $item->bukti_url;
            $buktiNama = $item->bukti ? basename($item->bukti) : '';
            $buktiExt = $item->bukti ? strtoupper(pathinfo($item->bukti, PATHINFO_EXTENSION)) : '';
            $buktiSize = ($item->bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->bukti))
                ? round(\Illuminate\Support\Facades\Storage::disk('public')->size($item->bukti) / 1024).' KB'
                : null;
            $buktiInfo = trim(($buktiExt ?: 'DOKUMEN').($buktiSize ? ' · '.$buktiSize : ''));
        @endphp
        <li class="rounded-2xl border border-brand/30 bg-white p-4">
            <span class="inline-block rounded-full px-3 py-1 text-xs font-extrabold {{ $setuju ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ $setuju ? 'Disetujui' : 'Ditolak' }}
            </span>
            <h3 class="mt-2 text-base font-extrabold leading-tight text-slate-900">{{ $nama }}</h3>

            <p class="mt-2 text-xs font-semibold text-slate-500">Penugasan & Tujuan</p>
            <p class="text-sm font-extrabold leading-snug text-slate-900">{{ $keperluan }} · {{ $tujuan }}</p>

            <p class="mt-2 text-xs font-semibold text-slate-500">Tanggal & Waktu</p>
            <p class="text-sm font-bold text-slate-800">{{ $waktuKeputusan }}</p>

            @if ($hasBukti)
                <a href="{{ $buktiUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-100 p-3 hover:bg-slate-200/70 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white border border-slate-200 text-slate-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-extrabold text-slate-900">{{ $buktiNama }}</p>
                            <p class="text-[11px] font-medium text-slate-500">{{ $buktiInfo }}</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                </a>
            @endif

            @if ($setuju)
                <div class="mt-3 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/90 p-3 text-xs font-bold text-emerald-900">
                    <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Disetujui {{ $pembimbingNama ? $pembimbingNama.' · ' : '' }}{{ $tglValidasi }}. Presensi hari ini tercatat TL.</span>
                </div>
            @else
                <div class="mt-3 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50/90 p-3 text-xs font-bold text-rose-900">
                    <svg class="h-4 w-4 shrink-0 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p>Ditolak {{ $pembimbingNama ? $pembimbingNama.' · ' : '' }}{{ $tglValidasi }}</p>
                        @if ($item->catatan_pembimbing)
                            <p class="mt-0.5 text-[11px] font-normal text-rose-700">Alasan: {{ $item->catatan_pembimbing }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </li>
    @empty
        <li class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">Belum ada keputusan.</li>
    @endforelse
</ul>

@if ($keputusan->hasPages())
    <nav class="mt-4 flex items-center justify-between gap-3" aria-label="Halaman keputusan">
        @if ($keputusan->previousPageUrl())
            <a href="{{ $keputusan->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Lebih baru</a>
        @else <span></span> @endif
        <span class="text-xs text-slate-500">Hal. {{ $keputusan->currentPage() }} / {{ $keputusan->lastPage() }}</span>
        @if ($keputusan->nextPageUrl())
            <a href="{{ $keputusan->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Lebih lama →</a>
        @else <span></span> @endif
    </nav>
@endif
@endsection
