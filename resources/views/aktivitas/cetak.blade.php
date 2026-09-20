@extends('layouts.user')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('styles')
<style>
    @media print {
        header, footer, .no-print, nav, .btn-print-group {
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
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar (Hanya tampil di layar monitor) -->
    <div class="no-print bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('aktivitas.index') }}" class="hover:text-blue-600 transition flex items-center gap-1 font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Log Aktivitas
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Cetak Lembar Aktivitas</span>
                </div>
                <h1 class="text-lg font-bold text-slate-900">Rekapitulasi Log Aktivitas Harian Magang</h1>
                <p class="text-xs text-slate-500">Pilih periode laporan aktivitas atau langsung cetak dokumen ini.</p>
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

        <!-- Filter Cepat Bulan & Status -->
        <form action="{{ route('aktivitas.cetak') }}" method="GET" class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label for="bulan" class="font-semibold text-slate-700">Pilih Bulan:</label>
                <input type="month" name="bulan" id="bulan" value="{{ request('bulan', $bulan) }}" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
            </div>
            <div class="flex items-center gap-2">
                <label for="status" class="font-semibold text-slate-700">Status:</label>
                <select name="status" id="status" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                    <option value="">Semua Status</option>
                    <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-semibold transition cursor-pointer">
                Tampilkan
            </button>
        </form>
    </div>

    <!-- Printable Official Sheet -->
    <div class="print-sheet bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-sm text-slate-800 space-y-6">

        <!-- KOP DOKUMEN RESMI -->
        <div class="border-b-2 border-slate-900 pb-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-xs">
                    PD
                </div>
                <div class="text-left">
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">SISTEM PRESENSI & MANAJEMEN MAGANG</h2>
                    <p class="text-[11px] text-slate-500 font-medium">
                        Divisi {{ $user->magang?->divisi?->nama_divisi ?? 'Operasional & Pengembangan' }}
                    </p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">Lembar Rekapitulasi Catatan Aktivitas Harian Peserta Magang &bull; Sah & Terverifikasi</p>
        </div>

        <!-- JUDUL & PERIODE -->
        <div class="text-center space-y-1 border-b border-slate-200 pb-3">
            <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">LEMBAR REKAPITULASI AKTIVITAS HARIAN</h3>
            <p class="text-xs text-slate-600">Periode: <span class="font-bold text-slate-800">{{ $periodeText }}</span></p>
        </div>

        <!-- IDENTITAS PENGGUNA (MAGANG & CS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs p-4 rounded-2xl bg-slate-50/70 border border-slate-200">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">
                        {{ $user->magang?->nama_lengkap ?? ($user->cs?->nama_lengkap ?? $user->username) }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">{{ $user->role === 'magang' ? 'Nomor Induk / NIM:' : 'NIK Karyawan:' }}</span>
                    <span class="font-semibold text-slate-800">
                        {{ $user->magang?->no_induk ?? ($user->cs?->nik ?? '-') }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">{{ $user->role === 'magang' ? 'Instansi Pendidikan:' : 'Jabatan:' }}</span>
                    <span class="font-semibold text-slate-800">
                        {{ $user->magang?->instansi_pendidikan ?? ($user->cs?->jabatan ?? 'Customer Service') }}
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500">{{ $user->role === 'magang' ? 'Divisi Penempatan:' : 'Unit / Peran:' }}</span>
                    <span class="font-semibold text-slate-800">{{ $user->magang?->divisi?->nama_divisi ?? 'Customer Service (CS)' }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">{{ $user->role === 'magang' ? 'Jurusan / Program:' : 'Peran Sistem:' }}</span>
                    <span class="font-semibold text-slate-800 uppercase">{{ $user->role === 'magang' ? ($user->magang?->jurusan ?? '-') : 'Customer Service' }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">Pembimbing / Validator:</span>
                    <span class="font-semibold text-slate-800">{{ $user->magang?->pembimbing?->nama_lengkap ?? ($user->cs?->pembimbing?->nama_lengkap ?? '-') }}</span>
                </div>
            </div>
        </div>

        <!-- RINGKASAN METRIK AKTIVITAS -->
        <div class="grid grid-cols-4 gap-3 text-center">
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Catatan</div>
                <div class="text-lg font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Disetujui</div>
                <div class="text-lg font-black text-emerald-800 mt-0.5">{{ $stats['approve'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50">
                <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Review</div>
                <div class="text-lg font-black text-amber-800 mt-0.5">{{ $stats['pending'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-rose-200 bg-rose-50/50">
                <div class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Perlu Revisi</div>
                <div class="text-lg font-black text-rose-800 mt-0.5">{{ $stats['revisi'] }}</div>
            </div>
        </div>

        <!-- TABEL RINCIAN AKTIVITAS -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="p-2.5 border-r border-slate-300 w-10 text-center">No</th>
                        <th class="p-2.5 border-r border-slate-300 w-28">Hari / Tanggal</th>
                        <th class="p-2.5 border-r border-slate-300">Uraian Tugas & Kegiatan Harian</th>
                        <th class="p-2.5 border-r border-slate-300 text-center w-16">Progres</th>
                        <th class="p-2.5 border-r border-slate-300 text-center w-24">Status</th>
                        <th class="p-2.5 w-44">Catatan Pembimbing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($aktivitas as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-2 border-r border-slate-200 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-2 border-r border-slate-200 whitespace-nowrap font-medium text-slate-900">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMM Y') }}
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">
                                Belum ada catatan aktivitas pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- KOLOM TANDA TANGAN DINAMIS (PEMBIMBING & PIMPINAN DIVISI) -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-xs text-center" style="page-break-inside: avoid;">
            <!-- Tanda Tangan Pembimbing Lapangan -->
            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">Mengetahui & Mengesahkan,</p>
                    <p class="font-bold text-slate-800 text-xs sm:text-sm">Pembimbing Lapangan</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">
                        {{ $pembimbing->nama_lengkap ?? ($user->magang?->pembimbing?->nama_lengkap ?? '( .................................................. )') }}
                    </p>
                    <p class="text-slate-500 text-[11px] mt-0.5">
                        NIP. {{ $pembimbing->nip ?? ($user->magang?->pembimbing?->nip ?? '-') }}
                    </p>
                    <p class="text-slate-400 text-[10px]">
                        {{ $pembimbing->jabatan ?? ($user->magang?->pembimbing?->jabatan ?? 'Pembimbing Lapangan') }}
                    </p>
                </div>
            </div>

            <!-- Tanda Tangan Pimpinan Divisi / Sub-Bagian -->
            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
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
