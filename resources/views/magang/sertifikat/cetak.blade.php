<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Magang - {{ $magang->nama_lengkap ?? 'Peserta' }}</title>
    <style>
        @page {
            margin: 0;
            background-color: #ebf1ff;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, "Nimbus Sans L", sans-serif;
            color: #0c3773;
        }
        .corner-tl {
            position: absolute;
            top: 0mm;
            left: 0mm;
            width: 60mm;
        }
        .corner-br {
            position: absolute;
            top: 138mm;
            left: 212mm;
            width: 85mm;
        }
        .cert-card-abs {
            position: absolute;
            top: 8mm;
            left: 10mm;
            width: 277mm;
            height: 194mm;
            background: #ffffff;
            border: 3.5px solid #bae6fd;
            box-sizing: border-box;
        }
        .section-table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>

{{-- Dekorasi Sudut Kiri Atas (Diletakkan sebelum kartu agar kartu menimpa bagian bawahnya) --}}
<div class="corner-tl">
    @if(!empty($bahan1Base64))
        <img src="{{ $bahan1Base64 }}" style="width: 60mm;" alt="Dekorasi Sudut Kiri Atas">
    @else
        <img src="{{ asset('images/sertifikat/bahan-1.png') }}" style="width: 60mm;" alt="Dekorasi Sudut Kiri Atas">
    @endif
</div>

{{-- Dekorasi Sudut Kanan Bawah --}}
<div class="corner-br">
    @if(!empty($bahan2Base64))
        <img src="{{ $bahan2Base64 }}" style="width: 85mm;" alt="Dekorasi Sudut Kanan Bawah">
    @else
        <img src="{{ asset('images/sertifikat/bahan-2.png') }}" style="width: 85mm;" alt="Dekorasi Sudut Kanan Bawah">
    @endif
</div>

{{-- Kartu Sertifikat Putih Utama --}}
<div class="cert-card-abs">
    <div style="padding: 2.5mm; height: 189mm;">
        <div style="border: 1.5px solid #e0f2fe; height: 184mm; padding: 6mm 10mm 4mm 10mm; box-sizing: border-box; text-align: center;">

            <table class="section-table" style="height: 100%;">
                {{-- 1. Header Judul --}}
                <tr>
                    <td style="text-align: center; vertical-align: top; height: 26mm;">
                        <h1 style="font-size: 42pt; font-weight: 900; letter-spacing: 0.2em; color: #0c3773; margin: 0; line-height: 1; text-transform: uppercase;">
                            SERTIFIKAT
                        </h1>
                        <p style="font-size: 14pt; font-weight: bold; color: #0c3773; margin: 3.5mm 0 0 0; letter-spacing: 0.05em;">
                            Diberikan kepada:
                        </p>
                    </td>
                </tr>

                {{-- 2. Nama Peserta (Font Cursive Emas Great Vibes) --}}
                <tr>
                    <td style="text-align: center; vertical-align: middle; height: 32mm;">
                        <p style="font-family: greatvibes, 'Great Vibes', 'Brush Script MT', cursive; font-size: 58pt; color: #c48a28; margin: 0; line-height: 1;">
                            ({{ $penilaian->magang->nama_lengkap ?? ($magang->nama_lengkap ?? 'Peserta') }})
                        </p>
                    </td>
                </tr>

                {{-- 3. Paragraf Kegiatan PKL / Magang --}}
                <tr>
                    <td style="text-align: center; vertical-align: middle; height: 26mm;">
                        <div style="font-size: 13.5pt; font-weight: 600; color: #0c3773; line-height: 1.6; width: 88%; margin: 0 auto;">
                            Telah melaksanakan Kegiatan Praktik Kerja Lapangan (PKL)<br> di {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }} Provinsi Kalimantan Selatan<br>
                            Pada Tanggal {{ $tanggalMulai }} sampai dengan {{ $tanggalSelesai }}
                        </div>
                    </td>
                </tr>

                {{-- 4. Paragraf Evaluasi Nilai Akhir --}}
                <tr>
                    <td style="text-align: center; vertical-align: middle; height: 24mm;">
                        <p style="font-size: 13pt; font-weight: 500; color: #0c3773; margin: 0;">
                            Berdasarkan hasil evaluasi, yang bersangkutan memperoleh nilai akhir sebesar
                        </p>
                        <p style="font-size: 22pt; font-weight: 900; color: #0c3773; margin: 1.5mm 0 0 0; letter-spacing: 0.05em;">
                            [{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})]
                        </p>
                    </td>
                </tr>

                {{-- 5. Bagian Tanda Tangan (Sebelah Kanan Bawah Sesuai Desain) --}}
                <tr>
                    <td style="vertical-align: bottom; height: 60mm; padding-bottom: 2mm;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 55%;"></td>
                                <td style="width: 45%; text-align: center; color: #0c3773; padding-right: 5mm;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="text-align: center; color: #0c3773; line-height: 1.35;">
                                                <div style="font-size: 11.5pt; font-weight: 500;">
                                                    {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalCetak }}
                                                </div>
                                                <div style="font-size: 11.5pt; font-weight: bold; font-style: italic; margin-top: 1mm;">
                                                    Kepala {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            {{-- Ruang Kosong Tanda Tangan & Stempel (24mm uncollapsible) --}}
                                            <td style="height: 24mm; line-height: 24mm; text-align: center;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; color: #0c3773; line-height: 1.35;">
                                                <div style="font-size: 12.5pt; font-weight: bold; text-decoration: underline;">
                                                    {{ $pengaturan->nama_kepala_dinas ?? 'Nama Kepala Dinas' }}
                                                </div>
                                                <div style="font-size: 10.5pt; font-weight: 500; margin-top: 1mm;">
                                                    {{ $pengaturan->pangkat_golongan ?? 'Pembina Utama Muda' }}
                                                </div>
                                                <div style="font-size: 9.5pt; font-weight: 500; color: #475569; margin-top: 0.8mm;">
                                                    NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>

</body>
</html>
