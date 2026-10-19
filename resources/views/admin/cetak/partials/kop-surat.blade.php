{{-- resources/views/admin/cetak/partials/kop-surat.blade.php
     Kop resmi mengikuti format views/admin/presensi/cetak.blade.php
     Logo di: storage/app/public/images/logo-kalsel.png (butuh php artisan storage:link) --}}
<style>
    .print-sheet { font-family: Arial, Helvetica, sans-serif; color:#000; background:#fff; }
    .kop-table { width:100%; border-collapse:collapse; border-bottom:2.5px solid #000; padding-bottom:5px; font-family: Arial, Helvetica, sans-serif; }
    .kop-table td { vertical-align:middle; padding:0 0 5px 0; border:0; }
    .kop-logo { width:75px; text-align:center; }
    .kop-text { text-align:center; line-height:1.3; }
    .kop-text .l1 { font-size:11pt; text-transform:uppercase; }
    .kop-text .l2 { font-size:13pt; font-weight:bold; text-transform:uppercase; }
    .kop-text .l3 { font-size:8pt; color:#222222; }
    .kop-spacer { border-bottom:1px solid #000; margin-top:2px; margin-bottom:12px; }

    .judul-laporan { text-align:center; margin-bottom:12px; }
    .judul-laporan h3 { font-size:12pt; font-weight:bold; text-decoration:underline; text-transform:uppercase; margin:0; }
    .judul-laporan p { font-size:10pt; margin:3px 0 0; }

    .info-table { width:100%; font-size:10pt; margin-bottom:12px; border-collapse:collapse; }
    .info-table td { padding:3.5px 0; vertical-align:middle; border:0; line-height:1.45; }
    .info-table td.k { width:185px; white-space:nowrap; }
    .info-table td.s { width:15px; text-align:center; white-space:nowrap; }

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

    @if(!($isPdf ?? false))
    @media print {
        @page { size: A4 portrait; margin: 12mm 10mm 12mm 10mm; }
        body { background:#fff !important; }
        .print-sheet { border:0 !important; box-shadow:0 !important; padding:0 !important; border-radius:0 !important; }
    }
    @endif
</style>

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