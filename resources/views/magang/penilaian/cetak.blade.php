@extends('layouts.print')

@section('title', 'Lembar Penilaian Akhir Magang')

@section('styles')
<style>
    .print-sheet, .print-sheet * {
        font-family: 'Times New Roman', Times, serif;
    }
    .surat-header-date {
        text-align: right;
        font-size: 10pt;
        margin-top: 4px;
        margin-bottom: 8px;
    }
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        margin-bottom: 10px;
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
        margin-bottom: 10px;
        font-size: 10pt;
        line-height: 1.35;
    }
    .salam-pembuka {
        margin-bottom: 8px;
        font-size: 10pt;
        line-height: 1.35;
    }
    .paragraf-surat {
        text-align: justify;
        text-indent: 32px;
        margin: 0 0 8px 0;
        font-size: 10pt;
        line-height: 1.35;
    }
    .ttd-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-top: 18px;
        page-break-inside: avoid;
        font-size: 10pt;
    }
    .ttd-table td {
        border: none;
        vertical-align: top;
    }
    .ttd-box {
        text-align: center;
        line-height: 1.35;
    }
    .ttd-nama {
        font-weight: bold;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        {{-- Tanggal Keluar Surat --}}
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
                <td>Lembar Penilaian Akhir Siswa/Mahasiswa Magang</td>
            </tr>
        </table>

        {{-- Kepada Yth. --}}
        <div class="tujuan-surat">
            <div>Kepada Yth.</div>
            <div><strong>{{ $penilaian->magang?->instansi_pendidikan ?? ($magang->instansi_pendidikan ?? '(nama instansi Pendidikan)') }}</strong></div>
            <div>di Tempat</div>
        </div>

        {{-- Salam Pembuka --}}
        <div class="salam-pembuka">
            <div>Assalamu’alaikum warahmatullahi wabarakatuh.</div>
            <div>Dengan hormat,</div>
        </div>

        {{-- Paragraf Pengantar --}}
        <p class="paragraf-surat">
            Sehubungan dengan telah berakhirnya pelaksanaan kegiatan magang di <strong>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika Provinsi Kalimantan Selatan' }}</strong>, bersama ini kami sampaikan Lembar Penilaian Akhir Magang siswa/mahasiswa sebagai bentuk dokumentasi dan pertanggungjawaban atas pelaksanaan kegiatan magang tersebut.
        </p>

        <p style="text-align: justify; margin: 0 0 8px 0; font-size: 10pt; line-height: 1.35;">
            Adapun yang bersangkutan adalah sebagai berikut:
        </p>

        {{-- Identitas Peserta Magang --}}
        <table class="info-table" style="width: 100%; margin-bottom: 12px;">
            <colgroup>
                <col style="width: 185px;">
                <col style="width: 15px;">
                <col>
            </colgroup>
            <tr>
                <td class="k" style="width: 185px; white-space: nowrap;">Nama Peserta Magang</td>
                <td class="s" style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ $penilaian->magang->nama_lengkap ?? ($magang->nama_lengkap ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Nomor Induk (NIS/NIM)</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->no_induk ?? ($magang->no_induk ?? '-') }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Asal Lembaga / Kampus</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->instansi_pendidikan ?? ($magang->instansi_pendidikan ?? '-') }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Program Studi / Jurusan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->jurusan ?? ($magang->jurusan ?? '-') }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Unit / Divisi Penempatan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->penempatanMagang->divisi->nama_divisi ?? ($penilaian->magang->divisi->nama_divisi ?? ($magang->divisi->nama_divisi ?? ($divisi->nama_divisi ?? '-'))) }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Periode Magang</td>
                <td class="s" style="text-align: center;">:</td>
                <td>
                    @php
                        $tglMulai = $penilaian->magang->tanggal_mulai ?? ($magang->tanggal_mulai ?? null);
                        $tglSelesai = $penilaian->magang->tanggal_selesai ?? ($magang->tanggal_selesai ?? null);
                    @endphp
                    @if($tglMulai && $tglSelesai)
                        {{ \Carbon\Carbon::parse($tglMulai)->locale('id')->isoFormat('D MMMM Y') }} sampai {{ \Carbon\Carbon::parse($tglSelesai)->locale('id')->isoFormat('D MMMM Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        {{-- Pengantar & Judul Tabel Penilaian --}}
        <p class="paragraf-surat" style="margin-top: 8px; margin-bottom: 6px;">
            Berdasarkan hasil evaluasi selama pelaksanaan kegiatan magang tersebut, diperoleh rincian penilaian akhir sebagai berikut:
        </p>
        <div style="font-weight: bold; font-size: 10pt; margin-bottom: 6px;">
            Tabel Penilaian Akhir Magang:
        </div>

        {{-- Tabel Nilai Akhir (No, Unsur/Kriteria Penilaian, Nilai Angka) --}}
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No</th>
                    <th style="width: 67%;">Unsur / Kriteria Penilaian</th>
                    <th style="width: 25%;">Nilai Angka</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penilaian->detail as $index => $detail)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</td>
                        <td class="c"><strong>{{ $detail->nilai }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL NILAI AKHIR</td>
                    <td class="c" style="font-weight: bold; font-size: 11pt;">{{ $penilaian->total_nilai }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">PREDIKAT KELULUSAN</td>
                    <td class="c" style="font-weight: bold;">{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})</td>
                </tr>
            </tfoot>
        </table>

        {{-- Standar Predikat --}}
        <div style="font-size: 8.5pt; color: #334155; margin-top: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1;">
            <strong>Standar Predikat:</strong>
            &bull; <strong>90 &ndash; 100:</strong> A (Sangat Baik)
            &bull; <strong>80 &ndash; 89:</strong> B (Baik)
            &bull; <strong>70 &ndash; 79:</strong> C (Cukup Baik)
            &bull; <strong>60 &ndash; 69:</strong> D (Kurang Baik)
            &bull; <strong>&lt; 60:</strong> E (Tidak Baik)
        </div>

        {{-- Hasil Karya Peserta Magang (Opsional) --}}
        @if(!empty($penilaian->hasil_karya) && count(array_filter($penilaian->hasil_karya)) > 0)
            <div style="margin-top: 10px; font-size: 9.5pt; page-break-inside: avoid;">
                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Hasil Karya Peserta Magang:</div>
                <ol style="margin: 0; padding-left: 20px; color: #334155; line-height: 1.5;">
                    @foreach(array_filter($penilaian->hasil_karya) as $karya)
                        <li>{{ $karya }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        {{-- Paragraf Penutup Surat --}}
        <p class="paragraf-surat" style="margin-top: 12px;">
            Lembar penilaian akhir magang ini disampaikan sebagai bahan dokumentasi dan evaluasi bagi pihak terkait.
        </p>
        <p class="paragraf-surat">
            Demikian penyampaian lembar penilaian akhir magang ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.
        </p>

        <div style="margin-bottom: 12px; font-size: 10pt; line-height: 1.35;">
            Wassalamu’alaikum warahmatullahi wabarakatuh.
        </div>

        {{-- Bagian Tanda Tangan (Kepala Dinas) --}}
        @php
            $jabatanTtd = $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas Komunikasi dan Informatika';
            if ($jabatanTtd === 'Kepala Dinas' && !empty($pengaturan->nama_instansi)) {
                $jabatanTtd = 'Kepala ' . $pengaturan->nama_instansi;
            }
        @endphp
        <table class="ttd-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    <div class="ttd-box">
                        <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                        <div style="margin-top: 2px;">Hormat kami,</div>
                        <div style="font-weight: bold;">{{ $jabatanTtd }}</div>
                        <br><br><br><br><br><br><br>
                        <div class="ttd-nama">{{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}</div>
                        @if(!empty($pengaturan->nip_kepala_dinas))
                            <div>NIP. {{ $pengaturan->nip_kepala_dinas }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

    </div>
</div>
@endsection
