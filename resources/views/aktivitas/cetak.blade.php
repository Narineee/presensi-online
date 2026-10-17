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

        {{-- Tabel Rekapan Aktivitas Dikelompokkan per Rencana Kerja (sesuai PRD alur-rekap.md) --}}
        @forelse ($groupedByRencana as $gIndex => $group)
            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #6366f1; padding: 6px 10px; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
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
                        <td class="c" style="padding: 20px;">Belum ada catatan aktivitas pada periode yang dipilih.</td>
                    </tr>
                </tbody>
            </table>
        @endforelse

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