@extends('layouts.print')

@section('title', 'Lembar Penilaian Akhir Magang')

@section('content')
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        {{-- Judul Lembar Penilaian --}}
        <div class="judul-laporan">
            <h3>LEMBAR PENILAIAN AKHIR MAGANG</h3>
            <p>Praktik Kerja Lapangan (PKL) / Program Internship</p>
        </div>

        {{-- Identitas Peserta Magang --}}
        <table class="info-table" style="width: 100%; margin-bottom: 14px;">
            <colgroup>
                <col style="width: 185px;">
                <col style="width: 15px;">
                <col>
            </colgroup>
            <tr>
                <td class="k" style="width: 185px; white-space: nowrap;">Nama Peserta Magang</td>
                <td class="s" style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ $penilaian->magang->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Nomor Induk (NIS/NIM)</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->no_induk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Asal Lembaga / Kampus</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->instansi_pendidikan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Program Studi / Jurusan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->magang->jurusan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Unit / Divisi Penempatan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $penilaian->penempatanMagang->divisi->nama_divisi ?? ($penilaian->magang->divisi->nama_divisi ?? ($divisi->nama_divisi ?? '-')) }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Periode Magang</td>
                <td class="s" style="text-align: center;">:</td>
                <td>
                    @if($penilaian->magang->tanggal_mulai && $penilaian->magang->tanggal_selesai)
                        {{ \Carbon\Carbon::parse($penilaian->magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') }} sampai {{ \Carbon\Carbon::parse($penilaian->magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

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

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection
