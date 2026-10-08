@extends('layouts.print')

@section('title', 'Cetak Rekapitulasi Penilaian Magang')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>REKAPITULASI PENILAIAN AKHIR MAGANG</h3>
            <p>Laporan Evaluasi Hasil Belajar &amp; Praktik Kerja Lapangan (PKL)</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="k">Filter Status</td><td class="s">:</td>
                <td>{{ request('status_nilai') ? (request('status_nilai') === 'sudah' ? 'Sudah Dinilai' : 'Belum Dinilai') : 'Semua Status' }}</td>
            </tr>
            <tr><td class="k">Waktu Cetak</td><td class="s">:</td><td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:22%">Peserta Magang</th>
                    <th style="width:22%">Divisi &amp; Asal Lembaga</th>
                    <th style="width:20%">Pembimbing Lapangan</th>
                    <th style="width:10%">Nilai Akhir</th>
                    <th style="width:10%">Predikat</th>
                    <th style="width:11%">Status</th>
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
                        <td>
                            {{ $item->pembimbing->nama_lengkap ?? '-' }}
                            <div class="sub">NIP: {{ $item->pembimbing->nip ?? '-' }}</div>
                        </td>
                        <td class="c"><strong>{{ $item->penilaian ? $item->penilaian->total_nilai : '-' }}</strong></td>
                        <td class="c"><strong>{{ $item->penilaian ? $item->penilaian->predikat : '-' }}</strong></td>
                        <td class="c">{{ $item->penilaian ? 'Sudah Dinilai' : 'Belum Dinilai' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="c" style="padding:16px;">Tidak ada data penilaian yang sesuai dengan parameter filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Peserta {{ $stats['total'] }};
            Sudah Dinilai {{ $stats['sudah'] }};
            Belum Dinilai {{ $stats['belum'] }};
            Rata-rata Nilai {{ number_format($stats['rata_rata'], 1) }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Rekapitulasi penilaian ini merupakan kompilasi resmi dari evaluasi berkala dan penilaian akhir.</p>
            <p>2. Penilaian dihitung secara terbobot berdasarkan kriteria penilaian resmi instansi.</p>
        </div>

        @include('admin.cetak.partials.ttd-kepala')

    </div>
</div>
@endsection