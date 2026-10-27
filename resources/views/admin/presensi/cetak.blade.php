<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Magang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            font-size: 9pt;
            line-height: 1.35;
            background: #ffffff;
            margin: 0;
            padding: 0;
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
            vertical-align: top;
            word-wrap: break-word;
        }
        .rekap-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
            vertical-align: middle;
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
        .ringkasan {
            font-size: 8.5pt;
            margin: 10px 0 6px 0;
            padding: 5px 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Judul & Periode -->
    <div class="judul-laporan">
        <h3>LAPORAN PRESENSI MAGANG</h3>
        <p>{{ $periodeSubJudul }}</p>
    </div>

    <!-- Info Laporan -->
    <table class="info-table">
        <tr>
            <td class="k">Cakupan Data</td>
            <td class="s">:</td>
            <td>Seluruh Peserta Magang</td>
        </tr>
        <tr>
            <td class="k">Waktu Cetak</td>
            <td class="s">:</td>
            <td>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
        </tr>
    </table>

    <!-- Tabel Rekap Presensi -->
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 22%;">Nama</th>
                <th style="width: 20%;">Pembimbing</th>
                <th style="width: 22%;">Periode</th>
                <th style="width: 6%;">Hadir</th>
                <th style="width: 6%;">Sakit</th>
                <th style="width: 6%;">Izin</th>
                <th style="width: 6%;">Alfa</th>
                <th style="width: 8%;">Tugas Luar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapList as $index => $item)
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->nama_lengkap }}</strong>
                        @if($item->divisi)
                            <div class="sub">{{ $item->divisi->nama_divisi }}</div>
                        @elseif($item->no_induk)
                            <div class="sub">{{ $item->no_induk }}</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->pembimbing_nama }}</strong>
                        @if($item->pembimbing_nip)
                            <div class="sub">NIP: {{ $item->pembimbing_nip }}</div>
                        @endif
                    </td>
                    <td class="c">{{ $item->periode_teks }}</td>
                    <td class="c" style="font-weight: bold;">{{ $item->total_hadir }}</td>
                    <td class="c">{{ $item->total_sakit }}</td>
                    <td class="c">{{ $item->total_izin }}</td>
                    <td class="c">{{ $item->total_alpa }}</td>
                    <td class="c">{{ $item->total_tugas_luar }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="c" style="padding: 16px;">Tidak ada data peserta magang yang sesuai dengan parameter filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan -->
    <div class="ringkasan">
        <strong>Ringkasan:</strong>
        Total Peserta {{ $stats['total_peserta'] }};
        Total Hadir {{ $stats['total_hadir'] }};
        Total Sakit {{ $stats['total_sakit'] }};
        Total Izin {{ $stats['total_izin'] }};
        Total Alfa {{ $stats['total_alpa'] }};
        Total Tugas Luar {{ $stats['total_tugas_luar'] }}.
    </div>

</body>
</html>