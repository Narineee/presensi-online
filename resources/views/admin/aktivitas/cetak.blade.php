<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Magang</title>
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
            padding: 6px 5px;
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
        .rencana-item {
            margin-bottom: 3px;
            text-align: left;
            line-height: 1.3;
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
        <h3>LAPORAN AKTIVITAS MAGANG</h3>
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

    <!-- Tabel Rekap Aktivitas (6 Kolom: No, Nama, Pembimbing, Periode, Rencana Kerja, Hasil) -->
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Nama</th>
                <th style="width: 20%;">Pembimbing</th>
                <th style="width: 18%;">Periode</th>
                <th style="width: 25%;">Rencana Kerja</th>
                <th style="width: 12%;">Hasil</th>
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
                            <div class="sub">NIS/NIM: {{ $item->no_induk }}</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->pembimbing_nama }}</strong>
                        @if($item->pembimbing_nip)
                            <div class="sub">NIP: {{ $item->pembimbing_nip }}</div>
                        @endif
                    </td>
                    <td class="c">{{ $item->periode_teks }}</td>
                    <td>
                        @if(!empty($item->rencana_kerja_list) && count($item->rencana_kerja_list) > 0)
                            @foreach ($item->rencana_kerja_list as $rkIndex => $rkJudul)
                                <div class="rencana-item">{{ $rkIndex + 1 }}. {{ $rkJudul }}</div>
                            @endforeach
                        @else
                            <div class="c" style="color: #64748b;">-</div>
                        @endif
                    </td>
                    <td class="c" style="font-weight: bold; font-size: 9.5pt; color: {{ $item->progres_akhir === 'Selesai' ? '#15803d' : ($item->progres_akhir === '-' ? '#64748b' : '#b45309') }};">
                        {{ $item->progres_akhir }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="c" style="padding: 16px;">Tidak ada data peserta magang yang sesuai dengan parameter filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan -->
    <div class="ringkasan">
        <strong>Ringkasan:</strong>
        Total Peserta {{ $stats['total_peserta'] }};
        Peserta Selesai {{ $stats['total_selesai'] ?? 0 }};
        Total Catatan Aktivitas {{ $stats['total_aktivitas'] }}.
    </div>

</body>
</html>