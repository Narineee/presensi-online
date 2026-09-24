@extends('layouts.pembimbing')

@section('title', 'Dashboard Pembimbing')

@section('content')
<div class="space-y-6">

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Welcome & Profil Pembimbing Banner -->
    <div class="bg-gradient-to-r from-purple-800 via-indigo-800 to-purple-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-purple-900/10 border border-purple-700/50">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-purple-100 text-xs font-semibold backdrop-blur-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Portal Pembimbing Lapangan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    Selamat Datang, {{ $pembimbing->nama_lengkap ?? Auth::user()->username }}!
                </h1>
                <p class="text-purple-100 text-sm mt-1 max-w-2xl leading-relaxed">
                    Pantau daftar peserta yang Anda ampu, validasi aktivitas harian, verifikasi izin, pantau kehadiran langsung hari ini, dan berikan penilaian akhir magang.
                </p>
            </div>

            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
                    <div class="text-[10px] uppercase tracking-wider text-purple-200 font-bold">NIP Pembimbing</div>
                    <div class="text-sm font-black text-white font-mono mt-0.5">{{ $pembimbing->nip ?? '-' }}</div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
                    <div class="text-[10px] uppercase tracking-wider text-purple-200 font-bold">Jabatan</div>
                    <div class="text-sm font-bold text-white mt-0.5">{{ $pembimbing->jabatan ?? 'Pembimbing' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Aktivitas Menunggu -->
        <a href="{{ route('pembimbing.aktivitas.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-purple-300 hover:shadow-md transition flex items-center justify-between group">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Aktivitas Menunggu</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending_aktivitas'] }}</p>
                <span class="inline-flex items-center text-[11px] font-semibold text-purple-600 mt-1 group-hover:translate-x-0.5 transition-transform">
                    Validasi Sekarang &rarr;
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                ⏳
            </div>
        </a>

        <!-- Card 2: Izin Menunggu -->
        <a href="{{ route('pembimbing.izin.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-purple-300 hover:shadow-md transition flex items-center justify-between group">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Izin Menunggu</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $stats['pending_izin'] }}</p>
                <span class="inline-flex items-center text-[11px] font-semibold text-purple-600 mt-1 group-hover:translate-x-0.5 transition-transform">
                    Verifikasi Izin &rarr;
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                🩺
            </div>
        </a>

        <!-- Card 3: Kehadiran Hari Ini (Pantau Kehadiran) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kehadiran Hari Ini</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['hadir_hari_ini'] }} <span class="text-xs font-semibold text-slate-400">/ {{ $stats['total_magang'] }}</span></p>
                <span class="text-[11px] font-medium text-emerald-600">Presensi tercatat</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                &check;
            </div>
        </div>

        <!-- Card 4: Total Binaan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Binaan Diampu</p>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_magang'] }} <span class="text-xs font-semibold text-slate-400">Orang</span></p>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 font-medium">
                    <span>{{ $stats['total_magang'] }} Peserta Magang</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                👥
            </div>
        </div>
    </div>

    <!-- PANDUAN & PINTASAN TUGAS PEMBIMBING -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('pembimbing.aktivitas.index') }}" class="p-4 bg-white hover:bg-purple-50/50 rounded-2xl border border-slate-200/80 hover:border-purple-300 transition flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-lg shrink-0">
                📝
            </div>
            <div>
                <h2 class="text-xs font-bold text-slate-900">Validasi Aktivitas</h2>
                <p class="text-[11px] text-slate-500">Tinjau uraian tugas &amp; foto peserta</p>
            </div>
        </a>

        <a href="{{ route('pembimbing.izin.index') }}" class="p-4 bg-white hover:bg-purple-50/50 rounded-2xl border border-slate-200/80 hover:border-purple-300 transition flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg shrink-0">
                🩺
            </div>
            <div>
                <h2 class="text-xs font-bold text-slate-900">Verifikasi Izin &amp; Sakit</h2>
                <p class="text-[11px] text-slate-500">Periksa surat izin &amp; berkas pendukung</p>
            </div>
        </a>

        <a href="{{ route('pembimbing.penilaian.index') }}" class="p-4 bg-white hover:bg-purple-50/50 rounded-2xl border border-slate-200/80 hover:border-purple-300 transition flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shrink-0">
                ⭐
            </div>
            <div>
                <h2 class="text-xs font-bold text-slate-900">Penilaian Akhir Magang</h2>
                <p class="text-[11px] text-slate-500">Input lembar nilai &amp; kriteria kompetensi</p>
            </div>
        </a>
    </div>

    <!-- SECTION 1: DAFTAR MAGANG YANG DIAMPU -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Peserta Magang yang Diampu</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Seluruh peserta Magang yang berada di bawah bimbingan &amp; pengawasan Anda.
                </p>
            </div>

            <div class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold border border-purple-200">
                🎓 Total {{ $magangList->count() }} Peserta
            </div>
        </div>

        <!-- CONTENT: MAGANG BINAAN -->
        <div class="p-5 sm:p-6 space-y-4">
            @if($magangList->isEmpty())
                <div class="text-center py-12">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xl mb-3">
                        🎓
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum ada anak magang yang diplotkan</p>
                    <p class="text-xs text-slate-400 mt-1">Administrator akan menempatkan peserta magang ke dalam bimbingan Anda.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($magangList as $magang)
                        <div class="p-5 rounded-2xl border border-slate-200/90 hover:border-purple-300 hover:shadow-md transition bg-gradient-to-b from-white to-slate-50/50 flex flex-col justify-between">
                            <div>
                                <!-- Header Peserta: Avatar & Status -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        @if($magang->foto)
                                            <img src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="w-12 h-12 rounded-xl object-cover border-2 border-purple-200 shadow-xs shrink-0">
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-extrabold text-sm border-2 border-purple-200 shrink-0 shadow-xs">
                                                {{ strtoupper(substr($magang->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $magang->nama_lengkap }}</h3>
                                            <p class="text-[11px] font-mono text-slate-500 font-semibold mt-0.5">NIM: {{ $magang->no_induk }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $magang->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($magang->status) }}
                                    </span>
                                </div>

                                <!-- Detail Info -->
                                <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400 text-[11px]">Instansi / Kampus:</span>
                                        <span class="font-semibold text-slate-800 text-right">{{ $magang->instansi_pendidikan ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400 text-[11px]">Jurusan:</span>
                                        <span class="font-semibold text-slate-800 text-right">{{ $magang->jurusan ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400 text-[11px]">Divisi:</span>
                                        <span class="font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded text-[11px]">{{ $magang->divisi->nama_divisi ?? 'Belum ada divisi' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400 text-[11px]">Periode:</span>
                                        <span class="font-mono text-[11px] font-medium text-slate-700">
                                            {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d/m/y') : '-' }} &ndash; {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d/m/y') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Aksi Peserta -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                @if($magang->penilaian)
                                    <a href="{{ route('pembimbing.penilaian.show', $magang->penilaian->id) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-200 transition">
                                        <span>&check; Nilai: {{ number_format($magang->penilaian->nilai_akhir, 1) }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('pembimbing.penilaian.create', ['magang_id' => $magang->id]) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-xl border border-amber-200 transition">
                                        <span>&plus; Input Nilai</span>
                                    </a>
                                @endif

                                <a href="{{ route('pembimbing.aktivitas.index') }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition">
                                    <span>Aktivitas &rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- SECTION 2: PANTAU KEHADIRAN HARI INI (Live Attendance Monitor Sesuai PRD tampilan.md) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Pantau Kehadiran Binaan Hari Ini</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Monitoring presensi masuk &amp; pulang peserta binaan per hari ini, {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}.
                </p>
            </div>
            <div class="text-xs font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/80">
                Total Hadir: <span class="font-bold text-emerald-600">{{ $presensiHariIni->count() }}</span> dari {{ $stats['total_magang'] }} peserta
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Peserta Magang</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Status Kehadiran</th>
                        <th class="px-6 py-4">Tipe Kerja</th>
                        <th class="px-6 py-4 text-center">Bukti Selfie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($presensiHariIni as $presensi)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Peserta Cell with Avatar -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($presensi->pengguna->magang && $presensi->pengguna->magang->foto)
                                        <img src="{{ asset('storage/' . $presensi->pengguna->magang->foto) }}" alt="{{ $presensi->pengguna->magang->nama_lengkap }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($presensi->pengguna->magang->nama_lengkap ?? $presensi->pengguna->username, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">
                                            {{ $presensi->pengguna->magang->nama_lengkap ?? $presensi->pengguna->username }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $presensi->pengguna->magang->divisi->nama_divisi ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Jam Masuk -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-slate-800">
                                @if($presensi->jam_masuk)
                                    <span class="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                        {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i:s') }} WITA
                                    </span>
                                @else
                                    <span class="text-slate-400 font-normal">&mdash;</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-slate-800">
                                @if($presensi->jam_keluar)
                                    <span class="text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                        {{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i:s') }} WITA
                                    </span>
                                @else
                                    <span class="text-slate-400 font-normal">Belum absen pulang</span>
                                @endif
                            </td>

                            <!-- Status Kehadiran -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($presensi->status === 'hadir')
                                    @if($presensi->keterangan && str_contains(strtolower($presensi->keterangan), 'terlambat'))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            &excl; Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            &check; Tepat Waktu
                                        </span>
                                    @endif

                                @elseif(in_array($presensi->status, ['izin', 'sakit', 'cuti']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($presensi->status) }}
                                    </span>

                                @elseif($presensi->status === 'alpa')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Alpa
                                    </span>

                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        {{ ucfirst($presensi->status ?? 'Alpa') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Tipe Kerja -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($presensi->is_wfh)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        🏠 WFH
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        🏢 WFO (Kantor)
                                    </span>
                                @endif
                            </td>

                            <!-- Bukti Selfie -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($presensi->foto_masuk)
                                    <button
                                        type="button"
                                        onclick="showPhotoPreview('{{ asset('storage/' . $presensi->foto_masuk) }}', '{{ $presensi->pengguna->magang->nama_lengkap ?? 'Peserta' }} - Masuk')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-semibold transition cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                                        <span>Lihat Foto</span>
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px]">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Belum ada presensi peserta binaan yang tercatat hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Preview Foto Selfie -->
<div id="modal-photo" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="photo-modal-title" class="text-sm font-bold text-slate-900">Bukti Foto Presensi</h3>
            <button type="button" onclick="closePhotoPreview()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="aspect-4/3 bg-slate-100 rounded-2xl overflow-hidden flex items-center justify-center">
            <img id="photo-modal-img" src="" alt="Bukti Foto" class="w-full h-full object-cover">
        </div>
        <div class="text-right">
            <button type="button" onclick="closePhotoPreview()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showPhotoPreview(url, title) {
    document.getElementById('photo-modal-img').src = url;
    document.getElementById('photo-modal-title').textContent = title;
    const modal = document.getElementById('modal-photo');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePhotoPreview() {
    const modal = document.getElementById('modal-photo');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
