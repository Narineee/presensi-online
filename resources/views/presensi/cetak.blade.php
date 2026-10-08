@extends('layouts.print')

@section('title', 'Cetak Lembar Rekapitulasi Presensi Kehadiran')

@section('content')
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        {{-- Tanggal Surat Dibuat (di bagian bawah kop surat sebelah kanan) --}}
        <div style="text-align: right; font-size: 10pt; margin-top: 6px; margin-bottom: 12px;">
            {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}
        </div>

        {{-- Judul Laporan & Periode Filter --}}
        <div class="judul-laporan" style="text-align: center; margin-bottom: 14px;">
            <h3 style="font-size: 12pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin: 0;">LEMBAR REKAPITULASI PRESENSI KEHADIRAN</h3>
            <p style="font-size: 10pt; font-weight: bold; margin: 4px 0 0; text-transform: uppercase;">PERIODE : {{ $periodeText }}</p>
        </div>

        {{-- Identitas Peserta Magang (Nama, No Induk, Jurusan, Pendidikan Asal) --}}
        <table class="info-table" style="font-size: 10pt; margin-bottom: 14px; border-collapse: collapse; width: 100%;">
            <tr>
                <td class="k" style="width: 140px; padding: 2px 0;">Nama</td>
                <td class="s" style="width: 14px; padding: 2px 0;">:</td>
                <td style="padding: 2px 0;"><strong>{{ $user->magang?->nama_lengkap ?? $user->username }}</strong></td>
            </tr>
            <tr>
                <td class="k" style="padding: 2px 0;">No. Induk</td>
                <td class="s" style="padding: 2px 0;">:</td>
                <td style="padding: 2px 0;">{{ $user->magang?->no_induk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="padding: 2px 0;">Jurusan</td>
                <td class="s" style="padding: 2px 0;">:</td>
                <td style="padding: 2px 0;">{{ $user->magang?->jurusan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="padding: 2px 0;">Pendidikan Asal</td>
                <td class="s" style="padding: 2px 0;">:</td>
                <td style="padding: 2px 0;">{{ $user->magang?->instansi_pendidikan ?? '-' }}</td>
            </tr>
        </table>

        {{-- Tabel Rekapan Presensi Kehadiran --}}
        <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 9pt;">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 18%;">Hari / Tanggal</th>
                    <th style="width: 14%;">Unit Kerja</th>
                    <th style="width: 12%;">Mode Kerja</th>
                    <th style="width: 13%;">Jam Masuk</th>
                    <th style="width: 13%;">Jam Pulang</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 16%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presensi as $index => $item)
                    @php
                        $unitKerja = $user->magang?->getDivisiAt($item->tanggal)?->nama_divisi 
                            ?? $user->magang?->divisi?->nama_divisi 
                            ?? ($divisi->nama_divisi ?? '-');
                        $modeText = ($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar') ? 'TUGAS LUAR' : strtoupper($item->mode_kerja ?? 'ONSITE');
                        $statusBadge = $item->is_tugas_luar ? 'Hadir (TL)' : ucfirst($item->status ?? 'Hadir');
                    @endphp
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                        <td>{{ $unitKerja }}</td>
                        <td class="c">{{ $modeText }}</td>
                        <td class="c">{{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c">{{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}</td>
                        <td class="c">{{ $statusBadge }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="c" style="padding: 16px;">Belum ada rekaman presensi pada periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Ringkasan Kehadiran --}}
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