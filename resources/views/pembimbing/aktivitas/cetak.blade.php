<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Log Aktivitas Harian Peserta Binaan</title>
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
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .catatan-dok {
            font-size: 8pt;
            color: #4b5563;
            margin-top: 6px;
        }
        .catatan-dok p {
            margin: 1px 0;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 20px;
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

    <!-- Kop Surat -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" width="65" alt="Logo Kalsel">
                @else
                    <img src="{{ asset('storage/images/logo-kalsel.png') }}" width="65" alt="Logo Kalsel">
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

    <!-- Tabel Rekapitulasi Aktivitas (No, Nama, Rencana Kerja, Hari/Tanggal, Progres) -->
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 22%;">Nama</th>
                <th style="width: 26%;">Rencana Kerja</th>
                <th style="width: 18%;">Hari / Tanggal</th>
                <th style="width: 29%;">Progres</th>
            </tr>
        </thead>
        <tbody>
            @php $noUrut = 1; @endphp
            @forelse ($rekapPeserta as $p)
                @php $firstRowPeserta = true; @endphp
                @forelse ($p['rencana_kerja_list'] as $rkIdx => $rk)
                    @php $firstRowRk = true; @endphp
                    @foreach ($rk['items'] as $item)
                        <tr>
                            @if ($firstRowPeserta)
                                <td class="c" rowspan="{{ $p['total_rowspan'] }}">{{ $noUrut++ }}</td>
                                <td rowspan="{{ $p['total_rowspan'] }}">
                                    <strong>{{ $p['nama_lengkap'] }}</strong>
                                    @if(!empty($p['no_induk']) && $p['no_induk'] !== '-')
                                        <div class="sub">NIS/NIM: {{ $p['no_induk'] }}</div>
                                    @endif
                                    @if(!empty($p['divisi']) && $p['divisi'] !== '-')
                                        <div class="sub">{{ $p['divisi'] }}</div>
                                    @endif
                                    @if(!empty($p['instansi']) && $p['instansi'] !== '-')
                                        <div class="sub">{{ $p['instansi'] }}</div>
                                    @endif
                                </td>
                                @php $firstRowPeserta = false; @endphp
                            @endif

                            @if ($firstRowRk)
                                <td rowspan="{{ count($rk['items']) }}">
                                    <strong>{{ $rkIdx + 1 }}. {{ $rk['judul'] }}</strong>
                                    @if(!empty($rk['deskripsi']))
                                        <div class="sub">{{ $rk['deskripsi'] }}</div>
                                    @endif
                                </td>
                                @php $firstRowRk = false; @endphp
                            @endif

                            <td class="c">
                                {{ \Carbon\Carbon::parse($item['tanggal'])->locale('id')->isoFormat('dddd, D MMM Y') }}
                            </td>
                            <td>
                                <div style="white-space: pre-line;">{{ $item['progres'] }}</div>
                                @if($item['status'] === 'revisi')
                                    <div style="font-size: 7.5pt; color: #dc2626; margin-top: 2px;">
                                        <em>(Perlu Revisi{{ !empty($item['catatan']) ? ': '.$item['catatan'] : '' }})</em>
                                    </div>
                                @elseif($item['status'] === 'pending')
                                    <div style="font-size: 7.5pt; color: #d97706; margin-top: 2px;">
                                        <em>(Menunggu Validasi)</em>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td class="c">{{ $noUrut++ }}</td>
                        <td>
                            <strong>{{ $p['nama_lengkap'] }}</strong>
                            @if(!empty($p['no_induk']) && $p['no_induk'] !== '-')
                                <div class="sub">NIS/NIM: {{ $p['no_induk'] }}</div>
                            @endif
                        </td>
                        <td class="c" style="color: #64748b;">-</td>
                        <td class="c" style="color: #64748b;">-</td>
                        <td class="c" style="color: #64748b;">Belum ada catatan aktivitas pada periode ini</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="5" class="c" style="padding: 16px;">Tidak ada catatan aktivitas peserta binaan untuk parameter filter yang dipilih.</td>
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

    <!-- Catatan -->
    <div class="catatan-dok">
        <p>Catatan:</p>
        <p>1. Log aktivitas ini merupakan bukti sah pengerjaan tugas peserta magang yang dibina.</p>
        <p>2. Hanya mencakup aktivitas peserta selama berada dalam periode penugasan di bawah bimbingan pembimbing terkait.</p>
    </div>

    <!-- Tanda Tangan Ganda -->
    @php
        $bidangTtd = $divisi?->bidang_induk ?? $divisi;
    @endphp
    <table class="ttd-table">
        <tr>
            <td style="width: 50%;">
                <div class="ttd-box">
                    <div>Mengetahui &amp; Mengesahkan,</div>
                    <div>Pembimbing Lapangan</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-nama">{{ $pembimbing->nama_lengkap ?? '( .................................................. )' }}</div>
                    <div>NIP. {{ $pembimbing->nip ?? '-' }}</div>
                    <div>{{ $pembimbing->jabatan ?? 'Pembimbing Lapangan' }}</div>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="ttd-box">
                    <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                    <div>{{ $bidangTtd->jabatan_pimpinan ?? 'Kepala Bidang' }}</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-nama">{{ $bidangTtd->nama_pimpinan ?? '( .................................................. )' }}</div>
                    <div>NIP. {{ $bidangTtd->nip_pimpinan ?? '-' }}</div>
                    <div>{{ $bidangTtd->nama_divisi ?? ($pengaturan->nama_instansi ?? 'Dinas Terkait') }}</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
