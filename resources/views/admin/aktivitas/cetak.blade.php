@extends('layouts.print')

@section('title', 'Cetak Rekap Aktivitas Harian')

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
                    <p class="text-[11px] text-slate-500 font-medium">Laporan Rekapitulasi Catatan Aktivitas & Progres Pekerjaan Peserta Magang</p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">{{ $pengaturan->alamat_instansi ?? 'Pusat Administrasi' }} &bull; Laporan Resmi Dicetak Melalui Sistem Presensi Digital</p>
        </div>

        <!-- JUDUL & PARAMETER PERIODE -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">REKAPITULASI LOG AKTIVITAS HARIAN MAGANG</h3>
                <p class="text-xs text-slate-600 mt-0.5">Periode Laporan: <span class="font-bold text-slate-800">{{ $periodeText }}</span></p>
            </div>
            <div class="text-xs text-slate-500 sm:text-right">
                <div>Waktu Cetak: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WITA</span></div>
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
        <div class="w-full">
            <table class="w-full table-fixed text-left text-[11px] border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="w-[4%] p-1.5 border-r border-slate-300 text-center">No</th>
                        <th class="w-[13%] p-1.5 border-r border-slate-300">Tanggal</th>
                        <th class="w-[18%] p-1.5 border-r border-slate-300">Nama Peserta</th>
                        <th class="w-[35%] p-1.5 border-r border-slate-300">Uraian Tugas / Pekerjaan Harian</th>
                        <th class="w-[8%] p-1.5 border-r border-slate-300 text-center">Progres</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Status</th>
                        <th class="w-[12%] p-1.5">Catatan Pembimbing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($aktivitas as $index => $item)
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
                            <td class="p-1.5 border-r border-slate-200 text-slate-800 leading-relaxed break-words">
                                {{ $item->isi }}
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center font-bold text-slate-700 break-words">
                                {{ $item->progress }}%
                            </td>
                            <td class="p-1.5 border-r border-slate-200 text-center break-words">
                                @if($item->status === 'approve')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800">Disetujui</span>
                                @elseif($item->status === 'pending')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-100 text-amber-800">Pending</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-rose-100 text-rose-800">Revisi</span>
                                @endif
                            </td>
                            <td class="p-1.5 text-slate-600 text-[10px] break-words">
                                <div>{{ $item->catatan_validasi ?? '-' }}</div>
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
            <p>1. Log aktivitas ini merupakan bukti sah pelaksanaan tugas peserta magang.</p>
            <p>2. Setiap aktivitas yang disetujui telah diverifikasi oleh Pembimbing Lapangan.</p>
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
