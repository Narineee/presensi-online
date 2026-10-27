@extends('layouts.print')

@section('title', 'Laporan Aktivitas Harian Magang')

@section('content')
<style>
    .print-sheet { font-family: 'Times New Roman', Times, serif; color:#000; background:#fff; }
    .judul-laporan { text-align:center; margin-bottom:14px; }
    .judul-laporan h3 { font-size:12pt; font-weight:bold; text-decoration:underline; text-transform:uppercase; margin:0; }
    .judul-laporan p { font-size:10pt; margin:3px 0 0; }

    .info-table { width:100%; font-size:10pt; margin-bottom:12px; border-collapse:collapse; }
    .info-table td { padding:3.5px 0; vertical-align:middle; border:0; line-height:1.45; }
    .info-table td.k { width:185px; white-space:nowrap; }
    .info-table td.s { width:15px; text-align:center; white-space:nowrap; }

    .rekap-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:9.5pt; }
    .rekap-table th, .rekap-table td { border:1px solid #000; padding:4px 5px; vertical-align:top; word-wrap:break-word; text-align:left; }
    .rekap-table th { background: transparent !important; background-color: transparent !important; text-align:left; font-weight:bold; text-transform:uppercase; font-size:9pt; vertical-align:middle; }
    .rekap-table th.c, .rekap-table td.c { text-align:center; }
    .rekap-table .sub { font-size:8pt; color:#333; }
    .rekap-table tr { page-break-inside:avoid; }
    .rekap-table thead { display:table-header-group; }

    .ringkasan { font-size:10pt; margin:12px 0 6px; background-color: transparent !important; background: transparent !important; border: 1px solid #000 !important; padding: 6px 8px; }
    .ringkasan strong { font-weight:bold; }
</style>
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        {{-- Judul Laporan & Periode Filter --}}
        <div class="judul-laporan">
            <h3>LAPORAN AKTIVITAS HARIAN MAGANG</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        {{-- Identitas Peserta Magang (Nama, No Induk, Jurusan, Pendidikan Asal) --}}
        <table class="info-table" style="width: 100%; margin-bottom: 14px;">
            <colgroup>
                <col style="width: 185px;">
                <col style="width: 15px;">
                <col>
            </colgroup>
            <tr>
                <td class="k" style="width: 185px; white-space: nowrap;">Nama</td>
                <td class="s" style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ $user->magang?->nama_lengkap ?? $user->username }}</strong></td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">No. Induk</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $user->magang?->no_induk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Jurusan</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $user->magang?->jurusan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Pendidikan Asal</td>
                <td class="s" style="text-align: center;">:</td>
                <td>{{ $user->magang?->instansi_pendidikan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k" style="white-space: nowrap;">Periode Magang</td>
                <td class="s" style="text-align: center;">:</td>
                <td>
                    @if($user->magang?->tanggal_mulai && $user->magang?->tanggal_selesai)
                        {{ \Carbon\Carbon::parse($user->magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') }} sampai {{ \Carbon\Carbon::parse($user->magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        {{-- Tabel Rekapitulasi Aktivitas Harian (No, Hari / Tanggal, Rencana Kerja, Hasil, Keterangan) --}}
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="c">No</th>
                    <th style="width: 23%;">Hari / Tanggal</th>
                    <th style="width: 38%;">Rencana Kerja</th>
                    <th style="width: 14%;">Hasil</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aktivitas as $index => $item)
                    @php
                        $details = $item->details;
                        $hasMultiple = $details && $details->count() > 1;
                    @endphp
                    <tr>
                        <td class="c">{{ $index + 1 }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </td>
                        <td>
                            @if($details && $details->isNotEmpty())
                                @if($hasMultiple)
                                    @foreach($details as $idx => $d)
                                        <div style="margin-bottom: 2px;">{{ $idx + 1 }}. {{ $d->judul_rencana }}</div>
                                    @endforeach
                                @else
                                    <div>{{ $details->first()->judul_rencana }}</div>
                                @endif
                            @else
                                <div>{{ $item->judul ?? $item->pekerjaan?->judul ?? 'Aktivitas Harian' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($details && $details->isNotEmpty())
                                @if($hasMultiple)
                                    @foreach($details as $idx => $d)
                                        @php
                                            $progLower = strtolower(trim($d->progres));
                                            $labelHasil = in_array($progLower, ['selesai', 'belum']) ? ucfirst($progLower) : $d->progres;
                                        @endphp
                                        <div style="margin-bottom: 2px; font-weight: bold; color: #000000;">
                                            {{ $idx + 1 }}. {{ $labelHasil }}
                                        </div>
                                    @endforeach
                                @else
                                    @php
                                        $d = $details->first();
                                        $progLower = strtolower(trim($d->progres));
                                        $labelHasil = in_array($progLower, ['selesai', 'belum']) ? ucfirst($progLower) : $d->progres;
                                    @endphp
                                    <div style="font-weight: bold; color: #000000;">
                                        {{ $labelHasil }}
                                    </div>
                                @endif
                            @else
                                <div>{{ ucfirst(trim($item->isi)) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($details && $details->isNotEmpty())
                                @if($hasMultiple)
                                    @foreach($details as $idx => $d)
                                        @php
                                            $progLower = strtolower(trim($d->progres));
                                            $alasanText = ($progLower === 'belum' && $d->alasan) ? 'Alasan: ' . $d->alasan : '-';
                                        @endphp
                                        <div style="margin-bottom: 2px;">{{ $idx + 1 }}. {{ $alasanText }}</div>
                                    @endforeach
                                @else
                                    @php
                                        $d = $details->first();
                                        $progLower = strtolower(trim($d->progres));
                                        $alasanText = ($progLower === 'belum' && $d->alasan) ? 'Alasan: ' . $d->alasan : '-';
                                    @endphp
                                    <div>{{ $alasanText }}</div>
                                @endif
                            @else
                                <div>-</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="c" style="padding: 16px;">Belum ada catatan aktivitas pada periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Ringkasan --}}
        <div class="ringkasan">
            <strong>Ringkasan Aktivitas:</strong>
            Total Catatan {{ $stats['total'] ?? $aktivitas->count() }};
            Disetujui {{ $stats['approve'] ?? $aktivitas->where('status', 'approve')->count() }};
            Menunggu Review {{ $stats['pending'] ?? $aktivitas->where('status', 'pending')->count() }};
            Perlu Revisi {{ $stats['revisi'] ?? $aktivitas->where('status', 'revisi')->count() }}.
        </div>

    </div>
</div>
@endsection