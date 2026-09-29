{{-- resources/views/admin/cetak/partials/kop-surat.blade.php
     Kop resmi mengikuti Surat_Persetujuan_PKL.docx
     Logo di: storage/app/public/images/logo-kalsel.png (butuh php artisan storage:link) --}}
<style>
    .print-sheet { font-family: Arial, Helvetica, sans-serif; color:#000; background:#fff; }
    .kop-table { width:100%; border-collapse:collapse; border-bottom:3px solid #000; padding-bottom:6px; }
    .kop-table td { vertical-align:middle; padding:0 0 6px 0; border:0; }
    .kop-logo { width:80px; text-align:center; }
    .kop-logo img { width:70px; height:auto; }
    .kop-text { text-align:center; line-height:1.35; }
    .kop-text .l1 { font-size:13pt; text-transform:uppercase; }
    .kop-text .l2 { font-size:15pt; font-weight:bold; text-transform:uppercase; }
    .kop-text .l3 { font-size:9pt; }
    .kop-spacer { border-bottom:1px solid #000; margin-top:2px; margin-bottom:14px; }

    .judul-laporan { text-align:center; margin-bottom:12px; }
    .judul-laporan h3 { font-size:12pt; font-weight:bold; text-decoration:underline; text-transform:uppercase; margin:0; }
    .judul-laporan p { font-size:10pt; margin:3px 0 0; }

    .info-table { font-size:10pt; margin-bottom:10px; border-collapse:collapse; }
    .info-table td { padding:1px 0; vertical-align:top; border:0; }
    .info-table td.k { width:120px; }
    .info-table td.s { width:14px; }

    .rekap-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:9.5pt; }
    .rekap-table th, .rekap-table td { border:1px solid #000; padding:4px 5px; vertical-align:top; word-wrap:break-word; }
    .rekap-table th { background:#e5e5e5; text-align:center; font-weight:bold; text-transform:uppercase; font-size:9pt; vertical-align:middle; }
    .rekap-table td.c { text-align:center; }
    .rekap-table .sub { font-size:8pt; color:#333; }
    .rekap-table tr { page-break-inside:avoid; }
    .rekap-table thead { display:table-header-group; }

    .ringkasan { font-size:10pt; margin:12px 0 6px; }
    .ringkasan strong { font-weight:bold; }
    .catatan-dok { font-size:9pt; margin-top:10px; }
    .catatan-dok p { margin:1px 0; }

    .ttd-wrap { margin-top:24px; display:flex; justify-content:flex-end; page-break-inside:avoid; font-size:10.5pt; }
    .ttd-box { width:290px; text-align:center; line-height:1.4; }
    .ttd-space { height:70px; }
    .ttd-nama { font-weight:bold; text-decoration:underline; }

    @media print {
        @page { size:A4; margin:18mm 16mm 18mm 16mm; }
        body { background:#fff !important; }
        .print-sheet { border:0 !important; box-shadow:0 !important; padding:0 !important; border-radius:0 !important; }
    }
</style>

<table class="kop-table">
    <tr>
        <td class="kop-logo">
            <img src="{{ asset('storage/images/logo-kalsel.png') }}" alt="Logo Provinsi Kalimantan Selatan">
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