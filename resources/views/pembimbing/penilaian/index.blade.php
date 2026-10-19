@extends('layouts.pembimbing-mobile')

@section('title', 'Penilaian Akhir Magang')

@section('content')
@php
    $in = 'w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25 transition';
    $adaFilter = request()->hasAny(['q', 'search', 'tanggal_mulai', 'tanggal_selesai', 'tanggal_akhir', 'status_nilai']);
    $fmtTgl = fn ($tgl) => $tgl ? \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('D MMM Y') : '-';
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.dashboard') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-xl font-black text-slate-900 leading-tight">Penilaian akhir magang</h1>
        <p class="text-xs text-slate-500 font-medium">{{ $belumDinilai }} peserta perlu ditindaklanjuti</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

{{-- Filter Section --}}
<form method="GET" action="{{ route('pembimbing.penilaian.index') }}" class="space-y-2.5">
    <!-- Cari Peserta -->
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
            </svg>
        </span>
        <input type="search" name="q" value="{{ request('q', request('search')) }}" placeholder="cari peserta"
            class="{{ $in }} pl-10">
    </div>

    <!-- Tanggal Mulai & Tanggal Selesai -->
    <div class="grid grid-cols-2 gap-2.5">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </span>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" title="Tanggal mulai" placeholder="tanggal mulai"
                class="{{ $in }} pl-8 text-xs">
        </div>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </span>
            <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai', request('tanggal_akhir')) }}" title="Tanggal selesai" placeholder="tanggal selesai"
                class="{{ $in }} pl-8 text-xs">
        </div>
    </div>

    <!-- Dropdown Status -->
    <div class="relative">
        <select name="status_nilai"
            class="{{ $in }} appearance-none pr-9">
            <option value="">semua status</option>
            <option value="belum" @selected(request('status_nilai') === 'belum')>Perlu dinilai</option>
            <option value="sudah" @selected(request('status_nilai') === 'sudah')>Sudah dinilai</option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </div>

    <!-- Tombol Terapkan Filter -->
    <button type="submit"
        class="w-full rounded-2xl bg-slate-500 hover:bg-slate-600 py-3 text-sm font-extrabold text-white shadow-xs transition active:scale-[.99]">
        Terapkan Filter
    </button>

    <!-- Tombol Cetak Rekapitulasi -->
    <a href="{{ route('pembimbing.penilaian.cetak', request()->query()) }}" target="_blank"
        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-500 hover:bg-slate-600 py-3 text-sm font-extrabold text-white shadow-xs transition active:scale-[.99]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
        </svg>
        <span>Cetak Rekapitulasi</span>
    </a>

    @if($adaFilter)
        <a href="{{ route('pembimbing.penilaian.index') }}" class="block text-center text-xs font-bold text-slate-500 underline pt-1">
            Reset filter
        </a>
    @endif
</form>

<!-- Catatan per tanggal -->
<p class="mt-2 text-xs text-slate-500 leading-relaxed">
    Catatan per tanggal, bukan rekap periode. Jam keluar kosong berarti belum tercatat.
</p>

{{-- Skala contoh · 1-100 --}}
<div class="mt-4 rounded-2xl bg-brand-soft p-4 text-slate-800 border border-blue-200/60">
    <p class="text-sm font-extrabold text-slate-900 mb-2">Skala contoh &middot; 1-100</p>
    <div class="space-y-1 text-xs text-slate-700 font-medium">
        <div class="flex items-center gap-2">
            <span class="w-12 font-semibold">&gt; 90</span>
            <span>: Sangat baik</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-12 font-semibold">80-89</span>
            <span>: Baik</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-12 font-semibold">70-79</span>
            <span>: Cukup baik</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-12 font-semibold">60-69</span>
            <span>: Kurang baik</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-12 font-semibold">&lt; 60</span>
            <span>: Tidak baik</span>
        </div>
    </div>
</div>

{{-- SECTION 1: PERLU DINILAI --}}
@if(request('status_nilai') !== 'sudah')
    <div class="mt-6 space-y-3">
        <h2 class="text-base font-extrabold text-slate-900">Perlu dinilai</h2>

        @forelse($perluDinilai as $item)
            @php
                $detailCount = $item->penilaian_saya ? $item->penilaian_saya->detail()->count() : 0;
                $tglMulaiTeks = $fmtTgl($item->tanggal_mulai);
                $tglSelesaiTeks = $fmtTgl($item->tanggal_selesai);
                $pembimbingAktifId = auth()->user()->pembimbing?->id;
                $penempatanSaya = $item->penempatanMagang->where('pembimbing_id', $pembimbingAktifId)->first();
            @endphp
            <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs">
                <div class="flex items-start gap-3">
                    <!-- Avatar Circle -->
                    <div class="h-12 w-12 shrink-0 rounded-full border border-slate-300 bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if($item->pengguna && $item->pengguna->foto_url)
                            <img src="{{ $item->pengguna->foto_url }}" alt="{{ $item->nama_lengkap }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-6 w-6 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-base font-extrabold text-slate-900">{{ $item->nama_lengkap }}</h3>
                        <p class="truncate text-xs text-slate-500">{{ $item->instansi_pendidikan ?? ($item->divisi->nama_divisi ?? '-') }}</p>

                        @if ($penempatanSaya && $item->penempatanMagang->count() > 1)
                            <p class="text-[11px] font-bold text-purple-700 mt-0.5">
                                Unit: {{ $penempatanSaya->divisi->nama_divisi ?? '-' }}
                                <span class="font-normal text-slate-400">({{ $penempatanSaya->tanggal_mulai?->format('d/m') }} - {{ $penempatanSaya->tanggal_selesai?->format('d/m/y') }})</span>
                            </p>
                        @endif
                        
                        <div class="mt-1">
                            <span class="inline-block rounded-full bg-slate-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                                {{ $detailCount > 0 ? "Draft - {$detailCount}/{$totalKriteria} terisi" : 'Belum dinilai' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-xs">
                    <p class="text-slate-500">Periode Magang :</p>
                    <p class="font-extrabold text-slate-800">{{ $tglMulaiTeks }} s.d {{ $tglSelesaiTeks }}</p>
                </div>

                <div class="mt-3.5">
                    <a href="{{ route('pembimbing.penilaian.create', ['magang_id' => $item->id, 'penempatan_magang_id' => $penempatanSaya?->id]) }}"
                        class="block w-full rounded-xl border border-slate-300 bg-slate-100 py-2.5 text-center text-sm font-bold text-slate-800 hover:bg-slate-200 transition">
                        Lanjutkan Penilaian
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 p-4 text-center text-xs text-slate-500">
                Tidak ada peserta yang perlu dinilai saat ini.
            </div>
        @endforelse
    </div>
@endif

{{-- SECTION 2: SUDAH DINILAI --}}
@if(request('status_nilai') !== 'belum')
    <div class="mt-6 space-y-3">
        <h2 class="text-base font-extrabold text-slate-900">Sudah dinilai</h2>

        @forelse($sudahDinilaiList as $item)
            @php
                $penilaian = $item->penilaian_saya ?? $item->penilaian;
                $tglMulaiTeks = $fmtTgl($item->tanggal_mulai);
                $tglSelesaiTeks = $fmtTgl($item->tanggal_selesai);
                $isAkumulasi = $item->penilaian_akumulasi && $item->penilaian_akumulasi->is_akumulasi;
            @endphp
            <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-3 min-w-0">
                        <!-- Avatar Circle -->
                        <div class="h-12 w-12 shrink-0 rounded-full border border-slate-300 bg-slate-100 flex items-center justify-center overflow-hidden">
                            @if($item->pengguna && $item->pengguna->foto_url)
                                <img src="{{ $item->pengguna->foto_url }}" alt="{{ $item->nama_lengkap }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-6 w-6 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('pembimbing.penilaian.show', $penilaian->id) }}" class="hover:underline">
                                <h3 class="truncate text-base font-extrabold text-slate-900">{{ $item->nama_lengkap }}</h3>
                            </a>
                            <p class="truncate text-xs text-slate-500">{{ $item->instansi_pendidikan ?? ($item->divisi->nama_divisi ?? '-') }}</p>
                            
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                <span class="inline-block rounded-full bg-slate-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                                    Nilai : {{ $penilaian->total_nilai ?? 0 }}/100
                                </span>
                                @if ($isAkumulasi)
                                    <span class="inline-block rounded-full bg-purple-100 border border-purple-200 px-2 py-0.5 text-[11px] font-bold text-purple-700">
                                        Akumulasi: {{ $item->penilaian_akumulasi->total_nilai }}/100 ({{ $item->penilaian_akumulasi->jumlah_pembimbing }} Pembimbing)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Letter Grade Badge (e.g. D, A, B) -->
                    <div class="shrink-0 text-right space-y-1">
                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-500 px-2 text-xs font-black text-white">
                            {{ $penilaian->predikat ?? '-' }}
                        </span>
                        @if ($isAkumulasi)
                            <span class="block text-[10px] font-bold text-purple-700">
                                Akum: {{ $item->penilaian_akumulasi->predikat }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 text-xs">
                    <p class="text-slate-500">Periode Magang :</p>
                    <p class="font-extrabold text-slate-800">{{ $tglMulaiTeks }} s.d {{ $tglSelesaiTeks }}</p>
                </div>

                <!-- 3 Action Buttons -->
                <div class="mt-3.5 grid grid-cols-3 gap-2">
                    <!-- Cetak Langsung mPDF -->
                    <a href="{{ route('pembimbing.penilaian.cetak', ['penilaian_id' => $penilaian->id]) }}" target="_blank"
                        class="flex items-center justify-center rounded-xl border border-slate-300 bg-white py-2.5 text-slate-700 hover:bg-slate-50 transition shadow-xs"
                        title="Cetak Laporan Penilaian (PDF)">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                        </svg>
                    </a>

                    <!-- Ubah Nilai -->
                    <a href="{{ route('pembimbing.penilaian.edit', $penilaian->id) }}"
                        class="flex items-center justify-center rounded-xl border border-slate-300 bg-white py-2.5 text-slate-700 hover:bg-slate-50 transition shadow-xs"
                        title="Ubah Penilaian">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </a>

                    <!-- Hapus Nilai -->
                    <form method="POST" action="{{ route('pembimbing.penilaian.destroy', $penilaian->id) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penilaian ini?')" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white py-2.5 text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition shadow-xs"
                            title="Hapus Penilaian">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 p-4 text-center text-xs text-slate-500">
                Belum ada data penilaian yang telah selesai.
            </div>
        @endforelse
    </div>
@endif
@endsection
