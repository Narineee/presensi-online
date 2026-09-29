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

        @if(isset($presensiScore))
            <!-- Ringkasan Objektif Presensi Digital -->
            <div class="bg-gradient-to-br from-emerald-50 via-teal-50/40 to-slate-50 p-3 rounded-xl border border-emerald-200/90 space-y-2">
                <div class="flex items-center justify-between gap-2 border-b border-emerald-200/60 pb-1.5">
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center font-bold text-[10px] shadow-xs">⏱️</span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Rincian Evaluasi Presensi Digital (Objektif)</h4>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $presensiScore['badge_class'] }} border">
                            Capaian: {{ $presensiScore['skor_presensi'] }}% ({{ $presensiScore['predikat'] }})
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <div class="bg-white/80 p-1.5 rounded-lg border border-emerald-100 shadow-2xs">
                        <span class="text-slate-400 block text-[9px] uppercase font-semibold">Target Jam Kerja</span>
                        <span class="font-mono font-bold text-slate-800 text-[11px]">{{ number_format($presensiScore['target_menit']) }} Menit</span>
                        <span class="text-[9px] text-slate-400 block">({{ $presensiScore['target_hari'] }} hari kerja)</span>
                    </div>
                    <div class="bg-white/80 p-1.5 rounded-lg border border-emerald-100 shadow-2xs">
                        <span class="text-slate-400 block text-[9px] uppercase font-semibold">Total Realisasi</span>
                        <span class="font-mono font-bold text-emerald-700 text-[11px]">{{ number_format($presensiScore['total_menit_realisasi']) }} Menit</span>
                        <span class="text-[9px] text-emerald-600 block">Persentase: {{ $presensiScore['skor_presensi'] }}%</span>
                    </div>
                    <div class="bg-white/80 p-1.5 rounded-lg border border-emerald-100 shadow-2xs">
                        <span class="text-slate-400 block text-[9px] uppercase font-semibold">Kehadiran & Izin</span>
                        <span class="font-mono font-bold text-slate-700 text-[11px]">{{ $presensiScore['total_hari_hadir'] }} Hadir &bull; {{ $presensiScore['total_hari_izin'] }} Izin</span>
                        <span class="text-[9px] text-blue-600 block">Izin disetujui (480m)</span>
                    </div>
                    <div class="bg-white/80 p-1.5 rounded-lg border border-emerald-100 shadow-2xs">
                        <span class="text-slate-400 block text-[9px] uppercase font-semibold">Lupa Checkout / Telat</span>
                        <span class="font-mono font-bold text-amber-700 text-[11px]">{{ $presensiScore['total_hari_lupa_checkout'] }} Lupa &bull; {{ $presensiScore['total_hari_terlambat'] }}x Telat</span>
                        <span class="text-[9px] text-rose-500 block">-{{ $presensiScore['menit_terlambat_potong'] }}m (cut 50%)</span>
                    </div>
                </div>
            </div>
        @endif

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
                            $isPresensi = $detail->kriteria->is_presensi ?? false;
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-slate-50/40' : 'bg-white' }}">
                            <td class="border border-slate-300 py-1.5 px-2 text-center text-slate-500 break-words">{{ $loop->iteration }}</td>
                            <td class="border border-slate-300 py-1.5 px-3 font-medium text-slate-900 break-words">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $detail->kriteria->nama ?? 'Kriteria #' . $detail->kriteria_id }}</span>
                                    @if($isPresensi)
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Objektif (Presensi)
                                        </span>
                                    @endif
                                </div>
                            </td>
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
            <span>&bull; <strong class="text-slate-800">90 - 100:</strong> A (Sangat Baik)</span>
            <span>&bull; <strong class="text-slate-800">80 - 89:</strong> B (Baik)</span>
            <span>&bull; <strong class="text-slate-800">70 - 79:</strong> C (Cukup Baik)</span>
            <span>&bull; <strong class="text-slate-800">60 - 69:</strong> D (Kurang Baik)</span>
            <span>&bull; <strong class="text-slate-800">&lt; 60:</strong> E (Tidak Baik)</span>
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
