@extends('layouts.mobile')
@section('title', 'Lembar Nilai Magang')

@push('head')
<style>
    @media print {
        header, footer, nav, aside, .no-print, #modal-presensi-flow { display: none !important; }
        html, body { background: white !important; color: black !important; margin: 0; padding: 0; width: 100% !important; max-width: 100% !important; }
        main { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .print-sheet { display: block !important; border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
        .screen-view { display: none !important; }
        .page-break { page-break-after: always; }
    }
    .nilai-table { width:100%; border-collapse:collapse; font-size:10pt; margin-top:6px; }
    .nilai-table th, .nilai-table td { border:1px solid #000; padding:5px 7px; vertical-align:middle; }
    .nilai-table th { background:#e5e5e5; text-align:center; font-weight:bold; }
    .nilai-table td.c { text-align:center; }
    .nilai-table tfoot td { font-weight:bold; background:#f3f3f3; }
    .judul-bagian { font-size:10.5pt; font-weight:bold; margin:14px 0 4px; }
    .kualifikasi { font-size:9pt; margin-top:10px; }
    .kualifikasi table { border-collapse:collapse; }
    .kualifikasi td { padding:0 14px 0 0; }
    .ttd-nilai { width:100%; margin-top:24px; border-collapse:collapse; page-break-inside:avoid; font-size:10.5pt; }
    .ttd-nilai td { width:50%; text-align:center; vertical-align:top; line-height:1.4; padding:0 10px; border:0; }
    .ttd-nilai .ruang { height:70px; }
    .ttd-nilai .nm { font-weight:bold; text-decoration:underline; }
</style>
@endpush

@section('content')
<header class="no-print sticky-top-nav -mx-4 mb-5 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg hover:bg-slate-50 transition" aria-label="Kembali ke Rekap">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-base font-extrabold leading-tight text-slate-900">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500">Lembar Nilai Magang</p>
    </div>
</header>

<div class="space-y-5">

    @if(!$penilaian)
        {{-- Belum Dinilai Card (Matching PRD penilaian (1).png container) --}}
        <section class="rounded-3xl bg-[#ECEEEF]/80 p-8 ring-1 ring-slate-200/80 shadow-xs text-center min-h-[360px] flex flex-col items-center justify-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-white text-amber-500 mx-auto flex items-center justify-center shadow-xs border border-slate-200/80">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Penilaian Belum Diterbitkan</h2>
                <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1 leading-relaxed">
                    Pembimbing lapangan belum menginput evaluasi nilai akhir. Pastikan presensi dan aktivitas Anda telah lengkap.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('magang.rekap') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold shadow-xs hover:bg-slate-50 transition">
                    Kembali ke Rekap
                </a>
            </div>
        </section>
    @else
        {{-- Screen View (Mobile Card Layout) --}}
        <div class="screen-view space-y-4">
            {{-- Summary Card --}}
            <section class="rounded-3xl bg-[#ECEEEF]/90 p-5 ring-1 ring-slate-300/80 shadow-xs space-y-4">
                <div class="rounded-2xl bg-white p-5 border border-slate-200/90 shadow-xs text-center space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">NILAI AKHIR MAGANG</span>
                    <p class="text-4xl font-extrabold text-brand">{{ $penilaian->total_nilai }} <span class="text-sm font-semibold text-slate-400">/ 100</span></p>
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-800">
                        Predikat: {{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})
                    </div>
                </div>

                {{-- Identitas Singkat --}}
                <div class="rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Nama Lengkap</span>
                        <span class="font-bold text-slate-900">{{ $penilaian->magang->nama_lengkap }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">NIM / NIS</span>
                        <span class="font-bold text-slate-900">{{ $penilaian->magang->no_induk }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Lembaga</span>
                        <span class="font-bold text-slate-900">{{ $penilaian->magang->instansi_pendidikan }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Pembimbing</span>
                        <span class="font-bold text-slate-900">{{ $penilaian->pembimbing->nama_lengkap ?? '-' }}</span>
                    </div>
                </div>

                {{-- Rincian Kriteria --}}
                <div class="rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs space-y-2.5">
                    <p class="text-xs font-extrabold text-slate-900">Rincian Kriteria Penilaian</p>
                    <div class="space-y-2">
                        @foreach($penilaian->detail as $detail)
                            @php
                                $bobot = $detail->kriteria->bobot ?? 0;
                                $terbobot = round(($detail->nilai * $bobot) / 100, 2);
                            @endphp
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 text-xs">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</p>
                                    <p class="text-[11px] text-slate-500">Bobot {{ $bobot }}%</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-brand">{{ $detail->nilai }}</span>
                                    <span class="text-[10px] text-slate-400 block">({{ $terbobot }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($penilaian->catatan)
                    <div class="rounded-2xl bg-white p-4 border border-slate-200/90 shadow-xs space-y-1.5 text-xs">
                        <p class="font-extrabold text-slate-900">Catatan Evaluasi Pembimbing</p>
                        <p class="text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">{{ $penilaian->catatan }}</p>
                    </div>
                @endif
            </section>
        </div>

        {{-- Printable Sheet (Official Print Version) --}}
        <div class="print-sheet hidden bg-white p-8 border border-slate-300">
            @include('admin.cetak.partials.kop-surat')

            <div class="text-center my-4">
                <h3 class="text-base font-bold uppercase tracking-wider">Lembar Penilaian Akhir Magang</h3>
                <p class="text-xs text-slate-600">Praktik Kerja Lapangan (PKL) / Program Internship</p>
            </div>

            <table class="w-full text-xs mb-4">
                <tr><td style="width:170px" class="py-1">Nama Peserta Magang</td><td>:</td><td><strong>{{ $penilaian->magang->nama_lengkap }}</strong></td></tr>
                <tr><td class="py-1">Nomor Induk (NIS/NIM)</td><td>:</td><td>{{ $penilaian->magang->no_induk }}</td></tr>
                <tr><td class="py-1">Asal Lembaga / Kampus</td><td>:</td><td>{{ $penilaian->magang->instansi_pendidikan }}</td></tr>
                <tr><td class="py-1">Program Studi / Jurusan</td><td>:</td><td>{{ $penilaian->magang->jurusan ?? '-' }}</td></tr>
                <tr><td class="py-1">Unit / Divisi Penempatan</td><td>:</td><td>{{ $penilaian->magang->divisi->nama_divisi ?? '-' }}</td></tr>
            </table>

            <table class="nilai-table">
                <thead>
                    <tr>
                        <th style="width:6%">No</th>
                        <th>Unsur / Kriteria Penilaian</th>
                        <th style="width:12%">Bobot</th>
                        <th style="width:18%">Nilai Angka</th>
                        <th style="width:18%">Nilai Terbobot</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penilaian->detail as $index => $detail)
                        @php
                            $bobot = $detail->kriteria->bobot ?? 0;
                            $terbobot = round(($detail->nilai * $bobot) / 100, 2);
                        @endphp
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td>{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</td>
                            <td class="c">{{ $bobot }}%</td>
                            <td class="c"><strong>{{ $detail->nilai }}</strong></td>
                            <td class="c">{{ number_format($terbobot, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right">TOTAL NILAI AKHIR</td>
                        <td class="c">{{ $penilaian->total_nilai }}</td>
                        <td class="c">/ 100</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right">PREDIKAT KELULUSAN</td>
                        <td colspan="2" class="c">{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})</td>
                    </tr>
                </tfoot>
            </table>

            <table class="ttd-nilai">
                <tr>
                    <td>
                        <div>Mengetahui &amp; Mengesahkan,</div>
                        <div>Pembimbing Lapangan</div>
                        <div class="ruang"></div>
                        <div class="nm">{{ $penilaian->pembimbing->nama_lengkap }}</div>
                        <div>NIP. {{ $penilaian->pembimbing->nip ?? '-' }}</div>
                    </td>
                    <td>
                        <div>Banjarbaru, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
                        <div>Kepala Dinas Kominfo</div>
                        <div class="ruang"></div>
                        <div class="nm">( .................................................. )</div>
                        <div>NIP. -</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Cetak Penilaian Action Button (Sesuai PRD penilaian (1).png) --}}
        <div class="no-print pt-2">
            <button type="button" onclick="window.print()"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-600 hover:bg-slate-700 py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] cursor-pointer">
                <span>🖨️ Cetak Penilaian</span>
            </button>
        </div>
    @endif

</div>
@endsection