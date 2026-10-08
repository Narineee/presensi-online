@extends('layouts.print')

@section('title', 'Cetak Rekap Presensi')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>REKAPITULASI PRESENSI KEHADIRAN MAGANG</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            <tr><td class="k">Kategori</td><td class="s">:</td><td>Peserta Magang</td></tr>
            <tr><td class="k">Waktu Cetak</td><td class="s">:</td><td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:12%">Tanggal</th>
                    <th style="width:25%">Nama Peserta &amp; Divisi</th>
                    <th style="width:9%">Mode</th>
                    <th style="width:12%">Jam Masuk</th>
                    <th style="width:12%">Jam Pulang</th>
                    <th style="width:9%">Status</th>
                    <th style="width:16%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presensi as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}</td>
                        <td>
                            <strong>{{ $item->nama_lengkap }}</strong>
                            @php
                                $divisiRow = $item->pengguna?->magang?->getDivisiAt($item->tanggal) ?? $item->pengguna?->magang?->divisi;
                            @endphp
                            @if($divisiRow)
                                <div class="sub">{{ $divisiRow->nama_divisi }}</div>
                            @endif
                        </td>
                        <td class="c" style="text-transform:uppercase;">{{ $item->mode_kerja }}</td>
                        <td class="c">{{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c">{{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c" style="text-transform:capitalize;">{{ $item->status }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="c" style="padding:16px;">Tidak ada data presensi yang sesuai dengan parameter filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Catatan {{ $stats['total'] }};
            Total Hadir {{ $stats['hadir'] }};
            Onsite (Kantor) {{ $stats['onsite'] }};
            WFH (Remote) {{ $stats['wfh'] }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Rekapitulasi ini dihasilkan secara otomatis dari sistem informasi manajemen presensi magang.</p>
            <p>2. Validitas kehadiran didasarkan pada verifikasi selfie dan koordinat GPS radius kantor.</p>
        </div>

        @include('admin.cetak.partials.ttd-kepala')

    </div>
</div>
@endsection