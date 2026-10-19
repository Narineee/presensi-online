<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi Siswa/Mahasiswa Magang</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            font-size: 11pt;
            line-height: 1.35;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
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





        .surat-header-date {
            text-align: right;
            font-size: 11pt;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
            border: none;
        }
        .meta-table td.col-label {
            width: 80px;
        }
        .meta-table td.col-sep {
            width: 16px;
        }

        .tujuan-surat {
            margin-bottom: 14px;
            font-size: 11pt;
            line-height: 1.35;
        }

        .salam-pembuka {
            margin-bottom: 10px;
            font-size: 11pt;
            line-height: 1.35;
        }

        .paragraf-surat {
            text-align: justify;
            text-indent: 36px;
            margin: 0 0 10px 0;
            font-size: 11pt;
            line-height: 1.35;
        }

        .identitas-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 20px;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        .identitas-table td {
            padding: 2px 0;
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

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 20px;
            page-break-inside: avoid;
            font-size: 11pt;
        }
        .ttd-table td {
            border: none;
            vertical-align: top;
        }
        .ttd-box {
            text-align: center;
            line-height: 1.35;
        }
        .ttd-space {
            height: 60px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
        .ttd-note {
            font-size: 8.5pt;
            color: #333333;
            margin-top: 2px;
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

    {{-- Kop Surat (Sesuai views/admin/cetak/partials/kop-surat.blade.php) --}}
        <!-- Kop Surat (Sesuai views/admin/presensi/cetak.blade.php) -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{$logoBase64}}" width="65" alt="Logo Kalsel">
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

    {{-- Tanggal Keluar Surat (di bawah kop surat sebelah kanan) --}}
    <div class="surat-header-date">
        {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}
    </div>

    {{-- Nomor dan Perihal Surat --}}
    <table class="meta-table">
        <tr>
            <td class="col-label">Nomor</td>
            <td class="col-sep">:</td>
            <td>{{ (!empty($nomorSurat) && $nomorSurat !== '-') ? $nomorSurat : '' }}</td>
        </tr>
        <tr>
            <td class="col-label">Perihal</td>
            <td class="col-sep">:</td>
            <td>Laporan Rekapitulasi Presensi Siswa/Mahasiswa Magang</td>
        </tr>
    </table>

    {{-- Kepada Yth. --}}
    <div class="tujuan-surat">
        <div>Kepada Yth.</div>
        <div><strong>{{ $user->magang?->instansi_pendidikan ?? '(nama instansi Pendidikan)' }}</strong></div>
    </div>

    {{-- Salam Pembuka --}}
    <div class="salam-pembuka">
        <div>Assalamu’alaikum warahmatullahi wabarakatuh.</div>
        <div>Dengan hormat,</div>
    </div>

    {{-- Paragraf Pengantar --}}
    <p class="paragraf-surat">
        Sehubungan dengan telah berakhirnya pelaksanaan kegiatan magang di <strong>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika Provinsi Kalimantan Selatan' }}</strong>, bersama ini kami sampaikan laporan rekapitulasi presensi siswa/mahasiswa sebagai bentuk dokumentasi dan pertanggungjawaban atas pelaksanaan kegiatan magang tersebut.
    </p>

    <p style="text-align: justify; margin: 0 0 8px 0; font-size: 11pt; line-height: 1.35;">
        Adapun yang bersangkutan adalah sebagai berikut:
    </p>

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

    {{-- Paragraf Pengantar Tabel Rekap --}}
    <p class="paragraf-surat">
        Selama periode tersebut, mahasiswa yang bersangkutan telah melaksanakan kegiatan magang di <strong>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika Provinsi Kalimantan Selatan' }}</strong>. Berdasarkan hasil rekapitulasi presensi, diperoleh rincian kehadiran sebagai berikut:
    </p>

    {{-- Tabel Rekapitulasi Presensi (9 Kolom sesuai contoh-rekap.docx) --}}
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
                    <td class="c">{{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}</td>
                    <td class="c">{{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}</td>
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

    {{-- Paragraf Penutup Surat --}}
    <p class="paragraf-surat">
        Rekapitulasi presensi secara lengkap terlampir bersama surat ini sebagai bahan dokumentasi dan evaluasi bagi pihak terkait.
    </p>
    <p class="paragraf-surat">
        Demikian surat penyampaian rekapitulasi presensi ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.
    </p>

    <div style="margin-bottom: 16px; font-size: 11pt; line-height: 1.35;">
        Wassalamu’alaikum warahmatullahi wabarakatuh.
    </div>

    {{-- Bagian Tanda Tangan (sesuai contoh-rekap.docx) --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 52%;"></td>
            <td style="width: 48%;">
                <div class="ttd-box">
                    <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                    <div style="margin-top: 2px;">Hormat kami,</div>
                    <div style="font-weight: bold;">{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-nama">{{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}</div>
                    <div>{{ $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas' }}</div>
                    @if(!empty($pengaturan->nip_kepala_dinas))
                        <div>NIP. {{ $pengaturan->nip_kepala_dinas }}</div>
                    @endif
                    <div class="ttd-note">(tanda tangan dan stempel)</div>
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>