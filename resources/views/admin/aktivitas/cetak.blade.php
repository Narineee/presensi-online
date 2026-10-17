@extends('layouts.print')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="print-sheet bg-white p-8 sm:p-12 border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

        <div class="judul-laporan">
            <h3>REKAPITULASI LOG AKTIVITAS HARIAN</h3>
            <p>Periode: {{ $periodeText }}</p>
        </div>

        <table class="info-table">
            @if($selectedMagang)
                <tr>
                    <td class="k">Peserta Magang</td>
                    <td class="s">:</td>
                    <td><strong>{{ $selectedMagang->nama_lengkap }}</strong> (NIS/NIM: {{ $selectedMagang->no_induk ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="k">Divisi &amp; Instansi</td>
                    <td class="s">:</td>
                    <td>{{ $selectedMagang->divisi->nama_divisi ?? '-' }} &bull; {{ $selectedMagang->instansi_pendidikan }}</td>
                </tr>
                <tr>
                    <td class="k">Pembimbing Lapangan</td>
                    <td class="s">:</td>
                    @php
                        $pembimbingAktif = $selectedMagang->getPembimbingAt(now()) ?? $selectedMagang->pembimbing;
                    @endphp
                    <td>{{ $pembimbingAktif ? $pembimbingAktif->nama_lengkap . ' (NIP: ' . ($pembimbingAktif->nip ?? '-') . ')' : '-' }}</td>
                </tr>
            @else
                <tr>
                    <td class="k">Cakupan Peserta</td>
                    <td class="s">:</td>
                    <td>Seluruh Peserta Magang</td>
                </tr>
            @endif
            <tr>
                <td class="k">Status Filter</td>
                <td class="s">:</td>
                <td>{{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</td>
            </tr>
            <tr>
                <td class="k">Waktu Cetak</td>
                <td class="s">:</td>
                <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</td>
            </tr>
        </table>

        @if($selectedMagang)
            {{-- Tampilan Rekap Per Peserta (Dikelompokkan per Rencana Kerja sesuai PRD) --}}
            @forelse ($groupedByRencana as $gIndex => $group)
                <div style="margin-bottom: 22px; page-break-inside: avoid;">
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; padding: 6px 10px; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
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
                                <th style="width: 18%;">Hari / Tanggal</th>
                                <th style="width: 50%;">Uraian Progres Pekerjaan</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 16%;">Catatan Pembimbing</th>
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
                            <td class="c" style="padding: 20px;">Tidak ada catatan aktivitas peserta magang untuk parameter filter yang dipilih.</td>
                        </tr>
                    </tbody>
                </table>
            @endforelse
        @else
            {{-- Tampilan Rekap Kolektif Seluruh Peserta Magang (dengan Pembimbing Lapangan pada tanggal terkait) --}}
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th style="width:4%">No</th>
                        <th style="width:11%">Tanggal</th>
                        <th style="width:18%">Nama Peserta &amp; Divisi</th>
                        <th style="width:18%">Pembimbing Lapangan</th>
                        <th style="width:14%">Pekerjaan / Rencana</th>
                        <th style="width:23%">Uraian Tugas / Progres Harian</th>
                        <th style="width:6%">Progres</th>
                        <th style="width:6%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aktivitas as $index => $item)
                        <tr>
                            <td class="c">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}</td>
                            <td>
                                <strong>{{ $item->nama_lengkap }}</strong>
                                @php
                                    $divisiRow = $item->pengguna?->magang?->getDivisiAt($item->tanggal) ?? $item->pengguna?->magang?->divisi;
                                    $pembimbingRow = $item->pengguna?->magang?->getPembimbingAt($item->tanggal);
                                @endphp
                                @if($divisiRow)
                                    <div class="sub">{{ $divisiRow->nama_divisi }}</div>
                                @endif
                            </td>
                            <td>
                                @if($pembimbingRow)
                                    <strong>{{ $pembimbingRow->nama_lengkap }}</strong>
                                    <div class="sub">NIP: {{ $pembimbingRow->nip ?? '-' }}</div>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->details && $item->details->isNotEmpty())
                                    <strong>{{ $item->details->pluck('judul_rencana')->join(', ') }}</strong>
                                    <div class="sub">(Rencana Kerja)</div>
                                @else
                                    <strong>{{ $item->pekerjaan?->judul ?? ($item->judul ?? 'Pekerjaan Umum') }}</strong>
                                    <div class="sub">({{ ($item->pekerjaan && $item->pekerjaan->isRutin()) ? 'Rutin' : 'Proyek' }})</div>
                                @endif
                            </td>
                            <td>
                                @if ($item->details && $item->details->isNotEmpty())
                                    @foreach($item->details as $d)
                                        <div style="margin-bottom: 2px;">• <strong>{{ $d->judul_rencana }}:</strong> {{ $d->progres }}</div>
                                    @endforeach
                                @else
                                    {{ $item->isi }}
                                @endif
                                @if($item->catatan_validasi)
                                    <div class="sub" style="margin-top: 4px; font-style: italic;">Catatan: {{ $item->catatan_validasi }}</div>
                                @endif
                            </td>
                            <td class="c">
                                @if($item->pekerjaan && $item->pekerjaan->isRutin())
                                    -
                                @else
                                    {{ $item->progress }}%
                                @endif
                            </td>
                            <td class="c">
                                @if($item->status === 'approve') Disetujui
                                @elseif($item->status === 'pending') Menunggu
                                @else Revisi
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="c" style="padding:16px;">Tidak ada catatan aktivitas yang sesuai dengan parameter filter yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="ringkasan">
            <strong>Ringkasan Aktivitas:</strong>
            Total Catatan {{ $stats['total'] }};
            Disetujui {{ $stats['approve'] }};
            Menunggu Review {{ $stats['pending'] }};
            Perlu Revisi {{ $stats['revisi'] }}.
        </div>

        <div class="catatan-dok">
            <p>Catatan:</p>
            <p>1. Log aktivitas ini merupakan bukti sah pelaksanaan tugas peserta magang.</p>
            <p>2. Pembimbing Lapangan yang tertera adalah pembimbing yang berwenang membina peserta pada tanggal penugasan terkait.</p>
        </div>

        @include('admin.cetak.partials.ttd-kepala')

    </div>
</div>
@endsection