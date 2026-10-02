@extends('layouts.print')

@section('title', 'Cetak Rekap Presensi Pribadi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>Lembar Rekapitulasi Presensi Kehadiran</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            <tr><td class="k" style="width:150px">Nama Lengkap</td><td class="s">:</td><td><strong>{{ $user->magang?->nama_lengkap ?? $user->username }}</strong></td></tr>
            <tr><td class="k">Nomor Induk / NIM</td><td class="s">:</td><td>{{ $user->magang?->no_induk ?? '-' }}</td></tr>
            <tr><td class="k">Instansi Pendidikan</td><td class="s">:</td><td>{{ $user->magang?->instansi_pendidikan ?? '-' }}</td></tr>
            <tr><td class="k">Divisi Penempatan</td><td class="s">:</td><td>{{ $user->magang?->divisi?->nama_divisi ?? '-' }}</td></tr>
            <tr><td class="k">Pembimbing Lapangan</td><td class="s">:</td><td>{{ $user->magang?->pembimbing?->nama_lengkap ?? '-' }}</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:20%">Hari / Tanggal</th>
                    <th style="width:11%">Mode Kerja</th>
                    <th style="width:13%">Jam Masuk</th>
                    <th style="width:13%">Jam Pulang</th>
                    <th style="width:11%">Status</th>
                    <th style="width:27%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presensi as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMMM Y') }}</td>
                        <td class="c" style="text-transform:uppercase;">{{ ($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar') ? 'TUGAS LUAR' : $item->mode_kerja }}</td>
                        <td class="c">{{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c">{{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c" style="text-transform:capitalize;">{{ $item->is_tugas_luar ? 'Hadir (TL)' : $item->status }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="c" style="padding:16px;">Belum ada rekaman presensi pada periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Hadir {{ $stats['total_hadir'] }} hari;
            Onsite (Kantor) {{ $stats['total_onsite'] }} hari;
            WFH (Remote) {{ $stats['total_wfh'] }} hari;
            Tugas Luar {{ $stats['total_tugas_luar'] ?? 0 }} hari.
        </div>

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection