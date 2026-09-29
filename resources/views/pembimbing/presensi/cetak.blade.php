@extends('layouts.print')

@section('title', 'Cetak Rekap Presensi Binaan')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>Rekapitulasi Presensi Kehadiran Peserta Magang Binaan</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="k">Pembimbing Lapangan</td>
                <td class="s">:</td>
                <td><strong>{{ $pembimbing->nama_lengkap ?? '-' }}</strong> (NIP. {{ $pembimbing->nip ?? '-' }})</td>
            </tr>
            <tr>
                <td class="k">Peserta Magang</td>
                <td class="s">:</td>
                <td>
                    @if($selectedMagang)
                        <strong>{{ $selectedMagang->nama_lengkap }}</strong> (NIM: {{ $selectedMagang->no_induk }}) &mdash; {{ $selectedMagang->divisi->nama_divisi ?? 'Divisi Umum' }}
                    @else
                        Seluruh Peserta Magang Binaan
                    @endif
                </td>
            </tr>
            <tr>
                <td class="k">Waktu Cetak</td>
                <td class="s">:</td>
                <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
            </tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:13%">Tanggal</th>
                    <th style="width:25%">Nama Peserta &amp; Divisi</th>
                    <th style="width:9%">Mode</th>
                    <th style="width:12%">Jam Masuk</th>
                    <th style="width:12%">Jam Pulang</th>
                    <th style="width:9%">Status</th>
                    <th style="width:15%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presensi as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}</td>
                        <td>
                            <strong>{{ $item->nama_lengkap }}</strong>
                            @if($item->pengguna && $item->pengguna->magang && $item->pengguna->magang->divisi)
                                <div class="sub">{{ $item->pengguna->magang->divisi->nama_divisi }}</div>
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
                        <td colspan="8" class="c" style="padding:16px;">Tidak ada rekaman presensi peserta binaan untuk parameter filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ringkasan">
            <strong>Ringkasan Presensi:</strong>
            Total Catatan: {{ $stats['total'] }};
            Hadir: {{ $stats['hadir'] }};
            Onsite (Kantor): {{ $stats['onsite'] }};
            WFH (Remote): {{ $stats['wfh'] }};
            Izin/Sakit: {{ $stats['izin_sakit'] }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Dokumen ini merupakan rekapitulasi sah kehadiran peserta binaan pembimbing lapangan.</p>
            <p>2. Data presensi terekam berbasis titik koordinat GPS dan bukti swafoto kehadiran harian.</p>
        </div>

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection
