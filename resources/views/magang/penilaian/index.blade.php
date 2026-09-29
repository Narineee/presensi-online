@extends('layouts.user')

@section('title', 'Lembar Penilaian Akhir Magang')

@section('styles')
<style>
    @media print {
        header, footer, .no-print, nav, .btn-print-group { display: none !important; }
        *, *::before, *::after { box-shadow: none !important; scrollbar-width: none !important; }
        *::-webkit-scrollbar, ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        html, body { background-color: white !important; color: black !important; margin: 0; padding: 0; width: 100% !important; max-width: 100% !important; overflow: visible !important; overflow-x: clip !important; }
        main { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .print-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
        .page-break { page-break-after: always; }
    }

    /* Gaya khusus lembar nilai */
    .nilai-table { width:100%; border-collapse:collapse; font-size:10pt; margin-top:6px; }
    .nilai-table th, .nilai-table td { border:1px solid #000; padding:5px 7px; vertical-align:middle; }
    .nilai-table th { background:#e5e5e5; text-align:center; font-weight:bold; }
    .nilai-table td.c { text-align:center; }
    .nilai-table tr { page-break-inside:avoid; }
    .nilai-table tfoot td { font-weight:bold; background:#f3f3f3; }
    .judul-bagian { font-size:10.5pt; font-weight:bold; margin:14px 0 4px; }
    .kualifikasi { font-size:9pt; margin-top:10px; }
    .kualifikasi table { border-collapse:collapse; }
    .kualifikasi td { padding:0 14px 0 0; }
</style>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(!$penilaian)
        <!-- Belum Dinilai -->
        <div class="bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-xs text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Penilaian Akhir Belum Diterbitkan</h2>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    Pembimbing lapangan Anda belum menginput evaluasi kelulusan akhir. Pastikan seluruh absensi harian dan log aktivitas harian Anda telah lengkap dan divalidasi.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Presensi
                </a>
            </div>
        </div>
    @else
        <!-- Action Bar (No Print) -->
        <div class="no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h1 class="text-base font-bold text-slate-900">Lembar Nilai Akhir Magang</h1>
                <p class="text-xs text-slate-500">Nilai resmi dari pembimbing lapangan.</p>
            </div>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                </svg>
                Cetak Lembar Nilai (PDF)
            </button>
        </div>

        <!-- Lembar Penilaian Resmi (A4) -->
        <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

            @include('admin.cetak.partials.kop-surat')

            <div class="judul-laporan">
                <h3>Lembar Penilaian Akhir Magang</h3>
                <p>Praktik Kerja Lapangan (PKL) / Program Internship</p>
            </div>

            <!-- Identitas Peserta -->
            <table class="info-table">
                <tr><td class="k" style="width:170px">Nama Peserta Magang</td><td class="s">:</td><td><strong>{{ $penilaian->magang->nama_lengkap }}</strong></td></tr>
                <tr><td class="k">Nomor Induk (NIS/NIM)</td><td class="s">:</td><td>{{ $penilaian->magang->no_induk }}</td></tr>
                <tr><td class="k">Asal Lembaga / Kampus</td><td class="s">:</td><td>{{ $penilaian->magang->instansi_pendidikan }}</td></tr>
                <tr><td class="k">Program Studi / Jurusan</td><td class="s">:</td><td>{{ $penilaian->magang->jurusan ?? '-' }}</td></tr>
                <tr><td class="k">Unit / Divisi Penempatan</td><td class="s">:</td><td>{{ $penilaian->magang->divisi->nama_divisi ?? '-' }}</td></tr>
                <tr>
                    <td class="k">Periode Pelaksanaan</td><td class="s">:</td>
                    <td>
                        {{ $penilaian->magang->tanggal_mulai ? $penilaian->magang->tanggal_mulai->isoFormat('D MMMM Y') : '-' }}
                        s/d
                        {{ $penilaian->magang->tanggal_selesai ? $penilaian->magang->tanggal_selesai->isoFormat('D MMMM Y') : '-' }}
                    </td>
                </tr>
            </table>

            @if(isset($presensiScore))
                <!-- Rincian Evaluasi Presensi Digital -->
                <div class="judul-bagian">A. Rincian Evaluasi Presensi Digital (Objektif)</div>
                <table class="nilai-table">
                    <tbody>
                        <tr>
                            <td style="width:34%">Target Jam Kerja</td>
                            <td>{{ number_format($presensiScore['target_menit']) }} menit ({{ $presensiScore['target_hari'] }} hari kerja aktif, 8 jam/hari)</td>
                        </tr>
                        <tr>
                            <td>Total Realisasi</td>
                            <td>{{ number_format($presensiScore['total_menit_realisasi']) }} menit</td>
                        </tr>
                        <tr>
                            <td>Kehadiran &amp; Izin</td>
                            <td>{{ $presensiScore['total_hari_hadir'] }} hari hadir; {{ $presensiScore['total_hari_izin'] }} hari izin (izin disetujui dihitung 480 menit penuh)</td>
                        </tr>
                        <tr>
                            <td>Lupa Checkout / Terlambat</td>
                            <td>{{ $presensiScore['total_hari_lupa_checkout'] }} hari lupa checkout; {{ $presensiScore['total_hari_terlambat'] }}x terlambat (potongan {{ $presensiScore['menit_terlambat_potong'] }} menit; checkout dipotong 50%)</td>
                        </tr>
                        <tr>
                            <td><strong>Capaian Presensi</strong></td>
                            <td><strong>{{ $presensiScore['skor_presensi'] }}% ({{ $presensiScore['predikat'] }})</strong></td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <!-- Rincian Nilai Kriteria -->
            <div class="judul-bagian">{{ isset($presensiScore) ? 'B.' : 'A.' }} Rincian Nilai Kriteria</div>
            <table class="nilai-table">
                <thead>
                    <tr>
                        <th style="width:6%">No</th>
                        <th>Unsur / Kriteria Penilaian</th>
                        <th style="width:11%">Bobot</th>
                        <th style="width:16%">Nilai Angka (0-100)</th>
                        <th style="width:15%">Nilai Terbobot</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penilaian->detail as $index => $detail)
                        @php
                            $bobot = $detail->kriteria->bobot ?? 0;
                            $terbobot = round(($detail->nilai * $bobot) / 100, 2);
                            $isPresensi = $detail->kriteria->is_presensi ?? false;
                        @endphp
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td>
                                {{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}
                                @if($isPresensi)
                                    <em style="font-size:8.5pt;">(Objektif - Presensi)</em>
                                @endif
                            </td>
                            <td class="c">{{ $bobot }}%</td>
                            <td class="c"><strong>{{ $detail->nilai }}</strong></td>
                            <td class="c">{{ number_format($terbobot, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right; text-transform:uppercase;">Total Nilai Akhir</td>
                        <td class="c">{{ $penilaian->total_nilai }}</td>
                        <td class="c">/ 100</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right; text-transform:uppercase;">Predikat Kelulusan</td>
                        <td colspan="2" class="c">{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Standar Kualifikasi Nilai -->
            <div class="kualifikasi">
                <strong>Standar Kualifikasi Nilai:</strong>
                <table>
                    <tr>
                        <td>90 - 100 : A (Sangat Baik)</td>
                        <td>80 - 89 : B (Baik)</td>
                        <td>70 - 79 : C (Cukup Baik)</td>
                        <td>60 - 69 : D (Kurang Baik)</td>
                        <td>&lt; 60 : E (Tidak Baik)</td>
                    </tr>
                </table>
            </div>

            <!-- Tanda Tangan: Pembimbing Lapangan & Pimpinan Divisi -->
            <style>
                .ttd-nilai { width:100%; margin-top:24px; border-collapse:collapse; page-break-inside:avoid; font-size:10.5pt; }
                .ttd-nilai td { width:50%; text-align:center; vertical-align:top; line-height:1.4; padding:0 10px; border:0; }
                .ttd-nilai .ruang { height:70px; }
                .ttd-nilai .nm { font-weight:bold; text-decoration:underline; }
            </style>
            <table class="ttd-nilai">
                <tr>
                    <td>
                        <div>Mengetahui &amp; Mengesahkan,</div>
                        <div>Pembimbing Lapangan</div>
                        <div class="ruang"></div>
                        <div class="nm">{{ $penilaian->pembimbing->nama_lengkap }}</div>
                        <div>NIP. {{ $penilaian->pembimbing->nip ?? '-' }}</div>
                        <div>{{ $penilaian->pembimbing->jabatan ?? 'Pembimbing Lapangan' }}</div>
                    </td>
                    <td>
                        <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
                        <div>{{ $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas' }}</div>
                        <div>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}</div>
                        <div class="ruang"></div>
                        <div class="nm">{{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}</div>
                        @if(!empty($pengaturan->pangkat_golongan))
                            <div>{{ $pengaturan->pangkat_golongan }}</div>
                        @endif
                        <div>NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}</div>
                    </td>
                </tr>
            </table>

        </div>

    @endif

</div>
@endsection