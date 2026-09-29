@extends('layouts.print')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>Rekapitulasi Log Aktivitas Harian Magang</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            <tr><td class="k">Status Filter</td><td class="s">:</td><td>{{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</td></tr>
            <tr><td class="k">Waktu Cetak</td><td class="s">:</td><td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:12%">Tanggal</th>
                    <th style="width:18%">Nama Peserta</th>
                    <th style="width:33%">Uraian Tugas / Pekerjaan Harian</th>
                    <th style="width:8%">Progres</th>
                    <th style="width:10%">Status</th>
                    <th style="width:14%">Catatan Pembimbing</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aktivitas as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}</td>
                        <td>
                            <strong>{{ $item->nama_lengkap }}</strong>
                            @if($item->pengguna && $item->pengguna->magang && $item->pengguna->magang->divisi)
                                <div class="sub">{{ $item->pengguna->magang->divisi->nama_divisi }}</div>
                            @endif
                        </td>
                        <td>{{ $item->isi }}</td>
                        <td class="c">{{ $item->progress }}%</td>
                        <td class="c">
                            @if($item->status === 'approve') Disetujui
                            @elseif($item->status === 'pending') Menunggu
                            @else Revisi
                            @endif
                        </td>
                        <td>
                            {{ $item->catatan_validasi ?? '-' }}
                            @if($item->validator)
                                <div class="sub">Oleh: {{ $item->nama_validator }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="c" style="padding:16px;">Tidak ada catatan aktivitas yang sesuai dengan parameter filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Catatan {{ $stats['total'] }};
            Disetujui {{ $stats['approve'] }};
            Menunggu Review {{ $stats['pending'] }};
            Perlu Revisi {{ $stats['revisi'] }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Log aktivitas ini merupakan bukti sah pelaksanaan tugas peserta magang.</p>
            <p>2. Setiap aktivitas yang disetujui telah diverifikasi oleh Pembimbing Lapangan.</p>
        </div>

        @include('admin.cetak.partials.ttd-kepala')

    </div>
</div>
@endsection