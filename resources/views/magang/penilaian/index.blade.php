@extends('layouts.user')

@section('title', 'Lembar Penilaian Akhir Magang')

@section('styles')
<style>
    @media print {
        /* Sembunyikan navbar dan tombol saat dicetak */
        header, footer, .no-print, nav, .btn-print-group {
            display: none !important;
        }

        *, *::before, *::after {
            box-shadow: none !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        *::-webkit-scrollbar,
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        html, body {
            background-color: white !important;
            color: black !important;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            width: 100% !important;
            max-width: 100% !important;
            overflow: visible !important;
            overflow-x: clip !important;
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
            border-collapse: collapse !important;
            width: 100% !important;
        }

        th, td {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(!$penilaian)
        <!-- Empty State (Belum Dinilai) -->
        <div class="bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-xs text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Penilaian Akhir Belum Diterbitkan</h2>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    Pembimbing lapangan Anda belum menginput evaluasi kelulusan akhir. Pastikan seluruh absensi harian dan log aktivitas harian Anda telah lengkap dan divalidasi.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Kembali ke Presensi
                </a>
            </div>
        </div>
    @else
        <!-- Action Bar (No Print) -->
        <div class="no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h1 class="text-base font-bold text-slate-900">Lembar Nilai Akhir Magang</h1>
                <p class="text-xs text-slate-500">Nilai resmi dari pembimbing lapangan.</p>
            </div>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                </svg>
                Cetak Lembar Nilai (PDF)
            </button>
        </div>

        <!-- Official Printable Score Sheet (A4 format) -->
        <div class="print-sheet bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-sm text-slate-800 space-y-6">

            <!-- KOP SURAT / DOKUMEN RESMI -->
            <div class="border-b-2 border-slate-900 pb-4 text-center">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-xs">
                        PD
                    </div>
                    <div class="text-left">
                        <h2 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">SISTEM PRESENSI & MANAJEMEN DIGITAL</h2>
                        <p class="text-[11px] text-slate-500 font-medium">Divisi {{ $penilaian->magang->divisi->nama_divisi ?? 'Operasional & Sumber Daya' }}</p>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400">Jl. Protokol Teknologi No. 88, Pusat Inovasi Digital &bull; Email: admin@presensi-digital.local</p>
            </div>

            <!-- Judul Lembar Penilaian -->
            <div class="text-center pt-2">
                <h1 class="text-lg font-black uppercase tracking-wide text-slate-900">LEMBAR PENILAIAN AKHIR MAGANG</h1>
                <p class="text-xs text-slate-500 mt-0.5">PRAKTIK KERJA LAPANGAN (PKL) / PROGRAM INTERNSHIP</p>
            </div>

            <!-- Data Identitas Peserta Magang -->
            <div class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 text-xs">
                <table class="w-full">
                    <tr class="py-1">
                        <td class="w-40 font-semibold text-slate-600 py-1">Nama Peserta Magang</td>
                        <td class="w-4 py-1">:</td>
                        <td class="font-bold text-slate-900 py-1">{{ $penilaian->magang->nama_lengkap }}</td>
                    </tr>
                    <tr class="py-1">
                        <td class="font-semibold text-slate-600 py-1">Nomor Induk (NIS/NIM)</td>
                        <td class="py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $penilaian->magang->no_induk }}</td>
                    </tr>
                    <tr class="py-1">
                        <td class="font-semibold text-slate-600 py-1">Asal Lembaga / Kampus</td>
                        <td class="py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $penilaian->magang->instansi_pendidikan }}</td>
                    </tr>
                    <tr class="py-1">
                        <td class="font-semibold text-slate-600 py-1">Program Studi / Jurusan</td>
                        <td class="py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $penilaian->magang->jurusan ?? '-' }}</td>
                    </tr>
                    <tr class="py-1">
                        <td class="font-semibold text-slate-600 py-1">Unit / Divisi Penempatan</td>
                        <td class="py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $penilaian->magang->divisi->nama_divisi ?? '-' }}</td>
                    </tr>
                    <tr class="py-1">
                        <td class="font-semibold text-slate-600 py-1">Periode Pelaksanaan</td>
                        <td class="py-1">:</td>
                        <td class="text-slate-800 py-1">
                            {{ $penilaian->magang->tanggal_mulai ? $penilaian->magang->tanggal_mulai->format('d F Y') : '-' }}
                            s/d
                            {{ $penilaian->magang->tanggal_selesai ? $penilaian->magang->tanggal_selesai->format('d F Y') : '-' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Tabel Rincian Nilai Kriteria -->
            <div>
                <table class="w-full text-xs border border-slate-300 border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 text-center">
                            <th class="border border-slate-300 py-2.5 px-3 w-12">No</th>
                            <th class="border border-slate-300 py-2.5 px-4 text-left">Unsur / Kriteria Penilaian</th>
                            <th class="border border-slate-300 py-2.5 px-3 w-24">Bobot</th>
                            <th class="border border-slate-300 py-2.5 px-3 w-28">Nilai Angka (0-100)</th>
                            <th class="border border-slate-300 py-2.5 px-3 w-28">Nilai Terbobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penilaian->detail as $index => $detail)
                            @php
                                $bobot = $detail->kriteria->bobot ?? 0;
                                $terbobot = round(($detail->nilai * $bobot) / 100, 2);
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="border border-slate-300 py-2 px-3 text-center">{{ $loop->iteration }}</td>
                                <td class="border border-slate-300 py-2 px-4 font-medium text-slate-900">{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</td>
                                <td class="border border-slate-300 py-2 px-3 text-center">{{ $bobot }}%</td>
                                <td class="border border-slate-300 py-2 px-3 text-center font-bold text-slate-800">{{ $detail->nilai }}</td>
                                <td class="border border-slate-300 py-2 px-3 text-center font-semibold text-slate-700">{{ number_format($terbobot, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-300">
                            <td colspan="3" class="border border-slate-300 py-2.5 px-4 text-right uppercase tracking-wider">
                                Total Nilai Akhir
                            </td>
                            <td class="border border-slate-300 py-2.5 px-3 text-center text-sm font-black text-blue-700">
                                {{ $penilaian->total_nilai }}
                            </td>
                            <td class="border border-slate-300 py-2.5 px-3 text-center text-sm font-black text-blue-700">
                                / 100
                            </td>
                        </tr>
                        <tr class="bg-slate-50 font-bold text-slate-900">
                            <td colspan="3" class="border border-slate-300 py-2.5 px-4 text-right uppercase tracking-wider">
                                Predikat Kelulusan
                            </td>
                            <td colspan="2" class="border border-slate-300 py-2.5 px-4 text-center">
                                <span class="text-sm font-black text-slate-900">{{ $penilaian->predikat }}</span>
                                <span class="text-xs font-semibold text-slate-600 ml-1">({{ $penilaian->keterangan_predikat }})</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Tabel Konversi Predikat -->
            <div class="text-[11px] text-slate-600 bg-slate-50/50 p-3 rounded-lg border border-slate-200/80">
                <span class="font-bold text-slate-700 block mb-1">Standar Kualifikasi Nilai:</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div>&bull; <span class="font-semibold">85 - 100</span> : A (Sangat Baik)</div>
                    <div>&bull; <span class="font-semibold">75 - 84</span> : B (Baik)</div>
                    <div>&bull; <span class="font-semibold">60 - 74</span> : C (Cukup)</div>
                    <div>&bull; <span class="font-semibold">&lt; 60</span> : D (Kurang)</div>
                </div>
            </div>

            <!-- Tanda Tangan Dinamis (Pembimbing & Pimpinan Divisi) -->
            <div class="pt-8 grid grid-cols-2 gap-8 text-xs text-center">
                <!-- Tanda Tangan Pembimbing Lapangan -->
                <div class="space-y-16">
                    <div>
                        <p class="text-slate-500">Mengetahui & Mengesahkan,</p>
                        <p class="font-bold text-slate-800">Pembimbing Lapangan</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 underline text-sm">{{ $penilaian->pembimbing->nama_lengkap }}</p>
                        <p class="text-slate-500">NIP. {{ $penilaian->pembimbing->nip ?? '-' }}</p>
                        <p class="text-slate-400 text-[11px]">{{ $penilaian->pembimbing->jabatan ?? 'Pembimbing Lapangan' }}</p>
                    </div>
                </div>

                <!-- Tanda Tangan Pimpinan Divisi / Sub-Bagian -->
                <div class="space-y-16">
                    <div>
                        <p class="text-slate-500">Ditetapkan di Kota Terkait,</p>
                        <p class="font-bold text-slate-800">{{ $penilaian->magang->divisi->jabatan_pimpinan ?? 'Kepala Sub-Bagian / Divisi' }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 underline text-sm">{{ $penilaian->magang->divisi->nama_pimpinan ?? '-' }}</p>
                        <p class="text-slate-500">NIP. {{ $penilaian->magang->divisi->nip_pimpinan ?? '-' }}</p>
                        <p class="text-slate-400 text-[11px]">{{ $penilaian->magang->divisi->nama_divisi ?? 'Divisi Terkait' }}</p>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
