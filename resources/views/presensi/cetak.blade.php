<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi Siswa/Mahasiswa Magang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            font-size: 10pt;
            line-height: 1.35;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-laporan h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .judul-laporan p {
            font-size: 10pt;
            margin: 0;
            color: #111827;
        }

        .identitas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .identitas-table td {
            padding: 2.5px 0;
            vertical-align: top;
            border: none;
        }
        .identitas-table td.label {
            width: 140px;
        }
        .identitas-table td.sep {
            width: 16px;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9pt;
            margin-top: 8px;
            margin-bottom: 14px;
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
            font-size: 9pt;
            vertical-align: middle;
        }
        .rekap-table td.c {
            text-align: center;
        }
        .rekap-table tr {
            page-break-inside: avoid;
        }
        .rekap-table thead {
            display: table-header-group;
        }

        .ringkasan {
            font-size: 9.5pt;
            margin: 12px 0 6px;
        }
        .ringkasan strong {
            font-weight: bold;
        }

        @media screen {
            body {
                background-color: #f1f5f9;
                margin: 0;
                padding: 0 0 40px 0;
            }
            .surat-container {
                width: 210mm;
                max-width: 96%;
                margin: 20px auto;
                background: #ffffff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: 1px solid #cbd5e1;
                padding: 12mm 14mm;
                box-sizing: border-box;
            }
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .surat-container {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

@if(!($isPdf ?? false))
<!-- Top Action Bar Khusus Pratinjau Lembar Cetak Browser -->
<div class="no-print" style="position: sticky; top: 0; z-index: 50; background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); font-family: sans-serif;">
    <div style="max-width: 900px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 13px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid #bfdbfe;">
                📄 Dokumen Rekapitulasi Presensi
            </span>
            <span>Pratinjau Lembar Cetak Kertas A4</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.print()" style="padding: 7px 16px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button type="button" onclick="window.close()" style="padding: 7px 14px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif

<div class="surat-container">

    {{-- Judul & Periode Laporan --}}
    <div class="judul-laporan">
        <h3>LAPORAN REKAPITULASI PRESENSI MAGANG</h3>
        <p>Periode: {{ $periodeMagangText ?? $periodeText }}</p>
    </div>

    {{-- Tabel Identitas Peserta Magang --}}
    <table class="identitas-table">
        <tr>
            <td class="label">Nama</td>
            <td class="sep">:</td>
            <td><strong>{{ $user->magang?->nama_lengkap ?? $user->username }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk</td>
            <td class="sep">:</td>
            <td>{{ $user->magang?->no_induk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jurusan</td>
            <td class="sep">:</td>
            <td>{{ $user->magang?->jurusan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Asal Instansi</td>
            <td class="sep">:</td>
            <td>{{ $user->magang?->instansi_pendidikan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Periode Magang</td>
            <td class="sep">:</td>
            <td>{{ $periodeMagangText ?? $periodeText }}</td>
        </tr>
    </table>

    {{-- Tabel Rekapitulasi Presensi (9 Kolom) --}}
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 17%;">Hari/Tgl</th>
                <th style="width: 14%;">Unit Kerja</th>
                <th style="width: 15%;">Pembimbing</th>
                <th style="width: 10%;">Mode</th>
                <th style="width: 10%;">Jam masuk</th>
                <th style="width: 10%;">Jam keluar</th>
                <th style="width: 9%;">status</th>
                <th style="width: 11%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($presensi as $index => $item)
                @php
                    $unitKerja = $user->magang?->getDivisiAt($item->tanggal)?->nama_divisi 
                        ?? $user->magang?->divisi?->nama_divisi 
                        ?? ($divisi->nama_divisi ?? '-');
                    $pembimbingNama = $user->magang?->getPembimbingAt($item->tanggal)?->nama_lengkap
                        ?? $user->magang?->pembimbing?->nama_lengkap
                        ?? ($pembimbing->nama_lengkap ?? '-');
                    $modeText = ($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar') 
                        ? 'TUGAS LUAR' 
                        : strtoupper($item->mode_kerja ?? 'ONSITE');
                    $statusBadge = $item->is_tugas_luar ? 'Hadir (TL)' : ucfirst($item->status ?? 'Hadir');
                    $keteranganText = $item->keterangan 
                        ?: (($item->mode_kerja === 'tugas_luar' && $item->pengajuanTugasLuar) ? $item->pengajuanTugasLuar->tujuan : '-');
                @endphp
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                    <td>{{ $unitKerja }}</td>
                    <td>{{ $pembimbingNama }}</td>
                    <td class="c">{{ $modeText }}</td>
                    <td class="c">{{ $item->jam_masuk_efektif ? substr($item->jam_masuk_efektif, 0, 5) . ' WITA' : '-' }}</td>
                    <td class="c">{{ $item->formatJamKeluar(':') !== '-' ? $item->formatJamKeluar(':') . ' WITA' : '-' }}</td>
                    <td class="c">{{ $statusBadge }}</td>
                    <td>{{ $keteranganText }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="c" style="padding: 16px;">Belum ada rekaman presensi pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Ringkasan --}}
    <div class="ringkasan">
        <strong>Ringkasan Presensi:</strong>
        Total Hadir {{ $stats['total_hadir'] }};
        Onsite {{ $stats['total_onsite'] }};
        WFH {{ $stats['total_wfh'] }};
        Tugas Luar {{ $stats['total_tugas_luar'] }}.
    </div>

</div>

</body>
</html>
