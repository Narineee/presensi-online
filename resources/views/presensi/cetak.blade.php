@extends('layouts.user')

@section('title', 'Cetak Rekap Presensi Pribadi')

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
                    <a href="{{ route('presensi.index') }}" class="hover:text-blue-600 transition flex items-center gap-1 font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Halaman Presensi
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Cetak Rekap Presensi</span>
                </div>
                <h1 class="text-lg font-bold text-slate-900">Rekapitulasi Kehadiran Pribadi</h1>
                <p class="text-xs text-slate-500">Pilih periode bulan yang ingin dicetak, lalu klik tombol cetak.</p>
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

        <!-- Filter Cepat Bulan -->
        <form action="{{ route('presensi.cetak') }}" method="GET" class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label for="bulan" class="font-semibold text-slate-700">Pilih Bulan:</label>
                <input type="month" name="bulan" id="bulan" value="{{ request('bulan', $bulan) }}" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
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
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">SISTEM PRESENSI & MANAJEMEN DIGITAL</h2>
                    <p class="text-[11px] text-slate-500 font-medium">
                        {{ $user->role === 'magang' && $user->magang && $user->magang->divisi ? 'Divisi ' . $user->magang->divisi->nama_divisi : 'Divisi Operasional & Layanan Pelanggan' }}
                    </p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">Lembar Rekapitulasi Presensi Resmi &bull; Tercatat Terverifikasi Digital</p>
        </div>

        <!-- JUDUL & PERIODE -->
        <div class="text-center space-y-1 border-b border-slate-200 pb-3">
            <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">LEMBAR REKAPITULASI PRESENSI KEHADIRAN</h3>
            <p class="text-xs text-slate-600">Periode: <span class="font-bold text-slate-800">{{ $periodeText }}</span></p>
        </div>

        <!-- IDENTITAS PENGGUNA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs p-4 rounded-2xl bg-slate-50/70 border border-slate-200">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-32 text-slate-500">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">
                        {{ $user->magang?->nama_lengkap ?? ($user->cs?->nama_lengkap ?? $user->username) }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500">{{ $user->role === 'magang' ? 'Nomor Induk / NIM:' : 'NIK Karyawan:' }}</span>
                    <span class="font-semibold text-slate-800">
                        {{ $user->magang?->no_induk ?? ($user->cs?->nik ?? '-') }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500">Peran / Kategori:</span>
                    <span class="font-semibold text-slate-800 uppercase">
                        {{ $user->role === 'magang' ? 'Peserta Magang' : 'Customer Service (CS)' }}
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                @if($user->role === 'magang' && $user->magang)
                    <div class="flex">
                        <span class="w-32 text-slate-500">Divisi Penempatan:</span>
                        <span class="font-semibold text-slate-800">{{ $user->magang?->divisi?->nama_divisi ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-slate-500">Instansi Pendidikan:</span>
                        <span class="font-semibold text-slate-800">{{ $user->magang?->instansi_pendidikan ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-slate-500">Pembimbing:</span>
                        <span class="font-semibold text-slate-800">{{ $user->magang?->pembimbing?->nama_lengkap ?? '-' }}</span>
                    </div>
                @else
                    <div class="flex">
                        <span class="w-32 text-slate-500">Jabatan:</span>
                        <span class="font-semibold text-slate-800">{{ $user->cs?->jabatan ?? 'Customer Service' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-slate-500">Supervisor / Validator:</span>
                        <span class="font-semibold text-slate-800">{{ $user->cs?->pembimbing?->nama_lengkap ?? '-' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- RINGKASAN STATISTIK KEHADIRAN -->
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Total Hadir</div>
                <div class="text-xl font-black text-emerald-800 mt-0.5">{{ $stats['total_hadir'] }} Hari</div>
            </div>
            <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/50">
                <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Hadir Onsite (Kantor)</div>
                <div class="text-xl font-black text-blue-800 mt-0.5">{{ $stats['total_onsite'] }} Hari</div>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50">
                <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Hadir WFH (Remote)</div>
                <div class="text-xl font-black text-amber-800 mt-0.5">{{ $stats['total_wfh'] }} Hari</div>
            </div>
        </div>

        <!-- TABEL RINCIAN PRESENSI -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="p-2.5 border-r border-slate-300 w-10 text-center">No</th>
                        <th class="p-2.5 border-r border-slate-300">Hari / Tanggal</th>
                        <th class="p-2.5 border-r border-slate-300 text-center">Mode Kerja</th>
                        <th class="p-2.5 border-r border-slate-300 text-center">Jam Masuk</th>
                        <th class="p-2.5 border-r border-slate-300 text-center">Jam Pulang</th>
                        <th class="p-2.5 border-r border-slate-300 text-center">Status</th>
                        <th class="p-2.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($presensi as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-2 border-r border-slate-200 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-2 border-r border-slate-200 whitespace-nowrap font-medium text-slate-900">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center uppercase font-semibold text-[10px]">
                                {{ $item->mode_kerja }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center font-mono font-medium">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center font-mono font-medium">
                                {{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="p-2 text-slate-600 max-w-xs truncate">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">
                                Belum ada rekaman presensi pada periode yang dipilih.
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
                        {{ $pembimbing->nama_lengkap ?? ($user->magang?->pembimbing?->nama_lengkap ?? ($user->cs?->pembimbing?->nama_lengkap ?? '( .................................................. )')) }}
                    </p>
                    <p class="text-slate-500 text-[11px] mt-0.5">
                        NIP. {{ $pembimbing->nip ?? ($user->magang?->pembimbing?->nip ?? ($user->cs?->pembimbing?->nip ?? '-')) }}
                    </p>
                    <p class="text-slate-400 text-[10px]">
                        {{ $pembimbing->jabatan ?? ($user->magang?->pembimbing?->jabatan ?? ($user->cs?->pembimbing?->jabatan ?? 'Pembimbing Lapangan')) }}
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
