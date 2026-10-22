@extends('layouts.pembimbing-mobile')

@section('title', 'Laporan Penilaian Akhir Magang - ' . $penilaian->magang->nama_lengkap)

@push('head')
<style>
    @media print {
        header, footer, .no-print, nav, aside, .sticky-top-nav, .btn-print-group { display: none !important; }
        *, *::before, *::after { box-shadow: none !important; scrollbar-width: none !important; }
        *::-webkit-scrollbar, ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        html, body { background-color: white !important; color: black !important; margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; overflow: visible !important; overflow-x: clip !important; }
        main { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .print-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
        .page-break { page-break-after: always; }
    }

    /* Gaya lembar nilai resmi */
    .judul-laporan { text-align: center; margin: 15px 0 20px; }
    .judul-laporan h3 { font-size: 13pt; font-weight: 800; margin: 0; text-transform: uppercase; color: #111827; }
    .judul-laporan p { font-size: 9.5pt; color: #4b5563; margin: 2px 0 0; }
    .info-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px; }
    .info-table td { padding: 3px 4px; vertical-align: top; }
    .info-table td.k { width: 185px; color: #374151; white-space: nowrap; }
    .info-table td.s { width: 15px; text-align: center; color: #6b7280; white-space: nowrap; }
    .rekap-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-top: 6px; }
    .rekap-table th, .rekap-table td { border: 1px solid #334155; padding: 5px 7px; vertical-align: middle; }
    .rekap-table th { background: #f1f5f9; text-align: center; font-weight: bold; color: #0f172a; text-transform: uppercase; }
    .rekap-table td.c { text-align: center; }
    .rekap-table tr { page-break-inside: avoid; }
    .rekap-table tfoot td { font-weight: bold; background: #f8fafc; }
</style>
@endpush

@section('content')
@php
    $pengaturan = $pengaturan ?? \App\Models\Pengaturan::first();
@endphp

<!-- Header Sticky Navigasi -->
<header class="sticky-top-nav no-print -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.penilaian.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Laporan Penilaian Akhir Magang</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $penilaian->magang->nama_lengkap }} ({{ $penilaian->magang->no_induk ?? '-' }})</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    <!-- Top Action Bar (Layar) -->
    <div class="no-print flex items-center justify-between gap-2 rounded-2xl border border-slate-300 bg-white p-3 shadow-xs">
        <div class="flex items-center gap-1.5 text-xs text-slate-600 font-bold truncate">
            <span class="rounded-lg bg-brand-soft px-2 py-0.5 text-brand font-black">Nilai: {{ $penilaian->total_nilai }}/100</span>
            <span>Predikat: {{ $penilaian->predikat }}</span>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pembimbing.penilaian.edit', $penilaian->id) }}" class="flex items-center gap-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                <span>Ubah</span>
            </a>

            <a href="{{ route('pembimbing.penilaian.cetak', ['penilaian_id' => $penilaian->id]) }}" target="_blank"
                class="flex items-center gap-1 rounded-xl bg-brand hover:bg-brand-dark px-3.5 py-2 text-xs font-extrabold text-white shadow-xs transition active:scale-[.99]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                </svg>
                <span>Cetak Laporan Penilaian (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Multi-pembimbing info banner jika peserta memiliki evaluasi dari beberapa pembimbing -->
    @if(isset($penilaianAkumulasi) && $penilaianAkumulasi->is_akumulasi)
        <div class="no-print rounded-2xl border border-brand/20 bg-brand-soft p-3 text-xs text-slate-800 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="rounded-full bg-brand px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">Multi-Pembimbing</span>
                <span class="font-extrabold text-brand">Akumulasi {{ $penilaianAkumulasi->evaluasi_count }} Unit Penempatan</span>
            </div>
            <p class="text-slate-600 leading-relaxed text-[11px]">
                Nilai Akhir Akumulasi: <strong class="text-brand font-bold">{{ $penilaianAkumulasi->total_nilai }}</strong> ({{ $penilaianAkumulasi->predikat }} - {{ $penilaianAkumulasi->keterangan_predikat }}). Lembar berikut khusus evaluasi unit: <strong>{{ $penilaian->penempatanMagang->divisi->nama_divisi ?? $penilaian->magang->divisi->nama_divisi ?? '-' }}</strong>.
            </p>
            @if($penilaian->magang->penilaians->count() > 1)
                <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-blue-200/60">
                    <span class="text-[10px] text-slate-500 font-medium">Beralih Unit:</span>
                    @foreach($penilaian->magang->penilaians as $p)
                        <a href="{{ route('pembimbing.penilaian.show', $p->id) }}" class="rounded-lg px-2 py-0.5 text-[11px] font-bold border transition {{ $p->id === $penilaian->id ? 'bg-brand text-white border-brand shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-300' }}">
                            {{ $p->penempatanMagang->divisi->nama_divisi ?? $p->magang->divisi->nama_divisi ?? 'Unit #' . $loop->iteration }} ({{ $p->total_nilai }})
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Lembar Penilaian Resmi (A4) -->
    <div class="print-sheet rounded-3xl border border-slate-300 bg-white p-5 sm:p-8 shadow-xs">

        @include('admin.cetak.partials.kop-surat')

        {{-- Judul Lembar Penilaian --}}
        <div class="judul-laporan">
            <h3>LAPORAN PENILAIAN AKHIR MAGANG</h3>
            <p>Praktik Kerja Lapangan (PKL) / Program Internship</p>
        </div>

        {{-- Identitas Peserta Magang --}}
        <table class="info-table" style="width: 100%; margin-bottom: 14px;">
            <colgroup>
                <col style="width: 185px;">
                <col style="width: 15px;">
                <col>
            </colgroup>
            <tr>
                <td class="k" style="width: 185px; white-space: nowrap;">Nama Peserta Magang</td>
                <td class="s" style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ $penilaian->magang->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Nomor Induk (NIS/NIM)</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->no_induk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Asal Lembaga / Kampus</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->instansi_pendidikan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Program Studi / Jurusan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->jurusan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Unit / Divisi Penempatan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->penempatanMagang->divisi->nama_divisi ?? ($penilaian->magang->divisi->nama_divisi ?? ($divisi->nama_divisi ?? '-')) }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Periode Magang</td>
                <td class="s" style="text-align: center;">:</td>
                <td>
                    @if($penilaian->magang->tanggal_mulai && $penilaian->magang->tanggal_selesai)
                        {{ \Carbon\Carbon::parse($penilaian->magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') }} sampai {{ \Carbon\Carbon::parse($penilaian->magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        {{-- Tabel Nilai Akhir (No, Unsur/Kriteria Penilaian, Nilai Angka) --}}
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No</th>
                    <th style="width: 67%;">Unsur / Kriteria Penilaian</th>
                    <th style="width: 25%;">Nilai Angka</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penilaian->detail as $index => $detail)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</td>
                        <td class="c"><strong>{{ $detail->nilai }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL NILAI AKHIR</td>
                    <td class="c" style="font-weight: bold; font-size: 11pt;">{{ $penilaian->total_nilai }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">PREDIKAT KELULUSAN</td>
                    <td class="c" style="font-weight: bold;">{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})</td>
                </tr>
            </tfoot>
        </table>

        {{-- Standar Predikat --}}
        <div style="font-size: 8.5pt; color: #334155; margin-top: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1;">
            <strong>Standar Predikat:</strong>
            &bull; <strong>90 &ndash; 100:</strong> A (Sangat Baik)
            &bull; <strong>80 &ndash; 89:</strong> B (Baik)
            &bull; <strong>70 &ndash; 79:</strong> C (Cukup Baik)
            &bull; <strong>60 &ndash; 69:</strong> D (Kurang Baik)
            &bull; <strong>&lt; 60:</strong> E (Tidak Baik)
        </div>

        {{-- Hasil Karya Peserta Magang (Opsional) --}}
        @if(!empty($penilaian->hasil_karya) && count(array_filter($penilaian->hasil_karya)) > 0)
            <div style="margin-top: 10px; font-size: 9.5pt; page-break-inside: avoid;">
                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Hasil Karya Peserta Magang:</div>
                <ol style="margin: 0; padding-left: 20px; color: #334155; line-height: 1.5;">
                    @foreach(array_filter($penilaian->hasil_karya) as $karya)
                        <li>{{ $karya }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        @include('admin.cetak.partials.ttd-dua', [
            'divisi' => $penilaian->penempatanMagang?->divisi ?? $penilaian->magang?->divisi ?? $divisi,
            'pembimbing' => $penilaian->pembimbing ?? $pembimbing,
            'pengaturan' => $pengaturan,
        ])

    </div>
</div>
@endsection