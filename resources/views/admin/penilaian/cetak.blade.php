@extends('layouts.print')

@section('title', 'Cetak Rekapitulasi Penilaian Magang')

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
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">
                        {{ $pengaturan->nama_instansi ?? 'SISTEM PRESENSI & MANAJEMEN DIGITAL' }}
                    </h2>
                    <p class="text-[11px] text-slate-500 font-medium">Laporan Rekapitulasi Evaluasi & Penilaian Akhir Peserta Magang</p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">
                {{ $pengaturan->alamat_instansi ?? 'Pusat Administrasi & Layanan Publik' }} &bull; Laporan Resmi Dicetak Melalui Sistem Presensi Digital
            </p>
        </div>

        <!-- JUDUL & PARAMETER PERIODE -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">REKAPITULASI PENILAIAN AKHIR MAGANG</h3>
                <p class="text-xs text-slate-600 mt-0.5">Laporan Evaluasi Hasil Belajar & Praktik Kerja Lapangan (PKL)</p>
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <div>Waktu Cetak: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</span></div>
                <div>Filter Status: <span class="font-semibold uppercase text-slate-700">{{ request('status_nilai') ? (request('status_nilai') === 'sudah' ? 'Sudah Dinilai' : 'Belum Dinilai') : 'Semua Status' }}</span></div>
            </div>
        </div>

        <!-- RINGKASAN METRIK LAPORAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 text-center">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Peserta</div>
                <div class="text-lg font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-center">
                <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Sudah Dinilai</div>
                <div class="text-lg font-black text-emerald-700 mt-0.5">{{ $stats['sudah'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 text-center">
                <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Belum Dinilai</div>
                <div class="text-lg font-black text-amber-700 mt-0.5">{{ $stats['belum'] }}</div>
            </div>
            <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/50 text-center">
                <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Rata-rata Nilai</div>
                <div class="text-lg font-black text-blue-700 mt-0.5">{{ number_format($stats['rata_rata'], 1) }}</div>
            </div>
        </div>

        <!-- TABEL DATA REKAPITULASI PENILAIAN -->
        <div class="w-full">
            <table class="w-full table-fixed text-left text-[11px] border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="w-[4%] p-1.5 border-r border-slate-300 text-center">No</th>
                        <th class="w-[22%] p-1.5 border-r border-slate-300">Peserta Magang</th>
                        <th class="w-[22%] p-1.5 border-r border-slate-300">Divisi & Asal Lembaga</th>
                        <th class="w-[20%] p-1.5 border-r border-slate-300">Pembimbing Lapangan</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Nilai Akhir</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Predikat</th>
                        <th class="w-[12%] p-1.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($magangList as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-1.5 border-r border-slate-200 text-center font-semibold text-slate-500 break-words">{{ $index + 1 }}</td>
                            <td class="p-1.5 border-r border-slate-200 font-bold text-slate-800 break-words">
                                <div>{{ $item->nama_lengkap }}</div>
                                <div class="text-[9px] font-normal text-slate-500">NIS/NIM: {{ $item->no_induk }}</div>
                            </td>
                            <td class="p-1.5 border-r border-slate-200 break-words">
                                <div class="font-semibold text-slate-800">{{ $item->divisi->nama_divisi ?? '-' }}</div>
                                <div class="text-[9px] text-slate-500">{{ $item->instansi_pendidikan }}</div>
                            </td>
                            <td class="p-1.5 border-r border-slate-200 break-words">
                                <div class="font-semibold text-slate-800">{{ $item->pembimbing->nama_lengkap ?? '-' }}</div>
                                <div class="text-[9px] text-slate-400">NIP: {{ $item->pembimbing->nip ?? '-' }}</div>
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center break-words font-black text-slate-900">
                                {{ $item->penilaian ? $item->penilaian->total_nilai : '-' }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center break-words font-bold">
                                @if($item->penilaian)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] uppercase {{ $item->penilaian->predikat === 'A' ? 'bg-emerald-100 text-emerald-800' : ($item->penilaian->predikat === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $item->penilaian->predikat }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-1.5 text-center break-words">
                                @if($item->penilaian)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        Sudah Dinilai
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-100 text-amber-800">
                                        Belum Dinilai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">
                                Tidak ada data penilaian yang sesuai dengan parameter filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- CATATAN DOKUMEN -->
        <div class="pt-2 text-[10px] text-slate-400 space-y-0.5">
            <p>1. Rekapitulasi penilaian ini merupakan kompilasi resmi dari evaluasi berkala dan penilaian akhir.</p>
            <p>2. Penilaian dihitung secara terbobot berdasarkan kriteria penilaian resmi instansi.</p>
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
