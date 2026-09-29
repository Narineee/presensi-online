@extends('layouts.print')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>Lembar Rekapitulasi Aktivitas Harian</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            <tr><td class="k" style="width:150px">Nama Lengkap</td><td class="s">:</td><td><strong>{{ $user->magang?->nama_lengkap ?? $user->username }}</strong></td></tr>
            <tr><td class="k">Nomor Induk / NIM</td><td class="s">:</td><td>{{ $user->magang?->no_induk ?? '-' }}</td></tr>
            <tr><td class="k">Instansi Pendidikan</td><td class="s">:</td><td>{{ $user->magang?->instansi_pendidikan ?? '-' }}</td></tr>
            <tr><td class="k">Jurusan / Program</td><td class="s">:</td><td>{{ $user->magang?->jurusan ?? '-' }}</td></tr>
            <tr><td class="k">Divisi Penempatan</td><td class="s">:</td><td>{{ $user->magang?->divisi?->nama_divisi ?? '-' }}</td></tr>
            <tr><td class="k">Pembimbing Lapangan</td><td class="s">:</td><td>{{ $user->magang?->pembimbing?->nama_lengkap ?? '-' }}</td></tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:18%">Hari / Tanggal</th>
                    <th style="width:40%">Uraian Tugas &amp; Kegiatan Harian</th>
                    <th style="width:9%">Progres</th>
                    <th style="width:12%">Status</th>
                    <th style="width:16%">Catatan Pembimbing</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aktivitas as $index => $item)
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMM Y') }}</td>
                        <td>{{ $item->isi }}</td>
                        <td class="c">{{ $item->progress }}%</td>
                        <td class="c">
                            @if($item->status === 'approve') Disetujui
                            @elseif($item->status === 'pending') Menunggu
                            @else Revisi
                            @endif
                        </td>
                        <td>{{ $item->catatan_validasi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="c" style="padding:16px;">Belum ada catatan aktivitas pada periode yang dipilih.</td>
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

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection