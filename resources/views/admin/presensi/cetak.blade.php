@extends('layouts.print')

@section('title', 'Cetak Rekap Presensi')

@section('content')
<div class="max-w-5xl mx-auto">

    <!-- Printable Official Report Sheet -->
    <div class="print-sheet bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-sm text-slate-800 space-y-6">

        <!-- KOP DOKUMEN RESMI -->
        <div class="border-b-2 border-slate-900 pb-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-xs">
                    PD
                </div>
                <div class="text-left">
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">{{ $pengaturan->nama_instansi ?? 'SISTEM PRESENSI & MANAJEMEN DIGITAL' }}</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Laporan Rekapitulasi Presensi & Kehadiran Peserta Magang</p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">{{ $pengaturan->alamat_instansi ?? 'Pusat Administrasi' }} &bull; Laporan Resmi Dicetak Melalui Sistem Presensi Digital</p>
        </div>

        <!-- JUDUL & PARAMETER PERIODE -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">REKAPITULASI PRESENSI KEHADIRAN MAGANG</h3>
                <p class="text-xs text-slate-600 mt-0.5">Periode Laporan: <span class="font-bold text-slate-800">{{ $periodeText }}</span></p>
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <div>Waktu Cetak: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</span></div>
                <div>Kategori: <span class="font-semibold uppercase text-slate-700">Peserta Magang</span></div>
            </div>
        </div>

        <!-- RINGKASAN METRIK LAPORAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 text-center">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Catatan</div>
                <div class="text-lg font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-center">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Total Hadir</div>
                <div class="text-lg font-black text-emerald-700 mt-0.5">{{ $stats['hadir'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/50 text-center">
                <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Onsite (Kantor)</div>
                <div class="text-lg font-black text-blue-700 mt-0.5">{{ $stats['onsite'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 text-center">
                <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">WFH (Remote)</div>
                <div class="text-lg font-black text-amber-700 mt-0.5">{{ $stats['wfh'] }}</div>
            </div>
        </div>

        <!-- TABEL DATA REKAPITULASI PRESENSI -->
        <div class="w-full">
            <table class="w-full table-fixed text-left text-[11px] border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="w-[4%] p-1.5 border-r border-slate-300 text-center">No</th>
                        <th class="w-[12%] p-1.5 border-r border-slate-300">Tanggal</th>
                        <th class="w-[26%] p-1.5 border-r border-slate-300">Nama Peserta &amp; Divisi</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Mode</th>
                        <th class="w-[12%] p-1.5 border-r border-slate-300 text-center">Jam Masuk</th>
                        <th class="w-[12%] p-1.5 border-r border-slate-300 text-center">Jam Pulang</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Status</th>
                        <th class="w-[14%] p-1.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($presensi as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-1.5 border-r border-slate-200 text-center font-semibold text-slate-500 break-words">{{ $index + 1 }}</td>
                            <td class="p-1.5 border-r border-slate-200 font-medium text-slate-900 break-words">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 font-bold text-slate-800 break-words">
                                <div>{{ $item->nama_lengkap }}</div>
                                @if($item->pengguna && $item->pengguna->magang && $item->pengguna->magang->divisi)
                                    <div class="text-[9px] font-normal text-slate-500">{{ $item->pengguna->magang->divisi->nama_divisi }}</div>
                                @endif
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center uppercase font-semibold text-[10px] break-words">
                                {{ $item->mode_kerja }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center font-mono font-medium text-[10px] break-words">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center font-mono font-medium text-[10px] break-words">
                                {{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center break-words">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $item->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="p-1.5 text-slate-600 break-words text-[10px]">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">
                                Tidak ada data presensi yang sesuai dengan parameter filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- CATATAN DOKUMEN -->
        <div class="pt-2 text-[10px] text-slate-400 space-y-0.5">
            <p>1. Rekapitulasi ini dihasilkan secara otomatis dari sistem informasi manajemen presensi magang.</p>
            <p>2. Validitas kehadiran didasarkan pada verifikasi selfie dan koordinat GPS radius kantor.</p>
        </div>

        <!-- KOLOM TANDA TANGAN RESMI KETUA INSTANSI (DARI TABEL PENGATURAN) -->
        <div class="pt-6 border-t border-slate-200 flex justify-end text-xs text-center" style="page-break-inside: avoid;">
            <div class="w-72 space-y-16">
                <div>
                    <p class="text-slate-500">{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                    <p class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5">
                        {{ $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas' }}
                    </p>
                    <p class="text-slate-500 text-[11px]">
                        {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                    </p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">
                        {{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}
                    </p>
                    @if(!empty($pengaturan->pangkat_golongan))
                        <p class="text-slate-500 text-[11px] mt-0.5">
                            {{ $pengaturan->pangkat_golongan }}
                        </p>
                    @endif
                    <p class="text-slate-500 text-[11px] mt-0.5">
                        NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
