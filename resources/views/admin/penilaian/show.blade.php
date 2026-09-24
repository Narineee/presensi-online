@extends('layouts.print')

@section('title', 'Lembar Penilaian - ' . $penilaian->magang->nama_lengkap)

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Official Printable Score Sheet (A4 format 1 lembar utuh) -->
    <div class="print-sheet bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm text-slate-800 space-y-4">

        <!-- KOP SURAT / DOKUMEN RESMI -->
        <div class="border-b-2 border-slate-900 pb-3 text-center">
            <div class="flex items-center justify-center gap-3 mb-1.5">
                <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-xs">
                    PD
                </div>
                <div class="text-left">
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">
                        {{ $pengaturan->nama_instansi ?? 'SISTEM PRESENSI & MANAJEMEN DIGITAL' }}
                    </h2>
                    <p class="text-[11px] text-slate-500 font-medium">
                        Divisi {{ $penilaian->magang->divisi->nama_divisi ?? 'Operasional & Sumber Daya' }}
                    </p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400">
                {{ $pengaturan->alamat_instansi ?? 'Pusat Administrasi & Layanan Publik' }} &bull; Laporan Resmi Dicetak Melalui Sistem Presensi Digital
            </p>
        </div>

        <!-- Judul Lembar Penilaian -->
        <div class="text-center pt-1">
            <h1 class="text-base font-black uppercase tracking-wide text-slate-900">LEMBAR PENILAIAN AKHIR MAGANG</h1>
            <p class="text-[11px] text-slate-500">PRAKTIK KERJA LAPANGAN (PKL) / PROGRAM INTERNSHIP</p>
        </div>

        <!-- Data Identitas Peserta Magang (2 Kolom Compact) -->
        <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-200/80 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5">
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Nama Peserta:</span>
                    <span class="font-bold text-slate-900 break-words">{{ $penilaian->magang->nama_lengkap }}</span>
                </div>
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Unit / Divisi:</span>
                    <span class="font-semibold text-slate-800 break-words">{{ $penilaian->magang->divisi->nama_divisi ?? '-' }}</span>
                </div>
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Nomor Induk (NIS/NIM):</span>
                    <span class="font-semibold text-slate-800 break-words">{{ $penilaian->magang->no_induk }}</span>
                </div>
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Program Studi / Jurusan:</span>
                    <span class="text-slate-800 break-words">{{ $penilaian->magang->jurusan ?? '-' }}</span>
                </div>
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Asal Lembaga / Kampus:</span>
                    <span class="text-slate-800 break-words">{{ $penilaian->magang->instansi_pendidikan }}</span>
                </div>
                <div class="flex items-baseline">
                    <span class="w-36 text-slate-500 shrink-0">Periode Pelaksanaan:</span>
                    <span class="text-slate-800 break-words">
                        {{ $penilaian->magang->tanggal_mulai ? $penilaian->magang->tanggal_mulai->format('d/m/Y') : '-' }}
                        s/d
                        {{ $penilaian->magang->tanggal_selesai ? $penilaian->magang->tanggal_selesai->format('d/m/Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Nilai Kriteria -->
        <div class="w-full">
            <table class="w-full table-fixed text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 text-center text-[11px]">
                        <th class="w-[6%] border border-slate-300 py-1.5 px-2">No</th>
                        <th class="w-[50%] border border-slate-300 py-1.5 px-3 text-left">Unsur / Kriteria Penilaian</th>
                        <th class="w-[12%] border border-slate-300 py-1.5 px-2">Bobot</th>
                        <th class="w-[16%] border border-slate-300 py-1.5 px-2">Nilai (0-100)</th>
                        <th class="w-[16%] border border-slate-300 py-1.5 px-2">Nilai Terbobot</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($penilaian->detail as $index => $detail)
                        @php
                            $bobot = $detail->kriteria->bobot ?? 0;
                            $terbobot = round(($detail->nilai * $bobot) / 100, 2);
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-slate-50/40' : 'bg-white' }}">
                            <td class="border border-slate-300 py-1.5 px-2 text-center text-slate-500 break-words">{{ $loop->iteration }}</td>
                            <td class="border border-slate-300 py-1.5 px-3 font-medium text-slate-900 break-words">{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</td>
                            <td class="border border-slate-300 py-1.5 px-2 text-center text-slate-700 break-words">{{ $bobot }}%</td>
                            <td class="border border-slate-300 py-1.5 px-2 text-center font-bold text-slate-800 break-words">{{ $detail->nilai }}</td>
                            <td class="border border-slate-300 py-1.5 px-2 text-center font-semibold text-slate-700 break-words">{{ number_format($terbobot, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-300">
                        <td colspan="3" class="border border-slate-300 py-1.5 px-3 text-right uppercase tracking-wider text-[11px]">
                            Total Nilai Akhir
                        </td>
                        <td class="border border-slate-300 py-1.5 px-2 text-center text-sm font-black text-blue-700">
                            {{ $penilaian->total_nilai }}
                        </td>
                        <td class="border border-slate-300 py-1.5 px-2 text-center text-xs font-semibold text-slate-500">
                            / 100
                        </td>
                    </tr>
                    <tr class="bg-slate-50 font-bold text-slate-900">
                        <td colspan="3" class="border border-slate-300 py-1.5 px-3 text-right uppercase tracking-wider text-[11px]">
                            Predikat Kelulusan
                        </td>
                        <td colspan="2" class="border border-slate-300 py-1.5 px-3 text-center">
                            <span class="text-sm font-black text-slate-900">{{ $penilaian->predikat }}</span>
                            <span class="text-xs font-semibold text-slate-600 ml-1">({{ $penilaian->keterangan_predikat }})</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tabel Konversi Predikat Ringkas -->
        <div class="text-[10px] text-slate-600 bg-slate-50/60 px-3 py-1.5 rounded-lg border border-slate-200/80 flex flex-wrap items-center justify-between gap-1">
            <span class="font-bold text-slate-700">Standar Predikat:</span>
            <span>&bull; <strong class="text-slate-800">85 - 100:</strong> A (Sangat Baik)</span>
            <span>&bull; <strong class="text-slate-800">75 - 84:</strong> B (Baik)</span>
            <span>&bull; <strong class="text-slate-800">60 - 74:</strong> C (Cukup)</span>
            <span>&bull; <strong class="text-slate-800">&lt; 60:</strong> D (Kurang)</span>
        </div>

        <!-- Tanda Tangan Dinamis (Pembimbing Lapangan & Pimpinan Divisi / Dinas) -->
        <div class="pt-4 grid grid-cols-2 gap-8 text-xs text-center" style="page-break-inside: avoid;">
            <!-- Tanda Tangan Pembimbing Lapangan -->
            <div class="space-y-12">
                <div>
                    <p class="text-slate-500">Mengetahui & Mengesahkan,</p>
                    <p class="font-bold text-slate-800 text-xs">Pembimbing Lapangan</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">{{ $penilaian->pembimbing->nama_lengkap }}</p>
                    <p class="text-slate-500 text-[11px]">NIP. {{ $penilaian->pembimbing->nip ?? '-' }}</p>
                    <p class="text-slate-400 text-[10px]">{{ $penilaian->pembimbing->jabatan ?? 'Pembimbing Lapangan' }}</p>
                </div>
            </div>

            <!-- Tanda Tangan Pimpinan Divisi / Kepala Dinas -->
            <div class="space-y-12">
                <div>
                    <p class="text-slate-500">{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $penilaian->created_at ? $penilaian->created_at->isoFormat('D MMMM Y') : \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                    <p class="font-bold text-slate-800 text-xs">{{ $penilaian->magang->divisi->jabatan_pimpinan ?? ($pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas') }}</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 underline text-xs sm:text-sm">{{ $penilaian->magang->divisi->nama_pimpinan ?? ($pengaturan->nama_kepala_dinas ?? '-') }}</p>
                    <p class="text-slate-500 text-[11px]">NIP. {{ $penilaian->magang->divisi->nip_pimpinan ?? ($pengaturan->nip_kepala_dinas ?? '-') }}</p>
                    <p class="text-slate-400 text-[10px]">{{ $penilaian->magang->divisi->nama_divisi ?? ($pengaturan->nama_instansi ?? 'Instansi') }}</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
