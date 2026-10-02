@extends('layouts.pembimbing')

@section('title', 'Detail Rekap Presensi - ' . $magang->nama_lengkap)

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pembimbing.binaan.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition flex items-center gap-1">
                    &larr; Kembali ke Daftar Binaan
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                Rekapitulasi Kehadiran: {{ $magang->nama_lengkap }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Rincian komprehensif kehadiran hari kerja, permohonan izin/sakit, dan log presensi harian peserta.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($magang->penilaian)
                <a href="{{ route('pembimbing.penilaian.show', $magang->penilaian->id) }}" class="px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 text-xs font-bold transition flex items-center gap-1.5">
                    <span>⭐ Nilai: {{ number_format($magang->penilaian->nilai_akhir, 1) }}</span>
                </a>
            @else
                <a href="{{ route('pembimbing.penilaian.create', ['magang_id' => $magang->id]) }}" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <span>⭐ Input Penilaian Akhir</span>
                </a>
            @endif

            <a href="{{ route('pembimbing.pekerjaan.index', ['magang_id' => $magang->id]) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition flex items-center gap-1.5">
                <span>💼 Tugas Peserta</span>
            </a>
        </div>
    </div>

    <!-- Profil Peserta Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if($magang->foto)
                    <img src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-purple-200 shadow-sm shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 font-black text-xl flex items-center justify-center shrink-0 border-2 border-purple-200 shadow-sm">
                        {{ strtoupper(substr($magang->nama_lengkap, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900">{{ $magang->nama_lengkap }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $magang->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $magang->status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500 font-medium mt-1 flex-wrap">
                        <span class="font-mono">NIM: <strong class="text-slate-700">{{ $magang->no_induk }}</strong></span>
                        <span>&bull;</span>
                        <span>Instansi: <strong class="text-slate-700">{{ $magang->instansi_pendidikan ?? '-' }}</strong></span>
                        <span>&bull;</span>
                        <span>Jurusan: <strong class="text-slate-700">{{ $magang->jurusan ?? '-' }}</strong></span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-600 mt-2 flex-wrap">
                        <span class="bg-purple-50 text-purple-700 font-bold px-2.5 py-0.5 rounded-lg border border-purple-200 text-[11px]">
                            🏢 Divisi: {{ $magang->divisi->nama_divisi ?? '-' }}
                        </span>
                        @if($magang->no_hp)
                            <span class="text-slate-500 font-mono text-[11px]">
                                📞 {{ $magang->no_hp }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Periode Magang & Status Hari Ini -->
            <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end gap-2 shrink-0">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-right md:min-w-[220px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Periode Magang Resmi</span>
                    <div class="text-xs font-bold text-slate-900 mt-0.5">
                        {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->translatedFormat('d M Y') : '-' }} &ndash; {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->translatedFormat('d M Y') : '-' }}
                    </div>
                </div>
                <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5 mt-1">
                    <span class="text-slate-400 font-normal">Status Hari Ini:</span>
                    <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-200">
                        {{ $rekap['status_hari_ini'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kotak Ringkasan Rekapitulasi (Sesuai Kebutuhan) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3.5">
        <!-- 1. Total Hari Kerja Magang -->
        <div class="p-4 rounded-2xl bg-white border border-purple-200 shadow-xs col-span-2 sm:col-span-2 lg:col-span-1">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 block">Total Hari Kerja</span>
            <div class="text-2xl font-black text-purple-800 mt-1">{{ $rekap['total_hari_magang'] }} <span class="text-xs font-bold text-purple-500">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Hanya hari kerja resmi (libur tidak dihitung)</p>
        </div>

        <!-- 2. Hadir s/d Hari Ini -->
        <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 block">Total Hadir</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $rekap['total_hadir'] }} <span class="text-xs font-bold text-emerald-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">s/d hari ini</p>
        </div>

        <!-- 3. Sakit -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500 block">Sakit</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $rekap['total_sakit'] }} <span class="text-xs font-bold text-rose-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Surat dokter</p>
        </div>

        <!-- 4. Izin -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 block">Izin</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $rekap['total_izin'] }} <span class="text-xs font-bold text-blue-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Halangan penting</p>
        </div>

        <!-- 5. Cuti -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 block">Cuti</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $rekap['total_cuti'] }} <span class="text-xs font-bold text-purple-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Hak libur resmi</p>
        </div>

        <!-- 6. Tugas Luar -->
        <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600 block">Tugas Luar</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $rekap['total_tugas_luar'] }} <span class="text-xs font-bold text-amber-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Dinas lapangan</p>
        </div>

        <!-- 7. Alpa / Tidak Hadir -->
        <div class="p-4 rounded-2xl bg-white border border-rose-200 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700 block">Alpa / Tanpa Ket</span>
            <div class="text-2xl font-black text-rose-700 mt-1">{{ $rekap['total_alpa'] }} <span class="text-xs font-bold text-rose-400">Hari</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Tanpa keterangan</p>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
        <div>
            <span class="text-xs font-bold text-purple-200 uppercase tracking-wider">Persentase Tingkat Kehadiran Kerja</span>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-3xl font-black">{{ number_format($rekap['persentase_kehadiran'], 1) }}%</span>
                <span class="text-xs text-purple-200 font-medium">
                    ({{ $rekap['total_hadir'] }} hari hadir dari {{ $rekap['hari_kerja_berjalan'] }} hari kerja yang telah berjalan)
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
                <span class="text-[10px] uppercase font-bold text-purple-200 block">Hari Berjalan</span>
                <span class="text-base font-extrabold">{{ $rekap['hari_kerja_berjalan'] }} Hari</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
                <span class="text-[10px] uppercase font-bold text-purple-200 block">Sisa Hari Kerja</span>
                <span class="text-base font-extrabold">{{ $rekap['sisa_hari_kerja'] }} Hari</span>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Log Kehadiran Harian -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Log Presensi Harian Peserta (Awal Magang s/d Hari Ini)
                </h3>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Total <strong class="text-slate-800">{{ $logHarian->count() }}</strong> hari kalender
            </div>
        </div>

        @if($logHarian->isEmpty())
            <div class="p-10 text-center text-slate-400 text-xs">
                Belum ada data riwayat presensi yang tersedia untuk periode ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Tanggal &amp; Hari</th>
                            <th class="py-3 px-3">Tipe Hari</th>
                            <th class="py-3 px-3">Status Kehadiran</th>
                            <th class="py-3 px-3">Jam Masuk</th>
                            <th class="py-3 px-3">Jam Pulang</th>
                            <th class="py-3 px-3">Mode Kerja</th>
                            <th class="py-3 px-4">Keterangan / Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($logHarian as $log)
                            <tr class="hover:bg-slate-50/80 transition {{ $log['is_hari_kerja'] ? '' : 'bg-slate-50/40 text-slate-400' }}">
                                <!-- Tanggal -->
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $log['tanggal']->translatedFormat('d M Y') }}
                                    <span class="font-normal font-sans text-[11px] text-slate-500 ml-1">({{ $log['hari'] }})</span>
                                </td>

                                <!-- Tipe Hari -->
                                <td class="py-3 px-3">
                                    @if(! $log['is_hari_kerja'])
                                        @if($log['status'] === 'weekend')
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-600">
                                                Akhir Pekan
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700">
                                                Hari Libur
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Hari Kerja
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Kehadiran -->
                                <td class="py-3 px-3">
                                    @if($log['status'] === 'hadir')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                        </span>
                                    @elseif($log['status'] === 'tugas_luar')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Tugas Luar
                                        </span>
                                    @elseif($log['status'] === 'sakit')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Sakit
                                        </span>
                                    @elseif($log['status'] === 'izin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Izin
                                        </span>
                                    @elseif($log['status'] === 'cuti')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Cuti
                                        </span>
                                    @elseif($log['status'] === 'alpa')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Alpa
                                        </span>
                                    @elseif($log['status'] === 'belum_absen')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            Belum Presensi
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">
                                            {{ ucfirst($log['status']) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Jam Masuk -->
                                <td class="py-3 px-3 font-mono text-xs">
                                    {{ $log['jam_masuk'] ?: '-' }}
                                </td>

                                <!-- Jam Pulang -->
                                <td class="py-3 px-3 font-mono text-xs">
                                    {{ $log['jam_keluar'] ?: '-' }}
                                </td>

                                <!-- Mode Kerja -->
                                <td class="py-3 px-3 text-xs uppercase font-bold text-slate-600">
                                    {{ $log['mode_kerja'] ?: '-' }}
                                </td>

                                <!-- Keterangan -->
                                <td class="py-3 px-4 text-xs text-slate-600">
                                    {{ $log['keterangan'] ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
