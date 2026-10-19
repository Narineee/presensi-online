@extends('layouts.print')

@section('title', ($isSingle ?? false) ? ('Laporan Penilaian Akhir Magang - ' . ($penilaian->magang->nama_lengkap ?? 'Peserta')) : 'Laporan Penilaian Akhir Magang')

@section('content')
@if($isSingle ?? false)
    {{-- ========================================================================= --}}
    {{-- 1. CETAK PER PESERTA MAGANG                                               --}}
    {{-- Format identik dengan tampilan lembar nilai akhir pada magang             --}}
    {{-- Berisi nilai yang diberikan oleh pembimbing ini (bukan akumulasi)         --}}
    {{-- ========================================================================= --}}
    <div class="w-full flex justify-center">
        <div class="print-sheet bg-white border border-slate-300">

            @include('admin.cetak.partials.kop-surat')

            {{-- Judul Lembar Penilaian --}}
            <div class="judul-laporan">
                <h3>LAPORAN PENILAIAN AKHIR MAGANG</h3>
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

            @include('admin.cetak.partials.ttd-dua', [
                'divisi' => $penilaian->penempatanMagang?->divisi ?? $penilaian->magang?->divisi ?? $divisi,
                'pembimbing' => $penilaian->pembimbing ?? $pembimbing,
                'pengaturan' => $pengaturan,
            ])

        </div>
    </div>
@else
    {{-- ========================================================================= --}}
    {{-- 2. CETAK REKAPITULASI SELURUH PESERTA MAGANG YANG DIBINA                  --}}
    {{-- Kolom Divisi & Asal Lembaga diganti menjadi Asal Lembaga saja             --}}
    {{-- ========================================================================= --}}
    <div class="max-w-5xl mx-auto">
        <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

            @include('admin.cetak.partials.kop-surat')

            <div class="judul-laporan">
                <h3>LAPORAN PENILAIAN AKHIR MAGANG</h3>
                <p>Rekapitulasi Evaluasi Hasil Penilaian Praktik Kerja Lapangan (PKL)</p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="k">Pembimbing Lapangan</td><td class="s">:</td>
                    <td><strong>{{ $pembimbing->nama_lengkap ?? '-' }}</strong> (NIP: {{ $pembimbing->nip ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="k">Filter Status</td><td class="s">:</td>
                    <td>{{ request('status_nilai') ? (request('status_nilai') === 'sudah' ? 'Sudah Dinilai' : 'Belum Dinilai') : 'Semua Status' }}</td>
                </tr>
                <tr>
                    <td class="k">Waktu Cetak</td><td class="s">:</td>
                    <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
                </tr>
            </table>

            <table class="rekap-table" style="font-size: 8.5pt;">
                <thead>
                    <tr>
                        <th style="width:4%">No</th>
                        <th style="width:24%">Peserta Magang</th>
                        <th style="width:20%">Asal Lembaga</th>
                        @foreach($kriteriaList as $idx => $k)
                            <th style="width:{{ round(28 / max(1, count($kriteriaList))) }}%" title="{{ $k->nama }} ({{ $k->bobot }}%)">
                                {{ chr(65 + $idx) }}
                            </th>
                        @endforeach
                        <th style="width:8%">Total</th>
                        <th style="width:8%">Predikat</th>
                        <th style="width:8%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($magangList as $index => $item)
                        <tr>
                            <td class="c">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->nama_lengkap }}</strong>
                                <div class="sub">NIS/NIM: {{ $item->no_induk }}</div>
                            </td>
                            <td>
                                {{ $item->instansi_pendidikan ?? '-' }}
                            </td>
                            @foreach($kriteriaList as $k)
                                @php
                                    $detailRow = $item->penilaian_saya?->detail?->firstWhere('kriteria_id', $k->id);
                                    $nilaiVal = $detailRow ? $detailRow->nilai : '-';
                                @endphp
                                <td class="c font-semibold">{{ $nilaiVal }}</td>
                            @endforeach
                            <td class="c"><strong>{{ $item->penilaian_saya ? $item->penilaian_saya->total_nilai : '-' }}</strong></td>
                            <td class="c"><strong>{{ $item->penilaian_saya ? $item->penilaian_saya->predikat : '-' }}</strong></td>
                            <td class="c">{{ $item->penilaian_saya ? 'Sudah' : 'Belum' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($kriteriaList) }}" class="c" style="padding:16px;">Tidak ada data peserta magang binaan yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Rincian Penilaian (Legend Huruf Kriteria) --}}
            <div style="margin-top: 12px; font-size: 8.5pt; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px;">
                <strong style="color: #1e293b;">Rincian / Keterangan Unsur Penilaian:</strong>
                <div style="margin-top: 4px; color: #334155;">
                    @foreach($kriteriaList as $idx => $k)
                        <span style="display: inline-block; margin-right: 14px; margin-bottom: 4px;"><strong>{{ chr(65 + $idx) }}</strong> = {{ $k->nama }} (Bobot: {{ $k->bobot }}%{{ $k->is_presensi ? ' &bull; Skor Presensi Objektif' : '' }})</span>
                    @endforeach
                </div>
            </div>

            <table style="width: 100%; margin-top: 35px; border-collapse: collapse; page-break-inside: avoid;">
                <tr>
                    <td style="width: 60%; border: none;"></td>
                    <td style="width: 40%; text-align: center; border: none; font-size: 10pt; line-height: 1.4;">
                        <div>{{ $pengaturan->kota_surat ?? 'Banjarmasin' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                        <div style="margin-top: 4px; font-weight: bold;">Pembimbing Lapangan,</div>
                        <div style="height: 60px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">{{ $pembimbing->nama_lengkap }}</div>
                        <div style="font-size: 9pt;">NIP. {{ $pembimbing->nip ?? '-' }}</div>
                    </td>
                </tr>
            </table>

        </div>
    </div>
@endif
@endsection
