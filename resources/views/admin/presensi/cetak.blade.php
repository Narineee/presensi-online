@extends('layouts.print')

@section('title', 'Laporan Presensi Magang')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>LAPORAN PRESENSI MAGANG</h3>
            <p>{{ $periodeSubJudul }}</p>
        </div>

        <table class="info-table">
            <tr><td class="k">Cakupan Data</td><td class="s">:</td><td>Seluruh Peserta Magang</td></tr>
            <tr><td class="k">Waktu Cetak</td><td class="s">:</td><td>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WITA</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 22%;">Nama</th>
                    <th style="width: 20%;">Pembimbing</th>
                    <th style="width: 22%;">Periode</th>
                    <th style="width: 6%;">Hadir</th>
                    <th style="width: 6%;">Sakit</th>
                    <th style="width: 6%;">Izin</th>
                    <th style="width: 6%;">Alfa</th>
                    <th style="width: 8%;">Tugas Luar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekapList as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->nama_lengkap }}</strong>
                            @if($item->divisi)
                                <div class="sub">{{ $item->divisi->nama_divisi }}</div>
                            @elseif($item->no_induk)
                                <div class="sub">{{ $item->no_induk }}</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->pembimbing_nama }}</strong>
                            @if($item->pembimbing_nip)
                                <div class="sub">NIP: {{ $item->pembimbing_nip }}</div>
                            @endif
                        </td>
                        <td class="c">{{ $item->periode_teks }}</td>
                        <td class="c font-bold">{{ $item->total_hadir }}</td>
                        <td class="c">{{ $item->total_sakit }}</td>
                        <td class="c">{{ $item->total_izin }}</td>
                        <td class="c">{{ $item->total_alpa }}</td>
                        <td class="c">{{ $item->total_tugas_luar }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="c" style="padding: 16px;">Tidak ada data peserta magang yang sesuai dengan parameter filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Peserta {{ $stats['total_peserta'] }};
            Total Hadir {{ $stats['total_hadir'] }};
            Total Sakit {{ $stats['total_sakit'] }};
            Total Izin {{ $stats['total_izin'] }};
            Total Alfa {{ $stats['total_alpa'] }};
            Total Tugas Luar {{ $stats['total_tugas_luar'] }}.
        </div>

        @include('admin.cetak.partials.ttd-kepala')

    </div>
</div>
@endsection