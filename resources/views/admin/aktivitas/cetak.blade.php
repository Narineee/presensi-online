@extends('layouts.admin')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('styles')
<style>
    @media print {
        aside, header, footer, .no-print, nav, .btn-print-group {
            display: none !important;
        }

        body {
            background-color: white !important;
            color: #0f172a !important;
            font-size: 10.5pt;
            margin: 0;
            padding: 0;
        }

        main {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            width: 100% !important;
        }

        table {
            page-break-inside: auto;
            border-collapse: collapse !important;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Top Action Bar (No-Print) -->
    <div class="no-print bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('admin.aktivitas.index') }}" class="hover:text-blue-600 transition flex items-center gap-1 font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Monitoring Aktivitas
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Cetak Laporan Aktivitas</span>
                </div>
                <h1 class="text-lg font-bold text-slate-900">Cetak Rekapitulasi Log Aktivitas Harian</h1>
                <p class="text-xs text-slate-500">Sesuaikan rentang tanggal atau cetak seluruh riwayat pekerjaan peserta.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                    </svg>
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Cepat Cetak -->
        <form action="{{ route('admin.aktivitas.cetak') }}" method="GET" class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end text-xs">
            <div>
                <label for="tanggal_mulai" class="block font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
            </div>
            <div>
                <label for="tanggal_akhir" class="block font-semibold text-slate-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
            </div>
            <div>
                <label for="status" class="block font-semibold text-slate-700 mb-1">Status Validasi</label>
                <select name="status" id="status" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                    <option value="">Semua Status</option>
                    <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui (Approve)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-semibold transition cursor-pointer">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Printable Official Report Sheet -->
    <div class="print-sheet bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-sm text-slate-800 space-y-6">

        <!-- KOP DOKUMEN RESMI -->
        <div class="border-b-2 border-slate-900 pb-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-xs">
                    PD
                </div>
                <div class="text-left">
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">SISTEM PRESENSI & MANAJEMEN DIGITAL</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Laporan Rekapitulasi Catatan Aktivitas & Progres Pekerjaan Peserta</p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">Pusat Administrasi &bull; Laporan Resmi Dicetak Melalui Sistem Presensi Digital</p>
        </div>

        <!-- JUDUL & PARAMETER PERIODE -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">REKAPITULASI LOG AKTIVITAS HARIAN</h3>
                <p class="text-xs text-slate-600 mt-0.5">Periode Laporan: <span class="font-bold text-slate-800">{{ $periodeText }}</span></p>
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <div>Waktu Cetak: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span></div>
                <div>Status: <span class="font-semibold uppercase text-slate-700">{{ request('status') ?: 'Semua Status' }}</span></div>
            </div>
        </div>

        <!-- RINGKASAN METRIK LAPORAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 text-center">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Catatan</div>
                <div class="text-lg font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-center">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Disetujui</div>
                <div class="text-lg font-black text-emerald-700 mt-0.5">{{ $stats['approve'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 text-center">
                <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Review</div>
                <div class="text-lg font-black text-amber-700 mt-0.5">{{ $stats['pending'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-rose-200 bg-rose-50/50 text-center">
                <div class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Perlu Revisi</div>
                <div class="text-lg font-black text-rose-700 mt-0.5">{{ $stats['revisi'] }}</div>
            </div>
        </div>

        <!-- TABEL DATA REKAPITULASI AKTIVITAS -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="p-2.5 border-r border-slate-300 w-10 text-center">No</th>
                        <th class="p-2.5 border-r border-slate-300">Tanggal</th>
                        <th class="p-2.5 border-r border-slate-300">Nama Peserta</th>
                        <th class="p-2.5 border-r border-slate-300">Uraian Tugas / Pekerjaan Harian</th>
                        <th class="p-2.5 border-r border-slate-300 text-center w-16">Progres</th>
                        <th class="p-2.5 border-r border-slate-300 text-center w-24">Status</th>
                        <th class="p-2.5">Catatan Pembimbing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($aktivitas as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-2 border-r border-slate-200 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-2 border-r border-slate-200 whitespace-nowrap font-medium text-slate-900">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 font-bold text-slate-800">
                                {{ $item->nama_lengkap }}
                                @if($item->pengguna && $item->pengguna->magang && $item->pengguna->magang->divisi)
                                    <div class="text-[10px] font-normal text-slate-500">{{ $item->pengguna->magang->divisi->nama_divisi }}</div>
                                @endif
                            </td>
                            <td class="p-2 border-r border-slate-200 text-slate-800 leading-relaxed">
                                {{ $item->isi }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center font-bold text-slate-700">
                                {{ $item->progress }}%
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center">
                                @if($item->status === 'approve')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Disetujui</span>
                                @elseif($item->status === 'pending')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">Pending</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800">Revisi</span>
                                @endif
                            </td>
                            <td class="p-2 text-slate-600 text-[11px]">
                                {{ $item->catatan_validasi ?? '-' }}
                                @if($item->validator)
                                    <div class="text-[9px] text-slate-400 mt-0.5">Oleh: {{ $item->nama_validator }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">
                                Tidak ada catatan aktivitas yang sesuai dengan parameter filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- KETERANGAN CATATAN -->
        <div class="pt-2 text-[10px] text-slate-400 space-y-0.5">
            <p>1. Log aktivitas ini merupakan bukti sah pelaksanaan tugas peserta.</p>
            <p>2. Setiap aktivitas yang disetujui telah diverifikasi oleh Pembimbing Lapangan.</p>
        </div>

        <!-- KOLOM TANDA TANGAN DINAMIS (PEMBIMBING LAPANGAN & PIMPINAN BAGIAN/SUB-BAGIAN DIVISI) -->
        <div class="pt-6 border-t border-slate-200 grid grid-cols-2 gap-8 text-xs text-center" style="page-break-inside: avoid;">
            <!-- Tanda Tangan Pembimbing Lapangan -->
            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">Mengetahui & Mengesahkan,</p>
                    <p class="font-bold text-slate-800 text-xs sm:text-sm">Pembimbing Lapangan</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">
                        {{ $pembimbing->nama_lengkap ?? '( .................................................. )' }}
                    </p>
                    <p class="text-slate-500 text-[11px] mt-0.5">
                        NIP. {{ $pembimbing->nip ?? '-' }}
                    </p>
                    <p class="text-slate-400 text-[10px]">
                        {{ $pembimbing->jabatan ?? 'Pembimbing Lapangan' }}
                    </p>
                </div>
            </div>

            <!-- Tanda Tangan Pimpinan Divisi / Sub-Bagian -->
            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">Banjarbaru, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                    <p class="font-bold text-slate-800 text-xs sm:text-sm">
                        {{ $divisi->jabatan_pimpinan ?? 'Kepala Sub-Bagian / Divisi' }}
                    </p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">
                        {{ $divisi->nama_pimpinan ?? '( .................................................. )' }}
                    </p>
                    <p class="text-slate-500 text-[11px] mt-0.5">
                        NIP. {{ $divisi->nip_pimpinan ?? '-' }}
                    </p>
                    <p class="text-slate-400 text-[10px]">
                        {{ $divisi->nama_divisi ?? 'Divisi Terkait' }}
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
