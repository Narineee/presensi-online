<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Penilaian Magang</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            font-size: 9pt;
            line-height: 1.35;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
        }
        .kop-table td {
            vertical-align: middle;
            padding-bottom: 5px;
            border: none;
        }
        .kop-logo {
            width: 75px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            line-height: 1.3;
        }
        .kop-text .l1 {
            font-size: 11pt;
            text-transform: uppercase;
        }
        .kop-text .l2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text .l3 {
            font-size: 8pt;
            color: #222222;
        }
        .kop-spacer {
            border-bottom: 1px solid #000000;
            margin-top: 2px;
            margin-bottom: 12px;
        }
        .judul-laporan {
            text-align: center;
            margin-bottom: 12px;
        }
        .judul-laporan h3 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0 0 3px 0;
        }
        .judul-laporan p {
            font-size: 9.5pt;
            margin: 0;
            color: #111827;
        }
        .info-table {
            width: 100%;
            font-size: 8.5pt;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
            border: none;
        }
        .info-table td.k {
            width: 95px;
        }
        .info-table td.s {
            width: 12px;
        }
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.5pt;
        }
        .rekap-table th, .rekap-table td {
            border: 1px solid #000000;
            padding: 5px 4px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .rekap-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .rekap-table td.c {
            text-align: center;
        }
        .rekap-table .sub {
            font-size: 7.5pt;
            color: #4b5563;
            margin-top: 2px;
        }
        .rekap-table tr {
            page-break-inside: avoid;
        }
        .rekap-table thead {
            display: table-header-group;
        }
        .keterangan-box {
            font-size: 8.5pt;
            margin-top: 10px;
            margin-bottom: 6px;
            padding: 6px 10px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            page-break-inside: avoid;
        }
        .keterangan-title {
            font-weight: bold;
            margin-bottom: 4px;
            text-decoration: underline;
        }
        .keterangan-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            font-size: 8pt;
        }
        .keterangan-table td {
            padding: 2px 4px;
            border: none;
            vertical-align: top;
        }
        .ringkasan {
            font-size: 8.5pt;
            margin: 6px 0;
            padding: 5px 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .ttd-table td {
            border: none;
            vertical-align: top;
        }
        .ttd-box {
            text-align: center;
            font-size: 9pt;
            line-height: 1.35;
        }
        .ttd-space {
            height: 55px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" width="65" alt="Logo Kalsel">
                @endif
            </td>
            <td class="kop-text">
                <div class="l1">Pemerintah Provinsi Kalimantan Selatan</div>
                <div class="l2">{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}</div>
                <div class="l3">{{ $pengaturan->alamat_instansi ?? 'Jl. Dharma Praja II No. 2 Banjarbaru, Kalimantan Selatan' }}</div>
                <div class="l3">(Kawasan Perkantoran Pemerintah Provinsi Kalimantan Selatan)</div>
                <div class="l3">Telepon 0511-6749844; Pos-el <i>diskominfo@kalselprov.go.id</i></div>
                <div class="l3">Laman <i>diskominfo.kalselprov.go.id;</i> instagram <i>@diskominfokalselprov</i></div>
            </td>
            <td class="kop-logo"></td>
        </tr>
    </table>
    <div class="kop-spacer"></div>

    <!-- Judul & Subjudul -->
    <div class="judul-laporan">
        <h3>LAPORAN REKAPITULASI PENILAIAN MAGANG</h3>
        <p>Laporan Evaluasi Hasil Penilaian Akhir Peserta Magang</p>
    </div>

    <!-- Info Laporan -->
    <table class="info-table">
        <tr>
            <td class="k">Cakupan Data</td>
            <td class="s">:</td>
            <td>Seluruh Peserta Magang</td>
        </tr>
        <tr>
            <td class="k">Filter Status</td>
            <td class="s">:</td>
            <td>{{ request('status_nilai') ? (request('status_nilai') === 'sudah' ? 'Sudah Dinilai' : 'Belum Dinilai') : 'Semua Status' }}</td>
        </tr>
        <tr>
            <td class="k">Waktu Cetak</td>
            <td class="s">:</td>
            <td>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
        </tr>
    </table>

    <!-- Tabel Rekap Penilaian dengan Sub-Kolom Kriteria Penilaian (A, B, C, ...) -->
    <table class="rekap-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No</th>
                <th rowspan="2" style="width: 27%;">Nama</th>
                <th colspan="{{ max(count($kriteriaList), 1) }}">Kriteria Penilaian</th>
                <th rowspan="2" style="width: 12%;">Nilai Akhir</th>
                <th rowspan="2" style="width: 14%;">Predikat</th>
            </tr>
            <tr>
                @forelse ($kriteriaList as $k)
                    <th style="width: {{ 42 / max(count($kriteriaList), 1) }}%;">{{ $kriteriaMap[$k->id] ?? 'K' }}</th>
                @empty
                    <th style="width: 42%;">-</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapList as $index => $item)
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td style="vertical-align: top;">
                        <strong>{{ $item->nama_lengkap }}</strong>
                        @if($item->divisi)
                            <div class="sub">{{ $item->divisi->nama_divisi }}</div>
                        @elseif($item->no_induk)
                            <div class="sub">NIS/NIM: {{ $item->no_induk }}</div>
                        @endif
                    </td>
                    @forelse ($kriteriaList as $k)
                        <td class="c" style="font-weight: 500;">
                            {{ $item->nilai_per_kriteria[$k->id] ?? '-' }}
                        </td>
                    @empty
                        <td class="c">-</td>
                    @endforelse
                    <td class="c" style="font-weight: bold; font-size: 9.5pt;">
                        {{ $item->nilai_akhir }}
                    </td>
                    <td class="c">
                        @if($item->is_dinilai)
                            <strong>{{ $item->predikat }}</strong>
                            @if($item->keterangan_predikat)
                                <div class="sub">{{ $item->keterangan_predikat }}</div>
                            @endif
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + max(count($kriteriaList), 1) }}" class="c" style="padding: 16px;">Tidak ada data penilaian yang sesuai dengan parameter filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Keterangan Kriteria Penilaian di Bawah Tabel -->
    <div class="keterangan-box">
        <div class="keterangan-title">Keterangan:</div>
        <table class="keterangan-table">
            <tr>
                @foreach ($kriteriaLegends as $kLeg)
                    <td><strong>{{ $kLeg['code'] }} = {{ $kLeg['nama'] }}@if(!empty($kLeg['bobot'])) ({{ $kLeg['bobot'] }}%)@endif</strong></td>
                    @if(($loop->iteration % 3 == 0) && !$loop->last)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Ringkasan -->
    <div class="ringkasan">
        <strong>Ringkasan:</strong>
        Total Peserta {{ $stats['total'] }};
        Sudah Dinilai {{ $stats['sudah'] }};
        Belum Dinilai {{ $stats['belum'] }};
        Rata-rata Nilai {{ number_format($stats['rata_rata'], 1) }}.
    </div>

    <!-- Tanda Tangan Kepala Dinas -->
    <table class="ttd-table">
        <tr>
            <td style="width: 58%;"></td>
            <td style="width: 42%;">
                <div class="ttd-box">
                    <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                    <div>{{ $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas' }}</div>
                    <div>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-nama">{{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}</div>
                    @if(!empty($pengaturan->pangkat_golongan))
                        <div>{{ $pengaturan->pangkat_golongan }}</div>
                    @endif
                    <div>NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>