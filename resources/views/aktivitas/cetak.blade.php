@extends('layouts.print')

@section('title', 'Cetak Lembar Rekapitulasi Aktivitas Harian')

@section('content')
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        {{-- Tanggal Surat Dibuat (di bagian bawah kop surat) --}}
        <div style="text-align: right; font-size: 10pt; margin-top: 6px; margin-bottom: 12px;">
            {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}
        </div>

        {{-- Judul Laporan & Periode Filter --}}
        <div class="judul-laporan" style="text-align: center; margin-bottom: 14px;">
            <h3 style="font-size: 12pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin: 0;">LEMBAR REKAPITULASI AKTIVITAS HARIAN</h3>
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

        {{-- Tabel Rekapan Aktivitas (10 Kolom: no, unit kerja, pembimbing, pekerjaan, uraian tugas, kegiatan, hari/tgl, progress, status, catatan) --}}
        <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 11%;">Unit Kerja</th>
                    <th style="width: 11%;">Pembimbing</th>
                    <th style="width: 12%;">Pekerjaan</th>
                    <th style="width: 14%;">Uraian Tugas</th>
                    <th style="width: 16%;">Kegiatan</th>
                    <th style="width: 11%;">Hari / Tgl</th>
                    <th style="width: 6%;">Progress</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 8%;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aktivitas as $index => $item)
                    @php
                        $unitKerja = $user->magang?->getDivisiAt($item->tanggal)?->nama_divisi 
                            ?? $user->magang?->divisi?->nama_divisi 
                            ?? ($divisi->nama_divisi ?? '-');
                        $pembimbingNama = $item->validator?->pembimbing?->nama_lengkap 
                            ?? $user->magang?->pembimbing?->nama_lengkap 
                            ?? ($pembimbing->nama_lengkap ?? '-');
                        $pekerjaanJudul = $item->pekerjaan?->nama_pekerjaan 
                            ?? ($item->pekerjaan?->judul ?? ($item->judul ?? 'Pekerjaan Umum'));
                        $uraianTugas = $item->pekerjaan?->deskripsi 
                            ?? ($item->judul ?? '-');
                        $kegiatan = $item->isi;
                        $statusText = match($item->status) {
                            'approve', 'disetujui' => 'Disetujui',
                            'pending', 'menunggu' => 'Menunggu',
                            default => 'Revisi',
                        };
                    @endphp
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>{{ $unitKerja }}</td>
                        <td>{{ $pembimbingNama }}</td>
                        <td>{{ $pekerjaanJudul }}</td>
                        <td>{{ $uraianTugas }}</td>
                        <td>{{ $kegiatan }}</td>
                        <td class="c">{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMM Y') }}</td>
                        <td class="c">{{ $item->progress }}%</td>
                        <td class="c">{{ $statusText }}</td>
                        <td>{{ $item->catatan_validasi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="c" style="padding: 16px;">Belum ada catatan aktivitas pada periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Ringkasan --}}
        <div class="ringkasan">
            <strong>Ringkasan:</strong>
            Total Catatan {{ $stats['total'] ?? $aktivitas->count() }};
            Disetujui {{ $stats['approve'] ?? $aktivitas->where('status', 'approve')->count() }};
            Menunggu Review {{ $stats['pending'] ?? $aktivitas->where('status', 'pending')->count() }};
            Perlu Revisi {{ $stats['revisi'] ?? $aktivitas->where('status', 'revisi')->count() }}.
        </div>

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection