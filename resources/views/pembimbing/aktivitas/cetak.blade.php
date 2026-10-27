<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Log Aktivitas Harian Peserta Binaan</title>
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
            width: 130px;
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
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
            text-align: left;
        }
        .rekap-table th {
            background-color: transparent;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .rekap-table th.c, .rekap-table td.c {
            text-align: center;
        }
        .rekap-table .sub {
            font-size: 7.5pt;
            color: #000000;
            margin-top: 2px;
        }
        .rekap-table thead {
            display: table-header-group;
        }
        .rekap-table tr {
            page-break-inside: avoid;
        }
        .ringkasan {
            font-size: 8.5pt;
            margin: 10px 0 6px 0;
            padding: 5px 8px;
            background-color: transparent;
            border: 1px solid #000000;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @if(!($isPdf ?? false))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; font-size: 9pt; display: flex; justify-content: space-between; align-items: center;" class="no-print">
        <span>📄 <strong>Pratinjau Lembar Cetak</strong> (Ukuran Kertas A4 Portrait)</span>
        <button type="button" onclick="window.print()" style="padding: 6px 14px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Dokumen
        </button>
    </div>
    @endif

    <!-- Judul & Periode -->
    <div class="judul-laporan">
        <h3>LAPORAN AKTIVITAS HARIAN PESERTA BINAAN</h3>
        <p>Periode: {{ $periodeText }}</p>
    </div>

    <!-- Info Laporan -->
    <table class="info-table">
        <tr>
            <td class="k">Pembimbing Lapangan</td>
            <td class="s">:</td>
            <td><strong>{{ $pembimbing->nama_lengkap ?? '-' }}</strong> (NIP. {{ $pembimbing->nip ?? '-' }})</td>
        </tr>
        <tr>
            <td class="k">Peserta Magang</td>
            <td class="s">:</td>
            <td>
                @if($selectedMagang)
                    <strong>{{ $selectedMagang->nama_lengkap }}</strong> (NIS/NIM: {{ $selectedMagang->no_induk ?? '-' }}) &mdash; {{ $selectedMagang->divisi->nama_divisi ?? 'Divisi Terkait' }}
                @else
                    Seluruh Peserta Magang Binaan ({{ count($rekapPeserta) }} Peserta)
                @endif
            </td>
        </tr>
        <tr>
            <td class="k">Waktu Cetak</td>
            <td class="s">:</td>
            <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
        </tr>
    </table>

    <!-- Tabel Rekapitulasi Aktivitas (No, Nama, Hari/Tanggal, Rencana Kerja, Hasil, Keterangan) -->
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="c">No</th>
                <th style="width: 20%;">Nama</th>
                <th style="width: 18%;">Hari / Tanggal</th>
                <th style="width: 28%;">Rencana Kerja</th>
                <th style="width: 14%;">Hasil</th>
                <th style="width: 16%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($aktivitas as $index => $item)
                @php
                    $m = $item->pengguna?->magang;
                    $namaPeserta = $m?->nama_lengkap ?? ($item->pengguna->username ?? 'Peserta');
                    $details = $item->details;
                    $hasMultiple = $details && $details->count() > 1;
                @endphp
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $namaPeserta }}</strong>
                        @if(!empty($m?->no_induk) && $m?->no_induk !== '-')
                            <div class="sub">NIS/NIM: {{ $m->no_induk }}</div>
                        @endif
                        @if(!empty($m?->divisi?->nama_divisi) && $m?->divisi?->nama_divisi !== '-')
                            <div class="sub">{{ $m->divisi->nama_divisi }}</div>
                        @endif
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </td>
                    <td>
                        @if($details && $details->isNotEmpty())
                            @if($hasMultiple)
                                @foreach($details as $idx => $d)
                                    <div style="margin-bottom: 2px;">{{ $idx + 1 }}. {{ $d->judul_rencana }}</div>
                                @endforeach
                            @else
                                <div>{{ $details->first()->judul_rencana }}</div>
                            @endif
                        @else
                            <div>{{ $item->judul ?? $item->pekerjaan?->judul ?? 'Aktivitas Harian' }}</div>
                        @endif
                    </td>
                    <td>
                        @if($details && $details->isNotEmpty())
                            @if($hasMultiple)
                                @foreach($details as $idx => $d)
                                    @php
                                        $progLower = strtolower(trim($d->progres));
                                        $labelHasil = in_array($progLower, ['selesai', 'belum']) ? ucfirst($progLower) : $d->progres;
                                    @endphp
                                    <div style="margin-bottom: 2px; font-weight: bold; color: #000000;">
                                        {{ $idx + 1 }}. {{ $labelHasil }}
                                    </div>
                                @endforeach
                            @else
                                @php
                                    $d = $details->first();
                                    $progLower = strtolower(trim($d->progres));
                                    $labelHasil = in_array($progLower, ['selesai', 'belum']) ? ucfirst($progLower) : $d->progres;
                                @endphp
                                <div style="font-weight: bold; color: #000000;">
                                    {{ $labelHasil }}
                                </div>
                            @endif
                        @else
                            <div>{{ ucfirst(trim($item->isi)) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($details && $details->isNotEmpty())
                            @if($hasMultiple)
                                @foreach($details as $idx => $d)
                                    @php
                                        $progLower = strtolower(trim($d->progres));
                                        $alasanText = ($progLower === 'belum' && $d->alasan) ? 'Alasan: ' . $d->alasan : '-';
                                    @endphp
                                    <div style="margin-bottom: 2px;">{{ $idx + 1 }}. {{ $alasanText }}</div>
                                @endforeach
                            @else
                                @php
                                    $d = $details->first();
                                    $progLower = strtolower(trim($d->progres));
                                    $alasanText = ($progLower === 'belum' && $d->alasan) ? 'Alasan: ' . $d->alasan : '-';
                                @endphp
                                <div>{{ $alasanText }}</div>
                            @endif
                        @else
                            <div>-</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="c" style="padding: 16px;">Tidak ada catatan aktivitas peserta binaan untuk parameter filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan -->
    <div class="ringkasan">
        <strong>Ringkasan Aktivitas:</strong>
        Total Catatan {{ $stats['total'] }};
        Disetujui {{ $stats['approve'] }};
        Menunggu Review {{ $stats['pending'] }};
        Perlu Revisi {{ $stats['revisi'] }}.
    </div>

</body>
</html>
