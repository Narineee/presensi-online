@extends('layouts.print')

@section('title', 'Laporan Aktivitas Harian Magang')

@section('content')
<div class="w-full flex justify-center">
    <div class="print-sheet bg-white border border-slate-300">

        @include('admin.cetak.partials.kop-surat')

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

        {{-- Tabel Rekapitulasi Aktivitas Harian (No, Unit Penempatan, Pembimbing, Rencana Kerja, Hari/Tanggal, Progres) --}}
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 18%;">Unit Penempatan</th>
                    <th style="width: 18%;">Pembimbing</th>
                    <th style="width: 22%;">Rencana Kerja</th>
                    <th style="width: 16%;">Hari / Tanggal</th>
                    <th style="width: 22%;">Progres</th>
                </tr>
            </thead>
            <tbody>
                @php $noUrut = 1; @endphp
                @forelse ($penempatanList as $penempatan)
                    @php $firstRowPenempatan = true; @endphp
                    @forelse ($penempatan['rencana_kerja_list'] as $rk)
                        @php
                            $firstRowRk = true;
                            $items = $rk['items'] ?? [];
                            $itemCount = count($items);
                        @endphp
                        @if ($itemCount > 0)
                            @foreach ($items as $item)
                                <tr>
                                    <td class="c">{{ $noUrut++ }}</td>

                                    @if ($firstRowPenempatan)
                                        <td rowspan="{{ $penempatan['total_rowspan'] }}">
                                            <strong>{{ $penempatan['divisi_nama'] }}</strong>
                                        </td>
                                        <td rowspan="{{ $penempatan['total_rowspan'] }}">
                                            <strong>{{ $penempatan['pembimbing_nama'] }}</strong>
                                            @if(!empty($penempatan['pembimbing_nip']) && $penempatan['pembimbing_nip'] !== '-')
                                                <div class="sub">NIP. {{ $penempatan['pembimbing_nip'] }}</div>
                                            @endif
                                        </td>
                                        @php $firstRowPenempatan = false; @endphp
                                    @endif

                                    @if ($firstRowRk)
                                        <td rowspan="{{ $itemCount }}">
                                            <strong>{{ $rk['judul'] }}</strong>
                                            @if(!empty($rk['deskripsi']))
                                                <div class="sub">{{ $rk['deskripsi'] }}</div>
                                            @endif
                                        </td>
                                        @php $firstRowRk = false; @endphp
                                    @endif

                                    <td class="c">
                                        {{ \Carbon\Carbon::parse($item['tanggal'])->locale('id')->isoFormat('dddd, D MMM Y') }}
                                    </td>
                                    <td>
                                        <div style="white-space: pre-line;">{{ $item['progres'] }}</div>
                                        @if(($item['status'] ?? '') === 'revisi')
                                            <div style="font-size: 7.5pt; color: #dc2626; margin-top: 2px;">
                                                <em>(Perlu Revisi{{ !empty($item['catatan']) ? ': '.$item['catatan'] : '' }})</em>
                                            </div>
                                        @elseif(($item['status'] ?? '') === 'pending')
                                            <div style="font-size: 7.5pt; color: #d97706; margin-top: 2px;">
                                                <em>(Menunggu Validasi)</em>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="c">{{ $noUrut++ }}</td>

                                @if ($firstRowPenempatan)
                                    <td rowspan="{{ $penempatan['total_rowspan'] }}">
                                        <strong>{{ $penempatan['divisi_nama'] }}</strong>
                                    </td>
                                    <td rowspan="{{ $penempatan['total_rowspan'] }}">
                                        <strong>{{ $penempatan['pembimbing_nama'] }}</strong>
                                        @if(!empty($penempatan['pembimbing_nip']) && $penempatan['pembimbing_nip'] !== '-')
                                            <div class="sub">NIP. {{ $penempatan['pembimbing_nip'] }}</div>
                                        @endif
                                    </td>
                                    @php $firstRowPenempatan = false; @endphp
                                @endif

                                <td>
                                    <strong>{{ $rk['judul'] }}</strong>
                                    @if(!empty($rk['deskripsi']))
                                        <div class="sub">{{ $rk['deskripsi'] }}</div>
                                    @endif
                                </td>
                                <td class="c" style="color: #64748b;">-</td>
                                <td style="color: #64748b;">Belum ada catatan progres</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td class="c">{{ $noUrut++ }}</td>
                            <td><strong>{{ $penempatan['divisi_nama'] }}</strong></td>
                            <td>
                                <strong>{{ $penempatan['pembimbing_nama'] }}</strong>
                                @if(!empty($penempatan['pembimbing_nip']) && $penempatan['pembimbing_nip'] !== '-')
                                    <div class="sub">NIP. {{ $penempatan['pembimbing_nip'] }}</div>
                                @endif
                            </td>
                            <td class="c" style="color: #64748b;">-</td>
                            <td class="c" style="color: #64748b;">-</td>
                            <td style="color: #64748b;">Belum ada rencana kerja / progres</td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="6" class="c" style="padding: 16px;">Belum ada catatan aktivitas pada periode yang dipilih.</td>
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

        @include('admin.cetak.partials.ttd-dua')

    </div>
</div>
@endsection