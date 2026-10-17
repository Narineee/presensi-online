@extends('layouts.print')

@section('title', 'Cetak Rekapitulasi Log Aktivitas Harian Binaan')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>REKAPITULASI LOG AKTIVITAS HARIAN PESERTA BINAAN</h3>
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
                        <strong>{{ $selectedMagang->nama_lengkap }}</strong> (NIS/NIM: {{ $selectedMagang->no_induk ?? '-' }}) &mdash; {{ $selectedMagang->divisi->nama_divisi ?? 'Divisi Terkait' }}
                    @else
                        Seluruh Peserta Magang Binaan ({{ count($groupedByPeserta) }} Peserta)
                    @endif
                </td>
            </tr>
            <tr>
                <td class="k">Waktu Cetak</td>
                <td class="s">:</td>
                <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
            </tr>
        </table>

        @if($selectedMagang)
            {{-- Tampilan Rekap Aktivitas Per Peserta (Tabel per Rencana Kerja sesuai PRD) --}}
            @forelse ($groupedByRencana as $gIndex => $group)
                <div style="margin-bottom: 22px; page-break-inside: avoid;">
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #9333ea; padding: 6px 10px; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 9.5pt; text-transform: uppercase; color: #1e293b;">
                                Tabel {{ $gIndex + 1 }}. Rencana Kerja: {{ $group['judul'] }}
                            </strong>
                            @if(!empty($group['deskripsi']))
                                <div style="font-size: 8pt; color: #64748b; margin-top: 1px;">{{ $group['deskripsi'] }}</div>
                            @endif
                        </div>
                        <span style="font-size: 8pt; color: #475569; font-weight: bold;">
                            {{ count($group['items']) }} Catatan Progres
                        </span>
                    </div>

                    <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                        <thead>
                            <tr>
                                <th style="width: 4%;">No</th>
                                <th style="width: 20%;">Hari / Tanggal</th>
                                <th style="width: 50%;">Uraian Progres Pekerjaan</th>
                                <th style="width: 11%;">Status</th>
                                <th style="width: 15%;">Catatan Pembimbing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['items'] as $index => $item)
                                <tr>
                                    <td class="c">{{ $index + 1 }}</td>
                                    <td class="c">{{ \Carbon\Carbon::parse($item['tanggal'])->locale('id')->isoFormat('dddd, D MMM Y') }}</td>
                                    <td>{{ $item['progres'] }}</td>
                                    <td class="c">{{ $item['status_label'] }}</td>
                                    <td>{{ $item['catatan'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 16px;">
                    <tbody>
                        <tr>
                            <td class="c" style="padding: 20px;">Tidak ada catatan aktivitas peserta binaan untuk parameter filter yang dipilih.</td>
                        </tr>
                    </tbody>
                </table>
            @endforelse
        @else
            {{-- Tampilan Rekap Seluruh Binaan (Dikelompokkan per Peserta, lalu per Rencana Kerja) --}}
            @forelse ($groupedByPeserta as $pIdx => $pItem)
                <div style="margin-bottom: 26px; page-break-inside: avoid;">
                    <div style="background: #ede9fe; border: 1px solid #c4b5fd; border-left: 5px solid #7c3aed; padding: 7px 12px; margin-bottom: 8px;">
                        <strong style="font-size: 10pt; color: #4c1d95;">{{ $pIdx + 1 }}. {{ $pItem['nama_lengkap'] }}</strong>
                        <span style="font-size: 8.5pt; color: #6d28d9; margin-left: 8px;">(NIS/NIM: {{ $pItem['no_induk'] }} | {{ $pItem['divisi'] }} | {{ $pItem['instansi'] }})</span>
                    </div>

                    @forelse ($pItem['grouped_rencana'] as $gIndex => $group)
                        <div style="margin-bottom: 14px; margin-left: 12px;">
                            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 3px solid #9333ea; padding: 5px 8px; margin-bottom: 3px; display: flex; justify-content: space-between; align-items: center;">
                                <strong style="font-size: 8.5pt; text-transform: uppercase; color: #1e293b;">
                                    Tabel {{ $gIndex + 1 }}. Rencana Kerja: {{ $group['judul'] }}
                                </strong>
                                <span style="font-size: 7.5pt; color: #475569;">{{ count($group['items']) }} progres</span>
                            </div>

                            <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">No</th>
                                        <th style="width: 20%;">Hari / Tanggal</th>
                                        <th style="width: 50%;">Uraian Progres Pekerjaan</th>
                                        <th style="width: 11%;">Status</th>
                                        <th style="width: 15%;">Catatan Pembimbing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['items'] as $index => $item)
                                        <tr>
                                            <td class="c">{{ $index + 1 }}</td>
                                            <td class="c">{{ \Carbon\Carbon::parse($item['tanggal'])->locale('id')->isoFormat('dddd, D MMM Y') }}</td>
                                            <td>{{ $item['progres'] }}</td>
                                            <td class="c">{{ $item['status_label'] }}</td>
                                            <td>{{ $item['catatan'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div style="font-size: 8.5pt; color: #64748b; padding: 8px 14px; font-style: italic;">
                            Belum ada catatan aktivitas pada periode ini.
                        </div>
                    @endforelse
                </div>
            @empty
                <table class="rekap-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 16px;">
                    <tbody>
                        <tr>
                            <td class="c" style="padding: 20px;">Tidak ada catatan aktivitas peserta binaan untuk parameter filter yang dipilih.</td>
                        </tr>
                    </tbody>
                </table>
            @endforelse
        @endif

        {{-- Ringkasan --}}
        <div class="ringkasan">
            <strong>Ringkasan Aktivitas:</strong>
            Total Catatan {{ $stats['total'] }};
            Disetujui {{ $stats['approve'] }};
            Menunggu Review {{ $stats['pending'] }};
            Perlu Revisi {{ $stats['revisi'] }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Log aktivitas ini merupakan bukti sah pengerjaan tugas peserta magang yang dibina.</p>
            <p>2. Hanya mencakup aktivitas peserta selama berada dalam periode penugasan di bawah bimbingan pembimbing terkait.</p>
        </div>

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection
