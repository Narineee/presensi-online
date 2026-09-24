@extends('layouts.print')

@section('title', 'Cetak Rekap Aktivitas Harian')

@section('content')
<div class="max-w-4xl mx-auto">

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

        <!-- IDENTITAS PENGGUNA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs p-4 rounded-2xl bg-slate-50/70 border border-slate-200">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">
                        {{ $user->magang?->nama_lengkap ?? $user->username }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">Nomor Induk / NIM:</span>
                    <span class="font-semibold text-slate-800">
                        {{ $user->magang?->no_induk ?? '-' }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">Instansi Pendidikan:</span>
                    <span class="font-semibold text-slate-800">
                        {{ $user->magang?->instansi_pendidikan ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500">Divisi Penempatan:</span>
                    <span class="font-semibold text-slate-800">{{ $user->magang?->divisi?->nama_divisi ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">Jurusan / Program:</span>
                    <span class="font-semibold text-slate-800 uppercase">{{ $user->magang?->jurusan ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500">Pembimbing Lapangan:</span>
                    <span class="font-semibold text-slate-800">{{ $user->magang?->pembimbing?->nama_lengkap ?? '-' }}</span>
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
        <div class="w-full">
            <table class="w-full table-fixed text-left text-[11px] border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                    <tr>
                        <th class="w-[5%] p-1.5 border-r border-slate-300 text-center">No</th>
                        <th class="w-[18%] p-1.5 border-r border-slate-300">Hari / Tanggal</th>
                        <th class="w-[42%] p-1.5 border-r border-slate-300">Uraian Tugas & Kegiatan Harian</th>
                        <th class="w-[10%] p-1.5 border-r border-slate-300 text-center">Progres</th>
                        <th class="w-[11%] p-1.5 border-r border-slate-300 text-center">Status</th>
                        <th class="w-[14%] p-1.5">Catatan Pembimbing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($aktivitas as $index => $item)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-1.5 border-r border-slate-200 text-center font-semibold text-slate-500 break-words">{{ $index + 1 }}</td>
                            <td class="p-1.5 border-r border-slate-200 font-medium text-slate-900 break-words">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMM Y') }}
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
