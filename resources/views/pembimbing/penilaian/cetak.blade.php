@extends('layouts.print')

@section('title', 'Cetak Rekapitulasi Penilaian Magang')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>REKAPITULASI PENILAIAN AKHIR MAGANG BINAAN</h3>
            <p>Laporan Evaluasi Hasil Penilaian Praktik Kerja Lapangan (PKL)</p>
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
                    <th style="width:20%">Divisi &amp; Asal Lembaga</th>
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
                            <strong>{{ $item->divisi->nama_divisi ?? '-' }}</strong>
                            <div class="sub">{{ $item->instansi_pendidikan }}</div>
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

        {{-- Rincian Penilaian (Legend Huruf Kriteria) sesuai PRD: A = 80, B = 80, A = presensi --}}
        <div style="margin-top: 12px; font-size: 8.5pt; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px;">
            <strong style="color: #1e293b;">Rincian / Keterangan Unsur Penilaian:</strong>
            <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-top: 4px; color: #334155;">
                @foreach($kriteriaList as $idx => $k)
                    <div><strong>{{ chr(65 + $idx) }}</strong> = {{ $k->nama }} (Bobot: {{ $k->bobot }}%{{ $k->is_presensi ? ' &bull; Skor Presensi Objektif' : '' }})</div>
                @endforeach
            </div>
        </div>

        <div class="ttd-grid" style="margin-top: 40px; display: flex; justify-content: flex-end;">
            <div style="text-align: center; min-width: 220px;">
                <p>Banjarmasin, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
                <p style="margin-top: 4px; font-weight: bold;">Pembimbing Lapangan,</p>
                <div style="height: 60px;"></div>
                <p style="font-weight: bold; text-decoration: underline;">{{ $pembimbing->nama_lengkap }}</p>
                <p style="font-size: 9pt;">NIP. {{ $pembimbing->nip ?? '-' }}</p>
            </div>
        </div>

    </div>
</div>
@endsection
