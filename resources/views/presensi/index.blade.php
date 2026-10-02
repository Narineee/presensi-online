@extends('layouts.user')

@section('title', 'Presensi Harian')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #leaflet-map {
        z-index: 1 !important;
    }
    .leaflet-pane {
        z-index: 10 !important;
    }
    .leaflet-top,
    .leaflet-bottom {
        z-index: 20 !important;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            <p class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                Periksa kembali isian presensi Anda:
            </p>
            <ul class="list-disc list-inside text-xs space-y-0.5 ml-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Banner Profil Belum Lengkap (Sesuai PRD: kalau masih kosong diwajibkan isi terlebih dahulu) -->
    @php
        $isMagangProfileIncomplete = false;
        if ($user->role === 'magang' && $user->magang) {
            $isMagangProfileIncomplete = empty($user->magang->instansi_pendidikan) || empty($user->magang->jurusan) || empty($user->magang->no_hp) || empty($user->magang->foto);
        }
    @endphp

    @if($isMagangProfileIncomplete)
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-amber-200/70 text-amber-800 flex items-center justify-center font-bold shrink-0 text-base">
                    ⚠️
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Biodata Profil Belum Lengkap</h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Harap lengkapi asal instansi/kampus, jurusan, nomor kontak WhatsApp, dan pasfoto resmi Anda terlebih dahulu.
                    </p>
                </div>
            </div>
            <a href="{{ route('profil.edit') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shrink-0 shadow-xs transition">
                <span>Lengkapi Profil</span>
                <span>&rarr;</span>
            </a>
        </div>
    @endif

    <!-- Profile & Live Digital Clock Header Card -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-500/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Presensi & Monitoring Harian</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $user->magang->nama_lengkap ?? $user->username }}!
                </h1>
                <p class="text-blue-100 text-sm mt-1 max-w-xl leading-relaxed">
                    Catat kehadiran Anda hari ini dengan mengaktifkan GPS dan mengambil foto selfie sebagai bukti absensi yang sah.
                </p>

                <!-- Informasi Waktu Kerja & Batasan Presensi WITA -->
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs text-blue-50">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            Masuk: <strong>07.30 - 08.00 WITA</strong> (Lewat 08.00 tercatat terlambat) &bull; Pulang: <strong>16.00 - 18.00 WITA</strong> (Maks. 18.00 WITA)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Live Digital Clock Widget -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 text-center shrink-0 min-w-[220px]">
                <div class="text-xs uppercase tracking-widest text-blue-200 font-bold" id="current-date">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                </div>
                <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white font-mono mt-1" id="live-clock">
                    --:--:-- WITA
                </div>
                <div class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-300 bg-emerald-950/40 px-2.5 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Waktu Server Terkoneksi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Metric Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kehadiran Bulan Ini</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_hadir'] }} Hari</p>
                <span class="text-[11px] text-emerald-600 font-medium">Bulan {{ \Carbon\Carbon::now()->isoFormat('MMMM Y') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bekerja di Kantor (Onsite)</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['total_onsite'] }} Hari</p>
                <span class="text-[11px] text-slate-400">Presensi di Kantor</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bekerja Dari Rumah (WFH)</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['total_wfh'] }} Hari</p>
                <span class="text-[11px] text-slate-400">Presensi Fleksibel</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            </div>
        </div>
    </div>

    <!-- Main Attendance Action Card (Masuk / Pulang) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        
        @if(!$todayPresensi)
            <!-- STATE 1: BELUM ABSEN MASUK -->
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200">
                            Langkah 1: Presensi Masuk
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Formulir Presensi Masuk Hari Ini</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih mode kerja Anda, pastikan GPS aktif, dan ambil foto selfie.</p>
                    </div>
                </div>

                <!-- Banner Informasi Waktu Masuk -->
                @if($timeStatus['is_before_masuk'] ?? false)
                    <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 shadow-xs flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center font-bold shrink-0 text-lg">
                            ⏰
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Presensi Masuk Belum Dibuka</h4>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Presensi masuk baru dapat dilakukan mulai pukul <strong>07.30 WITA</strong>. Harap menunggu hingga jam presensi dibuka.
                            </p>
                        </div>
                    </div>
                @elseif($timeStatus['is_after_tutup'] ?? false)
                    <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 shadow-xs flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-200/80 text-rose-800 flex items-center justify-center font-bold shrink-0 text-lg">
                            ⛔
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-rose-950 uppercase tracking-wider">Waktu Presensi Hari Ini Telah Berakhir</h4>
                            <p class="text-xs text-rose-800 mt-0.5">
                                Batas waktu presensi hari ini telah ditutup pada pukul <strong>18.00 WITA</strong>. Anda tidak dapat melakukan presensi lagi hari ini.
                            </p>
                        </div>
                    </div>
                @elseif($timeStatus['is_late_masuk'] ?? false)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center font-bold shrink-0">
                            ⚠️
                        </div>
                        <p class="text-xs text-amber-800">
                            Saat ini telah melewati batas jam masuk (<strong>08.00 WITA</strong>). Presensi yang Anda lakukan akan otomatis tercatat <strong>Terlambat</strong>.
                        </p>
                    </div>
                @endif

                <form action="{{ route('presensi.masuk') }}" method="POST" id="form-presensi-masuk" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="foto_masuk" id="input_foto_masuk" value="">
                    <input type="hidden" name="lokasi_masuk" id="input_lokasi_masuk" value="">
                    <input type="hidden" name="face_descriptor" id="input_face_descriptor" value="">

                    <!-- Pilihan Mode Kerja -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Mode Kerja Hari Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="mode_kerja" value="onsite" class="sr-only" checked>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                                        🏢
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Onsite (Di Kantor)</div>
                                        <div class="text-xs text-slate-500">Bekerja di area kantor</div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="mode_kerja" value="wfh" class="sr-only">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg">
                                        🏠
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">WFH (Work From Home)</div>
                                        <div class="text-xs text-slate-500">Bekerja dari rumah</div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-200 hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                                <input type="radio" name="mode_kerja" value="tugas_luar" class="sr-only">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg">
                                        🚗
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Tugas Luar (TL)</div>
                                        <div class="text-xs text-slate-500">Bertugas di luar kantor</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Form Rincian Khusus Tugas Luar (Skenario 1) -->
                    <div id="container-tugas-luar" class="hidden p-5 rounded-2xl bg-indigo-50/60 border border-indigo-200 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-indigo-100">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">📋</span>
                            <div>
                                <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Formulir Rincian Tugas Luar (TL)</h4>
                                <p class="text-[11px] text-indigo-700">Lengkapi data penugasan luar kantor. Validasi radius kantor otomatis dinonaktifkan.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="input_tujuan_tl" class="block text-xs font-bold text-slate-700 mb-1">
                                    Tujuan / Lokasi Penugasan <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="tujuan"
                                    id="input_tujuan_tl"
                                    placeholder="Contoh: Kantor Bappeda Prov. Kalsel / Lapangan"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                >
                            </div>

                            <div>
                                <label for="bukti_tugas_luar" class="block text-xs font-bold text-slate-700 mb-1">
                                    Bukti Penugasan (Surat Tugas / Dokumen / Foto)
                                </label>
                                <input
                                    type="file"
                                    name="bukti_tugas_luar"
                                    id="bukti_tugas_luar"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                >
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Format: PDF, JPG, JPEG, PNG (Maks. 5MB)</span>
                            </div>
                        </div>

                        <div>
                            <label for="input_keperluan_tl" class="block text-xs font-bold text-slate-700 mb-1">
                                Keperluan / Uraian Kegiatan <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                name="keperluan"
                                id="input_keperluan_tl"
                                rows="2"
                                placeholder="Contoh: Menghadiri rapat koordinasi teknis dan pendampingan implementasi aplikasi."
                                class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="input_waktu_mulai_tl" class="block text-xs font-bold text-slate-700 mb-1">
                                    Waktu Mulai Tugas Luar <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="time"
                                    name="waktu_mulai"
                                    id="input_waktu_mulai_tl"
                                    value="{{ date('H:i') }}"
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                >
                            </div>

                            <div>
                                <label for="input_waktu_selesai_tl" class="block text-xs font-bold text-slate-700 mb-1">
                                    Perkiraan Waktu Selesai (Opsional)
                                </label>
                                <input
                                    type="time"
                                    name="waktu_selesai"
                                    id="input_waktu_selesai_tl"
                                    placeholder="16:00"
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Webcam Selfie & GPS Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Kamera Selfie & Realtime Liveness Detection -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Foto Wajah (Liveness) <span class="text-rose-500">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Realtime AI
                                </span>
                            </div>
                            
                            <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border-2 border-slate-200 shadow-inner">
                                <!-- Video Stream (Mirrored) -->
                                <video id="webcam-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                                
                                <!-- Canvas Hidden (Untuk Capture) -->
                                <canvas id="webcam-canvas" class="hidden"></canvas>
                                
                                <!-- Face Oval Guide SVG Overlay -->
                                <div id="face-guide-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center transition-all duration-300">
                                    <svg class="w-48 h-60 sm:w-56 sm:h-68" viewBox="0 0 200 260" fill="none">
                                        <ellipse id="guide-oval" cx="100" cy="130" rx="72" ry="100" stroke="#38bdf8" stroke-width="3" stroke-dasharray="8 6" class="transition-colors duration-300" />
                                    </svg>
                                </div>

                                <!-- Challenge Instruction Banner (Top Overlay) -->
                                <div id="challenge-banner" class="absolute top-2.5 inset-x-2.5 pointer-events-none transition-all duration-300">
                                    <div class="bg-slate-950/85 backdrop-blur-md text-white px-3.5 py-2 rounded-xl border border-white/10 shadow-lg flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span id="challenge-icon" class="text-xl shrink-0 transition-transform duration-300">👤</span>
                                            <div class="min-w-0">
                                                <div id="challenge-instruction" class="text-xs sm:text-sm font-bold text-white truncate">
                                                    Menyiapkan Liveness Detection...
                                                </div>
                                                <div id="challenge-subtext" class="text-[10px] sm:text-[11px] text-slate-300 truncate">
                                                    Posisikan wajah Anda di dalam bingkai oval
                                                </div>
                                            </div>
                                        </div>
                                        <div id="challenge-step-badge" class="shrink-0 px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[10px] font-bold">
                                            Langkah 1/2
                                        </div>
                                    </div>
                                </div>

                                <!-- Flash Effect Overlay on Auto-Capture -->
                                <div id="camera-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-200"></div>

                                <!-- Hasil Capture Preview -->
                                <img id="captured-preview" class="w-full h-full object-cover hidden" alt="Selfie Preview">

                                <!-- Placeholder Ketika Kamera Belum Aktif -->
                                <div id="camera-placeholder" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-4 text-slate-300 text-center z-10">
                                    <div class="w-9 h-9 border-3 border-blue-500 border-t-transparent rounded-full animate-spin mb-2.5"></div>
                                    <span class="text-xs font-semibold text-slate-200">Menyiapkan Kamera & AI Liveness...</span>
                                    <span class="text-[10px] text-slate-400 mt-1">Pastikan izin kamera diizinkan di browser</span>
                                </div>
                            </div>

                            <!-- Status Box Liveness & Tombol Aksi -->
                            <div class="space-y-2">
                                <div id="liveness-status-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center justify-between gap-2 transition-all">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span id="liveness-status-dot" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                        <span id="liveness-status-text" class="text-slate-600 font-medium truncate">Menunggu verifikasi liveness wajah...</span>
                                    </div>
                                    <span id="liveness-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0 uppercase tracking-wider">
                                        Belum Terverifikasi
                                    </span>
                                </div>

                                <button type="button" id="btn-retake" class="hidden w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    <span>Ulangi Verifikasi Wajah</span>
                                </button>
                            </div>
                        </div>

                        <!-- Deteksi Lokasi GPS & Peta Interaktif -->
                        <div class="space-y-3 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Titik Lokasi GPS <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                                        Radius Kantor: {{ $officeLocation['radius'] ?? 40 }}m
                                    </span>
                                </div>

                                <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs text-slate-500 font-medium">Status Geolokasi:</div>
                                            <div id="gps-status" class="text-xs font-bold text-amber-600 flex items-center gap-1.5 mt-0.5">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                Mendeteksi posisi GPS Anda...
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Interactive Leaflet Map Container -->
                                    <div class="relative">
                                        <div id="leaflet-map" class="w-full h-52 sm:h-56 rounded-xl border border-slate-300 overflow-hidden shadow-inner bg-slate-100"></div>
                                        <div class="absolute bottom-2 left-2 z-20 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-md text-[10px] text-slate-700 font-medium border border-slate-200 shadow-xs flex items-center gap-2">
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Kantor ({{ $officeLocation['radius'] ?? 40 }}m)</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span> Posisi Anda</span>
                                        </div>
                                    </div>

                                    <!-- Status Radius & Info Jarak Realtime -->
                                    <div id="radius-indicator-box" class="p-3 rounded-xl border border-slate-200 bg-white text-xs space-y-1.5 shadow-xs transition-colors">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500">Target Lokasi:</span>
                                            <span class="font-bold text-slate-800">{{ $officeLocation['nama'] ?? 'Kantor Utama' }} (Radius {{ $officeLocation['radius'] ?? 40 }}m)</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-500">Jarak Anda ke Kantor:</span>
                                            <span id="gps-distance" class="font-mono font-bold text-slate-800">Menghitung...</span>
                                        </div>
                                        <div id="radius-badge" class="pt-1.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                            <span>Menunggu sinyal GPS aktif...</span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/80 text-xs text-slate-600 space-y-1.5">
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-400">Koordinat Anda:</span>
                                            <span id="gps-coords" class="font-mono font-bold text-slate-800">-</span>
                                        </div>

                                        <div id="gps-map-container" class="pt-1 flex items-center justify-between text-xs">
                                            <a id="gps-map-link" href="#" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-semibold">
                                                Buka Posisi di Google Maps &rarr;
                                            </a>
                                            <a href="https://www.google.com/maps?q={{ $officeLocation['lat'] }},{{ $officeLocation['lng'] }}" target="_blank" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 underline font-medium">
                                                Titik Kantor &nearr;
                                            </a>
                                        </div>
                                    </div>

                                    <button type="button" id="btn-refresh-gps" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        <span>Segarkan Titik Lokasi GPS</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Keterangan Tambahan (Opsional) -->
                            <div>
                                <label for="keterangan" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Catatan / Keterangan (Opsional)
                                </label>
                                <input
                                    type="text"
                                    name="keterangan"
                                    id="keterangan"
                                    placeholder="Contoh: Tugas luar kantor, kendala jaringan, dll."
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit Presensi Masuk -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                        @if($timeStatus['is_before_masuk'] ?? false)
                            <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Dibuka Pukul 07.30 WITA</span>
                            </button>
                        @elseif($timeStatus['is_after_tutup'] ?? false)
                            <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <span>Presensi Hari Ini Ditutup</span>
                            </button>
                        @else
                            <button type="submit" id="btn-submit-masuk" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Kirim Presensi Masuk Sekarang</span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

        @elseif($todayPresensi && !$todayPresensi->jam_keluar)
            <!-- STATE 2: SUDAH MASUK, BELUM PULANG -->
            <div class="space-y-6">
                <!-- Status Box Masuk Hari Ini -->
                <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if($todayPresensi->foto_masuk_url)
                            <img src="{{ $todayPresensi->foto_masuk_url }}" alt="Foto Masuk" class="w-16 h-16 rounded-2xl object-cover border-2 border-emerald-300 shadow-sm shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-emerald-200 text-emerald-800 font-bold flex items-center justify-center text-xl shrink-0">
                                &check;
                            </div>
                        @endif
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 uppercase">
                                Sudah Masuk
                            </span>
                            <h3 class="text-lg font-bold text-emerald-950 mt-0.5">
                                Presensi Masuk: <span class="font-mono text-emerald-700">{{ substr($todayPresensi->jam_masuk, 0, 5) }} WITA</span>
                            </h3>
                            <p class="text-xs text-emerald-700 mt-0.5">
                                Mode Kerja: <strong class="capitalize">{{ $todayPresensi->mode_kerja === 'tugas_luar' ? 'Tugas Luar (TL)' : $todayPresensi->mode_kerja }}</strong> &bull; Lokasi: {{ $todayPresensi->lokasi_masuk ?? '-' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        @if(!$todayTugasLuar || $todayTugasLuar->isDitolak())
                            <!-- Tombol Ajukan Tugas Luar (Skenario 2) -->
                            <button
                                type="button"
                                onclick="openModalAjukanTugasLuar()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                            >
                                <span>🚗</span>
                                <span>+ Ajukan Tugas Luar</span>
                            </button>
                        @endif
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white text-emerald-800 border border-emerald-200/80 text-center">
                            Sedang Aktif Bekerja
                        </span>
                    </div>
                </div>

                <!-- Banner Informasi Status Tugas Luar Hari Ini (Jika Ada) -->
                @if($todayTugasLuar)
                    @if($todayTugasLuar->isMenunggu())
                        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 shadow-xs flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-lg shrink-0">
                                    ⏳
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Pengajuan Tugas Luar Menunggu Verifikasi</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900">Menunggu Pembimbing</span>
                                    </div>
                                    <p class="text-xs text-amber-800 mt-1">
                                        Tujuan: <strong>{{ $todayTugasLuar->tujuan }}</strong> &bull; Waktu: <strong>{{ substr($todayTugasLuar->waktu_mulai, 0, 5) }} s/d {{ $todayTugasLuar->waktu_selesai ? substr($todayTugasLuar->waktu_selesai, 0, 5) : 'Selesai' }} WITA</strong>.
                                        Jam masuk awal Anda tetap tersimpan ({{ substr($todayPresensi->jam_masuk, 0, 5) }} WITA) dan tidak berubah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($todayTugasLuar->isDisetujui())
                        <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 shadow-xs flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                                    🚗
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Tugas Luar (TL) Disetujui Pembimbing</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">&check; Hadir — Tugas Luar</span>
                                    </div>
                                    <p class="text-xs text-indigo-800 mt-1">
                                        Tujuan: <strong>{{ $todayTugasLuar->tujuan }}</strong> &bull; Waktu: <strong>{{ substr($todayTugasLuar->waktu_mulai, 0, 5) }} s/d {{ $todayTugasLuar->waktu_selesai ? substr($todayTugasLuar->waktu_selesai, 0, 5) : 'Selesai' }} WITA</strong> &bull; Verifikator: <strong>{{ $todayTugasLuar->nama_validator }}</strong>.
                                        Batasan radius kantor saat presensi kepulangan telah otomatis dinonaktifkan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($todayTugasLuar->isDitolak())
                        <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 shadow-xs flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-200 text-rose-800 flex items-center justify-center font-bold text-lg shrink-0">
                                    ⛔
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-rose-950 uppercase tracking-wider">Pengajuan Tugas Luar Ditolak</h4>
                                    <p class="text-xs text-rose-800 mt-1">
                                        Alasan: {{ $todayTugasLuar->catatan_pembimbing ?: 'Tidak memenuhi syarat penugasan.' }} (Oleh: {{ $todayTugasLuar->nama_validator }}).
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Form Absen Pulang -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                            Langkah 2: Presensi Pulang
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Formulir Presensi Pulang</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Selesaikan jam kerja hari ini dengan mengambil foto selfie kepulangan dan mendeteksi lokasi GPS.</p>
                    </div>

                    @if(!($hasAktivitasToday ?? false))
                        <!-- TAMPILAN TERKUNCI: BELUM MENGISI AKTIVITAS HARIAN -->
                        <div class="p-6 sm:p-8 rounded-3xl bg-amber-50/70 border-2 border-dashed border-amber-300 text-center space-y-4">
                            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 mx-auto flex items-center justify-center shadow-inner">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>

                            <div class="max-w-md mx-auto">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Presensi Pulang Terkunci
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 mt-2">Wajib Mengisi Aktivitas Harian</h3>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Anda belum mengisi aktivitas harian untuk hari ini. Sesuai ketentuan, Anda wajib mencatat rincian tugas atau progres pekerjaan hari ini terlebih dahulu agar formulir presensi kepulangan dapat terbuka.
                                </p>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                                <a href="{{ route('aktivitas.create', ['redirect_to' => 'presensi']) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-600/20 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    <span>Catat Aktivitas Harian Sekarang</span>
                                </a>
                                <a href="{{ route('presensi.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <span>Segarkan Status</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- TAMPILAN TERBUKA: AKTIVITAS SUDAH DIISI -->
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-xs">
                                    <span class="font-bold text-emerald-950">Aktivitas harian hari ini telah diisi ({{ $countAktivitasToday ?? 1 }} catatan).</span>
                                    <span class="text-emerald-700 block">Syarat presensi pulang telah terpenuhi. Silakan lengkapi presensi pulang Anda di bawah ini.</span>
                                </div>
                            </div>
                            <a href="{{ route('aktivitas.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline shrink-0">
                                Lihat Aktivitas &rarr;
                            </a>
                        </div>

                        <!-- Banner Informasi Waktu Pulang -->
                        @if($timeStatus['is_before_pulang'] ?? false)
                            <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 shadow-xs flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center font-bold shrink-0 text-lg">
                                    ⏰
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Presensi Pulang Belum Dibuka</h4>
                                    <p class="text-xs text-amber-800 mt-0.5">
                                        Presensi pulang dibuka mulai pukul <strong>16.00 WITA</strong> sampai <strong>18.00 WITA</strong>. Saat ini belum memasuki jam kepulangan.
                                    </p>
                                </div>
                            </div>
                        @elseif($timeStatus['is_after_tutup'] ?? false)
                            <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 shadow-xs flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-200/80 text-rose-800 flex items-center justify-center font-bold shrink-0 text-lg">
                                    ⛔
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-rose-950 uppercase tracking-wider">Batas Waktu Presensi Pulang Telah Berakhir</h4>
                                    <p class="text-xs text-rose-800 mt-0.5">
                                        Batas waktu presensi pulang hari ini telah ditutup pada pukul <strong>18.00 WITA</strong>. Anda tidak dapat melakukan presensi kepulangan lagi.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('presensi.keluar') }}" method="POST" id="form-presensi-keluar" class="space-y-6">
                            @csrf
                            <input type="hidden" name="foto_keluar" id="input_foto_keluar" value="">
                            <input type="hidden" name="lokasi_keluar" id="input_lokasi_keluar" value="">
                            <input type="hidden" name="face_descriptor" id="input_face_descriptor" value="">
                            <input type="hidden" id="today-mode-kerja" value="{{ $todayPresensi->mode_kerja }}">
                            <input type="hidden" id="is-tugas-luar-today" value="{{ ($todayTugasLuar && ($todayTugasLuar->isMenunggu() || $todayTugasLuar->isDisetujui())) ? '1' : '0' }}">

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Kamera Pulang & Realtime Liveness Detection -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                            Foto Wajah Pulang (Liveness) <span class="text-rose-500">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border-2 border-slate-200 shadow-inner">
                                        <!-- Video Stream (Mirrored) -->
                                        <video id="webcam-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                                        
                                        <!-- Canvas Hidden (Untuk Capture) -->
                                        <canvas id="webcam-canvas" class="hidden"></canvas>
                                        
                                        <!-- Face Oval Guide SVG Overlay -->
                                        <div id="face-guide-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center transition-all duration-300">
                                            <svg class="w-48 h-60 sm:w-56 sm:h-68" viewBox="0 0 200 260" fill="none">
                                                <ellipse id="guide-oval" cx="100" cy="130" rx="72" ry="100" stroke="#38bdf8" stroke-width="3" stroke-dasharray="8 6" class="transition-colors duration-300" />
                                            </svg>
                                        </div>

                                        <!-- Challenge Instruction Banner (Top Overlay) -->
                                        <div id="challenge-banner" class="absolute top-2.5 inset-x-2.5 pointer-events-none transition-all duration-300">
                                            <div class="bg-slate-950/85 backdrop-blur-md text-white px-3.5 py-2 rounded-xl border border-white/10 shadow-lg flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span id="challenge-icon" class="text-xl shrink-0 transition-transform duration-300">👤</span>
                                                    <div class="min-w-0">
                                                        <div id="challenge-instruction" class="text-xs sm:text-sm font-bold text-white truncate">
                                                            Ikuti Instruksi di layar
                                                        </div>
                                                        <div id="challenge-subtext" class="text-[10px] sm:text-[11px] text-slate-300 truncate">
                                                            Posisikan wajah Anda di dalam bingkai oval
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="challenge-step-badge" class="shrink-0 px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[10px] font-bold">
                                                    Langkah 1/2
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Flash Effect Overlay on Auto-Capture -->
                                        <div id="camera-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-200"></div>

                                        <!-- Hasil Capture Preview -->
                                        <img id="captured-preview" class="w-full h-full object-cover hidden" alt="Selfie Preview">

                                        <!-- Placeholder Ketika Kamera Belum Aktif -->
                                        <div id="camera-placeholder" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-4 text-slate-300 text-center z-10">
                                            <div class="w-9 h-9 border-3 border-amber-500 border-t-transparent rounded-full animate-spin mb-2.5"></div>
                                            <span class="text-xs font-semibold text-slate-200">Membuka kamera...</span>
                                            <span class="text-[10px] text-slate-400 mt-1">Pastikan izin kamera diizinkan di browser</span>
                                        </div>
                                    </div>

                                    <!-- Status Box Liveness & Tombol Aksi -->
                                    <div class="space-y-2">
                                        <div id="liveness-status-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center justify-between gap-2 transition-all">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span id="liveness-status-dot" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                                <span id="liveness-status-text" class="text-slate-600 font-medium truncate">Ikuti instruksi di layar, foto akan diambil otomatis</span>
                                            </div>
                                            <span id="liveness-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0 uppercase tracking-wider">
                                                Belum Terverifikasi
                                            </span>
                                        </div>

                                        <button type="button" id="btn-retake" class="hidden w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition cursor-pointer">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                            <span>Ambil Ulang</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- GPS Pulang & Peta Interaktif -->
                                <div class="space-y-3 flex flex-col justify-between">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                                Titik Lokasi GPS Kepulangan <span class="text-rose-500">*</span>
                                            </label>
                                            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                                Mode: {{ strtoupper($todayPresensi->mode_kerja ?? 'ONSITE') }}
                                            </span>
                                        </div>

                                        <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs text-slate-500 font-medium">Status Geolokasi:</div>
                                                    <div id="gps-status" class="text-xs font-bold text-amber-600 flex items-center gap-1.5 mt-0.5">
                                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                        Mendeteksi posisi GPS Anda...
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Interactive Leaflet Map Container -->
                                            <div class="relative">
                                                <div id="leaflet-map" class="w-full h-52 sm:h-56 rounded-xl border border-slate-300 overflow-hidden shadow-inner bg-slate-100"></div>
                                                <div class="absolute bottom-2 left-2 z-20 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-md text-[10px] text-slate-700 font-medium border border-slate-200 shadow-xs flex items-center gap-2">
                                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Kantor ({{ $officeLocation['radius'] ?? 40 }}m)</span>
                                                    <span class="text-slate-300">|</span>
                                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span> Posisi Anda</span>
                                                </div>
                                            </div>

                                            <!-- Status Radius & Info Jarak Realtime -->
                                            <div id="radius-indicator-box" class="p-3 rounded-xl border border-slate-200 bg-white text-xs space-y-1.5 shadow-xs transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-slate-500">Target Lokasi:</span>
                                                    <span class="font-bold text-slate-800">{{ $officeLocation['nama'] ?? 'Kantor Utama' }} (Radius {{ $officeLocation['radius'] ?? 40 }}m)</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-slate-500">Jarak Anda ke Kantor:</span>
                                                    <span id="gps-distance" class="font-mono font-bold text-slate-800">Menghitung...</span>
                                                </div>
                                                <div id="radius-badge" class="pt-1.5 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                    <span>Menunggu sinyal GPS aktif...</span>
                                                </div>
                                            </div>

                                             <div class="pt-2 border-t border-slate-200/80 text-xs text-slate-600 space-y-1.5">
                                                 <div class="flex justify-between items-center">
                                                    <span class="text-slate-400">Koordinat Anda:</span>
                                                    <span id="gps-coords" class="font-mono font-bold text-slate-800">-</span>
                                                </div>

                                                <div id="gps-map-container" class="pt-1 flex items-center justify-between text-xs">
                                                    <a id="gps-map-link" href="#" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-semibold">
                                                        Buka Posisi di Google Maps &rarr;
                                                    </a>
                                                    <a href="https://www.google.com/maps?q={{ $officeLocation['lat'] }},{{ $officeLocation['lng'] }}" target="_blank" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 underline font-medium">
                                                        Titik Kantor &nearr;
                                                    </a>
                                                </div>
                                            </div>

                                            <button type="button" id="btn-refresh-gps" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                                <span>Segarkan Titik Lokasi GPS</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="keterangan_keluar" class="block text-xs font-semibold text-slate-700 mb-1">
                                            Catatan Kepulangan (Opsional)
                                        </label>
                                        <input
                                            type="text"
                                            name="keterangan_keluar"
                                            id="keterangan_keluar"
                                            placeholder="Contoh: Pekerjaan hari ini selesai, dll."
                                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                                @if($timeStatus['is_before_pulang'] ?? false)
                                    <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Dibuka Pukul 16.00 WITA</span>
                                    </button>
                                @elseif($timeStatus['is_after_tutup'] ?? false)
                                    <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <span>Batas Presensi Pulang Berakhir</span>
                                    </button>
                                @else
                                    <button type="submit" id="btn-submit-keluar" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-lg shadow-amber-500/25 transition cursor-pointer flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                        <span>Kirim Presensi Pulang Sekarang</span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        @else
            <!-- STATE 3: SUDAH SELESAI LENGKAP HARI INI -->
            <div class="text-center py-6 space-y-5">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-inner">
                    &check;
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Presensi Lengkap Hari Ini
                    </span>
                    <h2 class="text-2xl font-extrabold text-slate-900 mt-2">Terima Kasih Atas Dedikasi Anda!</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Anda telah menyelesaikan presensi masuk dan kepulangan untuk hari ini ({{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}).
                    </p>
                </div>

                <!-- Detail Kartu Masuk & Keluar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mx-auto text-left">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                        @if($todayPresensi->foto_masuk_url)
                            <img src="{{ $todayPresensi->foto_masuk_url }}" alt="Foto Masuk" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Presensi Masuk</span>
                            <div class="text-sm font-bold text-slate-800 font-mono">{{ substr($todayPresensi->jam_masuk, 0, 5) }} WITA</div>
                            <div class="text-[11px] text-slate-500 capitalize">Mode: {{ $todayPresensi->mode_kerja }}</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                        @if($todayPresensi->foto_keluar_url)
                            <img src="{{ $todayPresensi->foto_keluar_url }}" alt="Foto Pulang" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Presensi Pulang</span>
                            <div class="text-sm font-bold text-slate-800 font-mono">{{ substr($todayPresensi->jam_keluar, 0, 5) }} WITA</div>
                            <div class="text-[11px] text-slate-500 truncate">{{ $todayPresensi->keterangan ?? 'Selesai' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Riwayat Presensi Pribadi -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Presensi Saya</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar rekaman kehadiran, jam masuk/pulang, dan bukti selfie Anda.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Bulan -->
                <form action="{{ route('presensi.index') }}" method="GET" class="flex items-center gap-2">
                    <input
                        type="month"
                        name="bulan"
                        value="{{ request('bulan', date('Y-m')) }}"
                        class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                    >
                    <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                        Filter
                    </button>
                </form>

                <!-- Tombol Cetak Rekap Presensi -->
                <a href="{{ route('presensi.cetak', ['bulan' => request('bulan', date('Y-m'))]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
                    <span>Cetak Rekap</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Mode Kerja</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Foto Selfie</th>
                        <th class="px-6 py-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($riwayat as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        🚗 Tugas Luar
                                    </span>
                                @elseif ($item->mode_kerja === 'onsite')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        🏢 Onsite
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        🏠 WFH
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WITA' : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->is_tugas_luar)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-indigo-100 text-indigo-800">
                                        Hadir — Tugas Luar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        {{ $item->status }}
                                    </span>
                                @endif
                                @if (str_contains($item->keterangan ?? '', 'Terlambat'))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-rose-100 text-rose-700 ml-1">
                                        Terlambat
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if ($item->foto_masuk_url)
                                        <a href="{{ $item->foto_masuk_url }}" target="_blank" title="Foto Masuk" class="group relative">
                                            <img src="{{ $item->foto_masuk_url }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 hover:scale-110 transition">
                                        </a>
                                    @endif
                                    @if ($item->foto_keluar_url)
                                        <a href="{{ $item->foto_keluar_url }}" target="_blank" title="Foto Pulang" class="group relative">
                                            <img src="{{ $item->foto_keluar_url }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 hover:scale-110 transition">
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 max-w-xs truncate">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                Belum ada rekaman presensi pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL PENGAJUAN TUGAS LUAR (SKENARIO 2: SETELAH PRESENSI ONSITE) -->
    <div id="modal-ajukan-tugas-luar" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8 transition-all animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg font-bold">
                        🚗
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Ajukan Tugas Luar (TL)</h3>
                        <p class="text-xs text-slate-500">Penugasan kedinasan di luar kantor instansi</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="closeModalAjukanTugasLuar()"
                    class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Info Banner -->
            <div class="mt-4 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 text-xs text-indigo-900 flex items-start gap-2.5">
                <span class="text-base shrink-0">💡</span>
                <p class="leading-relaxed">
                    Jam presensi masuk awal Anda tetap tersimpan dan tidak berubah. Pengajuan ini akan diteruskan ke Pembimbing untuk verifikasi.
                </p>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('presensi.tugas-luar') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="modal_tujuan_tl" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tujuan / Lokasi Penugasan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="tujuan"
                        id="modal_tujuan_tl"
                        required
                        placeholder="Contoh: Kantor Dinas Kominfo / Pengadilan Tinggi"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    >
                </div>

                <div>
                    <label for="modal_keperluan_tl" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keperluan / Uraian Tugas <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="keperluan"
                        id="modal_keperluan_tl"
                        rows="3"
                        required
                        placeholder="Uraikan agenda penugasan atau kegiatan dinas yang akan dilaksanakan..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="modal_waktu_mulai_tl" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Waktu Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="time"
                            name="waktu_mulai"
                            id="modal_waktu_mulai_tl"
                            required
                            value="{{ date('H:i') }}"
                            class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                    </div>

                    <div>
                        <label for="modal_waktu_selesai_tl" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Perkiraan Selesai (Opsional)
                        </label>
                        <input
                            type="time"
                            name="waktu_selesai"
                            id="modal_waktu_selesai_tl"
                            placeholder="16:00"
                            class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                    </div>
                </div>

                <div>
                    <label for="modal_bukti_tl" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Bukti Penugasan (Surat Tugas / Surat Undangan / Foto)
                    </label>
                    <input
                        type="file"
                        name="bukti_tugas_luar"
                        id="modal_bukti_tl"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    >
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: PDF, JPG, JPEG, PNG (Maksimal 5MB)</span>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        onclick="closeModalAjukanTugasLuar()"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer"
                    >
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script src="{{ asset('js/face-id.js') }}"></script>
<!-- MediaPipe FaceMesh & Camera Utils -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // 1. LIVE DIGITAL CLOCK
    // ==========================================
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('live-clock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds} WITA`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ==========================================
    // 2. WEBCAM & REALTIME LIVENESS DETECTION (AI FACE MESH)
    // ==========================================
    const video = document.getElementById('webcam-video');
    const canvas = document.getElementById('webcam-canvas');
    const preview = document.getElementById('captured-preview');
    const placeholder = document.getElementById('camera-placeholder');
    const btnRetake = document.getElementById('btn-retake');

    // Input target hidden foto
    const inputFotoMasuk = document.getElementById('input_foto_masuk');
    const inputFotoKeluar = document.getElementById('input_foto_keluar');
    const inputFaceDescriptor = document.getElementById('input_face_descriptor');
    if (typeof FaceID !== 'undefined') FaceID.load(); // muat model lebih awal

    // Pengaturan resize & kompres foto (Base64 JPEG)
    const FOTO_MAX_DIMENSI = 1000;
    const FOTO_QUALITY = 0.85;
    const FOTO_MAX_BYTES = 1.5 * 1024 * 1024;

    // Helper Web Audio API untuk feedback suara intuitif (beeps)
    function playTone(freq = 600, type = 'sine', duration = 0.12) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, ctx.currentTime);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + duration);
        } catch (e) {
            // AudioContext silent fallback jika browser memerlukan user gesture
        }
    }

    // Ambil frame video, mirror horizontally agar sesuai tampilan selfie, kompres JPEG Base64
    function ambilFotoTerkompres(videoEl, canvasEl) {
        const srcW = videoEl.videoWidth || 640;
        const srcH = videoEl.videoHeight || 480;

        const scale = Math.min(1, FOTO_MAX_DIMENSI / Math.max(srcW, srcH));
        canvasEl.width = Math.round(srcW * scale);
        canvasEl.height = Math.round(srcH * scale);

        const ctx = canvasEl.getContext('2d');
        ctx.save();
        ctx.translate(canvasEl.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
        ctx.restore();

        return canvasEl.toDataURL('image/jpeg', FOTO_QUALITY);
    }

    // Validasi format & ukuran foto sebelum submit
    function isValidSelfie(dataUrl) {
        if (!dataUrl || !/^data:image\/jpeg;base64,/.test(dataUrl)) return false;
        const base64 = dataUrl.split(',')[1] || '';
        const sizeBytes = Math.floor(base64.length * 3 / 4);
        return sizeBytes >= 5 * 1024 && sizeBytes <= FOTO_MAX_BYTES;
    }

    // Daftar preset tantangan liveness acak (Random Challenge Pool)
    const LIVENESS_CHALLENGES = [
        {
            id: 'left_then_front',
            title: 'Tengok Kiri lalu Depan',
            steps: [
                { type: 'turn_left', text: 'Tengokkan kepala ke KIRI', subtext: 'Perlahan tolehkan kepala ke arah kiri Anda', icon: '⬅️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Tahan posisi menghadap kamera dengan tenang', icon: '😊' }
            ]
        },
        {
            id: 'right_then_front',
            title: 'Tengok Kanan lalu Depan',
            steps: [
                { type: 'turn_right', text: 'Tengokkan kepala ke KANAN', subtext: 'Perlahan tolehkan kepala ke arah kanan Anda', icon: '➡️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Tahan posisi menghadap kamera dengan tenang', icon: '😊' }
            ]
        },
        {
            id: 'left_right_front',
            title: 'Tengok Kiri, Kanan, lalu Depan',
            steps: [
                { type: 'turn_left', text: 'Tengokkan kepala ke KIRI', subtext: 'Perlahan tolehkan kepala ke arah kiri Anda', icon: '⬅️' },
                { type: 'turn_right', text: 'Tengokkan kepala ke KANAN', subtext: 'Bagus! Sekarang tolehkan kepala ke arah kanan Anda', icon: '➡️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Sempurna! Tahan posisi menghadap kamera', icon: '😊' }
            ]
        },
        {
            id: 'right_left_front',
            title: 'Tengok Kanan, Kiri, lalu Depan',
            steps: [
                { type: 'turn_right', text: 'Tengokkan kepala ke KANAN', subtext: 'Perlahan tolehkan kepala ke arah kanan Anda', icon: '➡️' },
                { type: 'turn_left', text: 'Tengokkan kepala ke KIRI', subtext: 'Bagus! Sekarang tolehkan kepala ke arah kiri Anda', icon: '⬅️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Sempurna! Tahan posisi menghadap kamera', icon: '😊' }
            ]
        },
        {
            id: 'blink_then_front',
            title: 'Kedipkan Mata lalu Hadap Depan',
            steps: [
                { type: 'blink', text: 'Kedipkan kedua mata Anda', subtext: 'Pejamkan mata sejenak lalu buka kembali', icon: '😉' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Tahan posisi menghadap kamera dengan tenang', icon: '😊' }
            ]
        },
        {
            id: 'blink_left_front',
            title: 'Kedipkan Mata, Tengok Kiri, lalu Depan',
            steps: [
                { type: 'blink', text: 'Kedipkan kedua mata Anda', subtext: 'Pejamkan mata sejenak lalu buka kembali', icon: '😉' },
                { type: 'turn_left', text: 'Tengokkan kepala ke KIRI', subtext: 'Tolehkan kepala ke arah kiri Anda', icon: '⬅️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Sempurna! Tahan posisi menghadap kamera', icon: '😊' }
            ]
        },
        {
            id: 'blink_right_front',
            title: 'Kedipkan Mata, Tengok Kanan, lalu Depan',
            steps: [
                { type: 'blink', text: 'Kedipkan kedua mata Anda', subtext: 'Pejamkan mata sejenak lalu buka kembali', icon: '😉' },
                { type: 'turn_right', text: 'Tengokkan kepala ke KANAN', subtext: 'Tolehkan kepala ke arah kanan Anda', icon: '➡️' },
                { type: 'face_center', text: 'Kembali hadap lurus ke DEPAN', subtext: 'Sempurna! Tahan posisi menghadap kamera', icon: '😊' }
            ]
        }
    ];

    // State Mesin Verifikasi Liveness
    let isLivenessVerified = false;
    let isLivenessActive = false;
    let currentChallenge = null;
    let currentStepIndex = 0;
    let stepConsecutiveFrames = 0;
    let blinkState = 'waiting_close';
    let centerHoldStartTime = null;
    let faceMeshInstance = null;
    let cameraInstance = null;
    let isSendingFrame = false;

    // Perbarui Tampilan UI Tantangan
    function updateChallengeUI(customTitle = null, customSubtext = null, customIcon = null) {
        if (!currentChallenge) return;
        const iconEl = document.getElementById('challenge-icon');
        const titleEl = document.getElementById('challenge-instruction');
        const subtextEl = document.getElementById('challenge-subtext');
        const stepBadgeEl = document.getElementById('challenge-step-badge');

        if (customTitle) {
            if (titleEl) titleEl.textContent = customTitle;
            if (subtextEl) subtextEl.textContent = customSubtext || '';
            if (iconEl && customIcon) iconEl.textContent = customIcon;
            return;
        }

        const step = currentChallenge.steps[currentStepIndex];
        if (step) {
            if (titleEl) titleEl.textContent = step.text;
            if (subtextEl) subtextEl.textContent = step.subtext;
            if (iconEl) iconEl.textContent = step.icon;
            if (stepBadgeEl) {
                stepBadgeEl.textContent = `Langkah ${currentStepIndex + 1}/${currentChallenge.steps.length}`;
            }
        }
    }

    // Mulai Sesi Verifikasi Liveness Baru (dengan tantangan acak)
    function startNewLivenessSession() {
        isLivenessVerified = false;
        isLivenessActive = true;

        // Pilih tantangan acak dari preset
        const randIdx = Math.floor(Math.random() * LIVENESS_CHALLENGES.length);
        currentChallenge = LIVENESS_CHALLENGES[randIdx];
        currentStepIndex = 0;
        stepConsecutiveFrames = 0;
        blinkState = 'waiting_close';
        centerHoldStartTime = null;

        // Kosongkan nilai foto sebelumnya
        if (inputFotoMasuk) inputFotoMasuk.value = '';
        if (inputFotoKeluar) inputFotoKeluar.value = '';
        if (inputFaceDescriptor) inputFaceDescriptor.value = '';

        // Tampilkan stream video & sembunyikan preview
        if (video) video.classList.remove('hidden');
        if (preview) preview.classList.add('hidden');
        if (btnRetake) btnRetake.classList.add('hidden');

        const guideOval = document.getElementById('guide-oval');
        if (guideOval) guideOval.setAttribute('stroke', '#38bdf8'); // Biru netral

        const guideOverlay = document.getElementById('face-guide-overlay');
        if (guideOverlay) guideOverlay.classList.remove('hidden');

        const banner = document.getElementById('challenge-banner');
        if (banner) banner.classList.remove('hidden');

        // Update status box UI
        const statusBox = document.getElementById('liveness-status-box');
        if (statusBox) {
            statusBox.className = 'p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center justify-between gap-2 transition-all';
        }
        const statusDot = document.getElementById('liveness-status-dot');
        if (statusDot) {
            statusDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0';
        }
        const statusText = document.getElementById('liveness-status-text');
        if (statusText) {
            statusText.textContent = 'Ikuti instruksi di layar, foto akan diambil otomatis';
        }
        const statusBadge = document.getElementById('liveness-badge');
        if (statusBadge) {
            statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0 uppercase tracking-wider';
            statusBadge.textContent = 'Belum Terverifikasi';
        }

        updateChallengeUI();
    }

    // Auto-Capture Setelah Verifikasi Liveness Berhasil & Verifikasi Kecocokan Wajah ke Server
    async function completeLivenessAndCapture() {
        isLivenessActive = false; // hentikan pemrosesan frame liveness

        const statusBox = document.getElementById('liveness-status-box');
        const statusDot = document.getElementById('liveness-status-dot');
        const statusText = document.getElementById('liveness-status-text');
        const statusBadge = document.getElementById('liveness-badge');

        if (statusBox) {
            statusBox.className = 'p-3 rounded-xl border border-blue-200 bg-blue-50 text-xs flex items-center justify-between gap-2 transition-all';
        }
        if (statusDot) {
            statusDot.className = 'w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping shrink-0';
        }
        if (statusBadge) {
            statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 shrink-0 uppercase tracking-wider animate-pulse';
            statusBadge.textContent = 'Memeriksa Wajah...';
        }
        if (statusText) statusText.textContent = 'Mencocokkan wajah Anda dengan data pendaftaran di database...';

        // Ambil foto dari frame video saat ini
        const dataUrl = ambilFotoTerkompres(video, canvas);

        // Hitung descriptor dari foto yang sama
        let descriptor;
        try {
            if (typeof FaceID === 'undefined') {
                throw new Error('Modul pengenal wajah gagal dimuat. Segarkan halaman.');
            }
            descriptor = await FaceID.descriptorFrom(canvas);
        } catch (err) {
            startNewLivenessSession();
            if (statusText) statusText.textContent = err.message + ' Ikuti instruksi sekali lagi.';
            return;
        }

        // Efek kilat layar
        const flashEl = document.getElementById('camera-flash');
        if (flashEl) {
            flashEl.classList.remove('opacity-0');
            flashEl.classList.add('opacity-90');
            setTimeout(() => {
                flashEl.classList.remove('opacity-90');
                flashEl.classList.add('opacity-0');
            }, 250);
        }

        // Kirim AJAX ke server untuk mencocokkan wajah secara instan
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value;

            const response = await fetch('{{ route('presensi.verifikasi-wajah') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    face_descriptor: JSON.stringify(descriptor)
                })
            });

            const result = await response.json();

            if (response.ok && result.match) {
                // Wajah COCOK!
                isLivenessVerified = true;
                if (inputFaceDescriptor) inputFaceDescriptor.value = JSON.stringify(descriptor);
                if (inputFotoMasuk) inputFotoMasuk.value = dataUrl;
                if (inputFotoKeluar) inputFotoKeluar.value = dataUrl;

                playTone(1050, 'triangle', 0.25);

                // Tampilkan hasil foto, sembunyikan video dan panduan
                if (preview) {
                    preview.src = dataUrl;
                    preview.classList.remove('hidden');
                }
                if (video) video.classList.add('hidden');

                const guideOverlay = document.getElementById('face-guide-overlay');
                if (guideOverlay) guideOverlay.classList.add('hidden');
                const banner = document.getElementById('challenge-banner');
                if (banner) banner.classList.add('hidden');

                if (statusBox) {
                    statusBox.className = 'p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-xs flex items-center justify-between gap-2 transition-all';
                }
                if (statusDot) statusDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0';
                if (statusText) statusText.textContent = `Wajah cocok & terverifikasi (Jarak: ${result.distance}). Silakan lanjutkan presensi.`;
                if (statusBadge) {
                    statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0 uppercase tracking-wider';
                    statusBadge.textContent = 'Terverifikasi (Cocok)';
                }

                if (btnRetake) btnRetake.classList.remove('hidden');
            } else {
                // Wajah TIDAK COCOK!
                isLivenessVerified = false;
                if (inputFaceDescriptor) inputFaceDescriptor.value = '';
                if (inputFotoMasuk) inputFotoMasuk.value = '';
                if (inputFotoKeluar) inputFotoKeluar.value = '';

                // Suara buzzer gagal
                playTone(280, 'sawtooth', 0.35);

                const guideOval = document.getElementById('guide-oval');
                if (guideOval) guideOval.setAttribute('stroke', '#f43f5e');

                if (statusBox) {
                    statusBox.className = 'p-3 rounded-xl border border-rose-300 bg-rose-50 text-xs flex items-center justify-between gap-2 transition-all';
                }
                if (statusDot) statusDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
                if (statusBadge) {
                    statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 shrink-0 uppercase tracking-wider';
                    statusBadge.textContent = 'Wajah Tidak Cocok!';
                }
                const failDistanceText = result.distance ? ` (Selisih: ${result.distance})` : '';
                if (statusText) {
                    statusText.textContent = (result.message || 'Wajah tidak cocok dengan akun terdaftar!') + failDistanceText + ' Mengulang dalam 3 detik...';
                }

                if (btnRetake) btnRetake.classList.remove('hidden');

                // Otomatis mulai ulang sesi liveness setelah 3.5 detik
                setTimeout(() => {
                    if (!isLivenessVerified) {
                        startNewLivenessSession();
                    }
                }, 3500);
            }
        } catch (netErr) {
            console.error('Error saat verifikasi wajah ke server:', netErr);
            isLivenessVerified = false;
            if (inputFaceDescriptor) inputFaceDescriptor.value = '';
            if (inputFotoMasuk) inputFotoMasuk.value = '';
            if (inputFotoKeluar) inputFotoKeluar.value = '';

            playTone(280, 'sawtooth', 0.3);

            if (statusBox) {
                statusBox.className = 'p-3 rounded-xl border border-rose-300 bg-rose-50 text-xs flex items-center justify-between gap-2 transition-all';
            }
            if (statusDot) statusDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
            if (statusBadge) {
                statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 shrink-0 uppercase tracking-wider';
                statusBadge.textContent = 'Gagal Verifikasi';
            }
            if (statusText) statusText.textContent = 'Gagal menghubungi server untuk memverifikasi wajah. Mengulang kamera...';

            setTimeout(() => {
                startNewLivenessSession();
            }, 3000);
        }
    }

    // Listener Hasil Frame FaceMesh MediaPipe Realtime
    function onFaceMeshResults(results) {
        if (!isLivenessActive || isLivenessVerified) return;

        const guideOval = document.getElementById('guide-oval');

        // 1. Cek apakah ada wajah manusia di depan kamera
        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            stepConsecutiveFrames = 0;
            centerHoldStartTime = null;
            blinkState = 'waiting_close';
            if (guideOval) guideOval.setAttribute('stroke', '#f43f5e'); // Merah (tidak ada wajah)
            updateChallengeUI('Arahkan wajah ke kamera', 'Posisikan wajah Anda tepat di dalam bingkai oval', '👤');
            return;
        }

        const landmarks = results.multiFaceLandmarks[0];

        // Landmark anatomi wajah
        const nose = landmarks[1];          // Ujung hidung
        const cheekRight = landmarks[234];  // Sisi kanan wajah anatomi
        const cheekLeft = landmarks[454];   // Sisi kiri wajah anatomi
        const forehead = landmarks[10];     // Dahi
        const chin = landmarks[152];        // Dagu

        // 2. Cek jarak dan posisi wajah dalam frame
        const faceHeight = Math.hypot(chin.x - forehead.x, chin.y - forehead.y);

        if (faceHeight < 0.22) {
            stepConsecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b');
            updateChallengeUI('Dekatkan wajah Anda', 'Posisikan wajah lebih dekat ke dalam bingkai oval', '🔍');
            return;
        }

        if (faceHeight > 0.85) {
            stepConsecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b');
            updateChallengeUI('Mundurkan sedikit wajah', 'Wajah terlalu dekat dengan kamera', '🔍');
            return;
        }

        if (nose.x < 0.22 || nose.x > 0.78 || nose.y < 0.20 || nose.y > 0.80) {
            stepConsecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b');
            updateChallengeUI('Posisikan wajah di tengah', 'Arahkan wajah tepat di tengah bingkai oval', '🎯');
            return;
        }

        // Posisi wajah tepat di dalam bingkai oval
        if (guideOval) guideOval.setAttribute('stroke', '#22c55e'); // Hijau cerah

        // 3. Hitung Rasio Yaw Rotasi Kepala (Horizontal Turn)
        // dRight: Jarak hidung ke pipi kanan anatomi (kiri pada kamera)
        // dLeft: Jarak hidung ke pipi kiri anatomi (kanan pada kamera)
        const dRight = Math.abs(nose.x - cheekRight.x);
        const dLeft = Math.abs(cheekLeft.x - nose.x);
        const yawRatio = (dRight + dLeft) > 0 ? (dRight / (dRight + dLeft)) : 0.5;

        // 4. Hitung EAR (Eye Aspect Ratio) untuk Kedipan Mata
        // Mata Kanan Anatomi: 159-145 (vertikal), 33-133 (horizontal)
        const vr = Math.hypot(landmarks[159].x - landmarks[145].x, landmarks[159].y - landmarks[145].y);
        const hr = Math.hypot(landmarks[33].x - landmarks[133].x, landmarks[33].y - landmarks[133].y);
        const earR = hr > 0 ? (vr / hr) : 0;

        // Mata Kiri Anatomi: 386-374 (vertikal), 263-362 (horizontal)
        const vl = Math.hypot(landmarks[386].x - landmarks[374].x, landmarks[386].y - landmarks[374].y);
        const hl = Math.hypot(landmarks[263].x - landmarks[362].x, landmarks[263].y - landmarks[362].y);
        const earL = hl > 0 ? (vl / hl) : 0;

        const avgEar = (earR + earL) / 2;

        // 5. Verifikasi Gerakan Sesuai Langkah Tantangan Aktif
        const currentStep = currentChallenge.steps[currentStepIndex];
        updateChallengeUI();

        let isStepSatisfied = false;

        if (currentStep.type === 'turn_left') {
            // Pengguna menoleh ke arah kirinya sendiri (yawRatio > 0.65)
            if (yawRatio > 0.65) {
                stepConsecutiveFrames++;
                if (stepConsecutiveFrames >= 3) {
                    isStepSatisfied = true;
                }
            } else {
                stepConsecutiveFrames = Math.max(0, stepConsecutiveFrames - 1);
            }
        } else if (currentStep.type === 'turn_right') {
            // Pengguna menoleh ke arah kanannya sendiri (yawRatio < 0.35)
            if (yawRatio < 0.35) {
                stepConsecutiveFrames++;
                if (stepConsecutiveFrames >= 3) {
                    isStepSatisfied = true;
                }
            } else {
                stepConsecutiveFrames = Math.max(0, stepConsecutiveFrames - 1);
            }
        } else if (currentStep.type === 'blink') {
            // Siklus kedip: mata terpejam (ear < 0.15) lalu terbuka kembali (ear > 0.20)
            if (blinkState === 'waiting_close') {
                if (avgEar < 0.15) blinkState = 'closed';
            } else if (blinkState === 'closed') {
                if (avgEar > 0.20) {
                    blinkState = 'waiting_close';
                    isStepSatisfied = true;
                }
            }
        } else if (currentStep.type === 'face_center') {
            // Wajah menghadap lurus ke depan (yawRatio antara 0.42 dan 0.58)
            if (yawRatio >= 0.42 && yawRatio <= 0.58) {
                if (!centerHoldStartTime) {
                    centerHoldStartTime = performance.now();
                } else if (performance.now() - centerHoldStartTime >= 550) {
                    // Tahan 550ms untuk kestabilan pose bebas blur
                    isStepSatisfied = true;
                }
            } else {
                centerHoldStartTime = null;
            }
        }

        // Jika langkah aktif terpenuhi
        if (isStepSatisfied) {
            playTone(720, 'sine', 0.1);
            stepConsecutiveFrames = 0;
            centerHoldStartTime = null;
            blinkState = 'waiting_close';
            currentStepIndex++;

            if (currentStepIndex >= currentChallenge.steps.length) {
                // Semua langkah berhasil -> Auto-capture foto selfie
                completeLivenessAndCapture();
            } else {
                updateChallengeUI();
            }
        }
    }

    // Inisialisasi MediaPipe FaceMesh & Camera Stream
    function initFaceMeshAndCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (placeholder) {
                placeholder.innerHTML = `
                    <div class="text-rose-400 text-xs p-4">
                        <p class="font-bold">Peramban Anda tidak mendukung akses kamera.</p>
                        <p class="text-[11px] text-slate-300 mt-1">Gunakan peramban modern seperti Google Chrome atau Microsoft Edge.</p>
                    </div>
                `;
            }
            return;
        }

        try {
            faceMeshInstance = new FaceMesh({
                locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
            });

            faceMeshInstance.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true,
                minDetectionConfidence: 0.5,
                minTrackingConfidence: 0.5
            });

            faceMeshInstance.onResults(onFaceMeshResults);

            cameraInstance = new Camera(video, {
                onFrame: async () => {
                    if (isLivenessActive && !isLivenessVerified && video.videoWidth > 0 && !isSendingFrame) {
                        isSendingFrame = true;
                        try {
                            await faceMeshInstance.send({ image: video });
                        } catch (err) {
                            console.warn("FaceMesh frame processing error:", err);
                        } finally {
                            isSendingFrame = false;
                        }
                    }
                },
                width: 640,
                height: 480
            });

            cameraInstance.start()
                .then(() => {
                    if (placeholder) placeholder.classList.add('hidden');
                    startNewLivenessSession();
                })
                .catch((err) => {
                    console.error("Gagal membuka kamera:", err);
                    if (placeholder) {
                        placeholder.innerHTML = `
                            <div class="text-rose-400 text-xs p-4">
                                <p class="font-bold">Izin kamera tidak diberikan atau perangkat tidak mendukung.</p>
                                <p class="text-[11px] text-slate-300 mt-1">Pastikan izin kamera diaktifkan di peramban Anda.</p>
                            </div>
                        `;
                    }
                });
        } catch (e) {
            console.error("Inisialisasi FaceMesh gagal:", e);
        }
    }

    // Tombol Ulangi Verifikasi
    if (btnRetake) {
        btnRetake.addEventListener('click', function() {
            startNewLivenessSession();
        });
    }

    // Periksa kesiapan pustaka MediaPipe FaceMesh & Camera
    if (video) {
        let loadAttempts = 0;
        function checkLibrariesAndStart() {
            if (typeof FaceMesh !== 'undefined' && typeof Camera !== 'undefined') {
                initFaceMeshAndCamera();
            } else if (loadAttempts < 50) {
                loadAttempts++;
                setTimeout(checkLibrariesAndStart, 150);
            } else {
                if (placeholder) {
                    placeholder.innerHTML = `
                        <div class="text-rose-400 text-xs p-4">
                            <p class="font-bold">Gagal memuat pustaka AI Liveness dari CDN.</p>
                            <p class="text-[11px] text-slate-300 mt-1">Periksa koneksi internet Anda lalu segarkan halaman ini.</p>
                        </div>
                    `;
                }
            }
        }
        checkLibrariesAndStart();
    }

    // ==========================================
    // 3. PETA LEAFLET & REALTIME GPS RADIUS
    // ==========================================
    const officeConfig = {
        lat: {{ $officeLocation['lat'] ?? -3.4893886444181983 }},
        lng: {{ $officeLocation['lng'] ?? 114.8252583950533 }},
        radius: {{ $officeLocation['radius'] ?? 40 }},
        nama: "{{ addslashes($officeLocation['nama'] ?? 'Kantor Utama') }}"
    };

    const inputLokasiMasuk = document.getElementById('input_lokasi_masuk');
    const inputLokasiKeluar = document.getElementById('input_lokasi_keluar');
    const gpsStatus = document.getElementById('gps-status');
    const gpsCoords = document.getElementById('gps-coords');
    const gpsMapLink = document.getElementById('gps-map-link');
    const btnRefreshGps = document.getElementById('btn-refresh-gps');

    let map = null;
    let officeMarker = null;
    let officeCircle = null;
    let userMarker = null;
    let currentDistanceMeters = null;

    // Formula Haversine dalam JavaScript (hasil dalam meter)
    function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Radius bumi dalam meter
        const dLat = (lat2 - lat1) * (Math.PI / 180);
        const dLon = (lon2 - lon1) * (Math.PI / 180);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(R * c);
    }

    // Ambil mode kerja saat ini (onsite atau wfh)
    function getCurrentWorkMode() {
        const radioMode = document.querySelector('input[name="mode_kerja"]:checked');
        if (radioMode) return radioMode.value;

        const todayModeEl = document.getElementById('today-mode-kerja');
        if (todayModeEl) return todayModeEl.value;

        return 'onsite';
    }

    // Update status visual radius pada UI
    function updateRadiusStatusUI(distance) {
        const badgeEl = document.getElementById('radius-badge');
        const boxEl = document.getElementById('radius-indicator-box');
        const distanceEl = document.getElementById('gps-distance');
        const mode = getCurrentWorkMode();
        const isTLToday = (document.getElementById('is-tugas-luar-today')?.value === '1');
        const isTugasLuarActive = (mode === 'tugas_luar') || isTLToday;

        if (distanceEl) {
            distanceEl.textContent = `${distance.toLocaleString('id-ID')} meter`;
        }

        if (!badgeEl) return;

        if (mode === 'wfh') {
            badgeEl.className = 'pt-1.5 border-t border-blue-100 flex items-center gap-1.5 text-[11px] font-semibold text-blue-700';
            badgeEl.innerHTML = `
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Mode WFH Aktif: Bebas batasan radius kantor (Jarak: ${distance.toLocaleString('id-ID')}m).</span>
            `;
            if (boxEl) {
                boxEl.className = 'p-3 rounded-xl border border-blue-200 bg-blue-50/50 text-xs space-y-1.5 shadow-xs transition-colors';
            }
        } else if (isTugasLuarActive) {
            badgeEl.className = 'pt-1.5 border-t border-indigo-100 flex items-center gap-1.5 text-[11px] font-semibold text-indigo-700';
            badgeEl.innerHTML = `
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span>Tugas Luar (TL) Aktif: Bebas batasan radius kantor (Jarak: ${distance.toLocaleString('id-ID')}m).</span>
            `;
            if (boxEl) {
                boxEl.className = 'p-3 rounded-xl border border-indigo-200 bg-indigo-50/50 text-xs space-y-1.5 shadow-xs transition-colors';
            }
        } else {
            if (distance <= officeConfig.radius) {
                badgeEl.className = 'pt-1.5 border-t border-emerald-100 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700';
                badgeEl.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Di Dalam Radius Kantor (${distance}m &le; ${officeConfig.radius}m) - Anda dapat presensi.</span>
                `;
                if (boxEl) {
                    boxEl.className = 'p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 text-xs space-y-1.5 shadow-xs transition-colors';
                }
            } else {
                badgeEl.className = 'pt-1.5 border-t border-rose-100 flex items-center gap-1.5 text-[11px] font-semibold text-rose-700';
                badgeEl.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Di Luar Radius Kantor (${distance.toLocaleString('id-ID')}m > ${officeConfig.radius}m) - Dekati area kantor untuk Onsite.</span>
                `;
                if (boxEl) {
                    boxEl.className = 'p-3 rounded-xl border border-rose-200 bg-rose-50/50 text-xs space-y-1.5 shadow-xs transition-colors';
                }
            }
        }
    }

    // Toggle formulir rincian tugas luar pada Skenario 1
    const containerTL = document.getElementById('container-tugas-luar');
    const inputTujuanTL = document.getElementById('input_tujuan_tl');
    const inputKeperluanTL = document.getElementById('input_keperluan_tl');
    const inputWaktuMulaiTL = document.getElementById('input_waktu_mulai_tl');

    function toggleTugasLuarForm() {
        const mode = getCurrentWorkMode();
        if (containerTL) {
            if (mode === 'tugas_luar') {
                containerTL.classList.remove('hidden');
                if (inputTujuanTL) inputTujuanTL.setAttribute('required', 'required');
                if (inputKeperluanTL) inputKeperluanTL.setAttribute('required', 'required');
                if (inputWaktuMulaiTL) inputWaktuMulaiTL.setAttribute('required', 'required');
            } else {
                containerTL.classList.add('hidden');
                if (inputTujuanTL) inputTujuanTL.removeAttribute('required');
                if (inputKeperluanTL) inputKeperluanTL.removeAttribute('required');
                if (inputWaktuMulaiTL) inputWaktuMulaiTL.removeAttribute('required');
            }
        }
    }

    // Listener pergantian radio mode kerja
    const radioModes = document.querySelectorAll('input[name="mode_kerja"]');
    radioModes.forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleTugasLuarForm();
            if (currentDistanceMeters !== null) {
                updateRadiusStatusUI(currentDistanceMeters);
            }
        });
    });
    toggleTugasLuarForm();

    // Inisialisasi Peta Leaflet
    const mapContainer = document.getElementById('leaflet-map');
    if (mapContainer && typeof L !== 'undefined') {
        map = L.map('leaflet-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([officeConfig.lat, officeConfig.lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Marker Kantor (Pin Merah)
        const officeIcon = L.divIcon({
            className: 'office-pin-icon',
            html: `
                <div style="background-color: #ef4444; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25); border: 2px solid #ffffff;">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            `,
            iconSize: [34, 34],
            iconAnchor: [17, 17],
            popupAnchor: [0, -20]
        });

        officeMarker = L.marker([officeConfig.lat, officeConfig.lng], { icon: officeIcon })
            .addTo(map)
            .bindPopup(`
                <div style="text-align: center; font-size: 12px; font-family: sans-serif; line-height: 1.4;">
                    <strong style="color: #1e293b; font-size: 13px;">${officeConfig.nama}</strong><br>
                    <span style="color: #64748b;">Pusat Titik Presensi</span><br>
                    <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; background: #fee2e2; color: #b91c1c; border-radius: 9999px; font-weight: bold; font-size: 10px;">
                        Radius: ${officeConfig.radius} meter
                    </span>
                </div>
            `);

        // Lingkaran Radius Kantor 40m
        officeCircle = L.circle([officeConfig.lat, officeConfig.lng], {
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.15,
            radius: officeConfig.radius,
            weight: 2
        }).addTo(map);

        setTimeout(function() {
            if (map) map.invalidateSize();
        }, 250);
    }

    // Deteksi Posisi Pengguna Melalui HTML5 Geolocation API
    function getGPSLocation() {
        if (inputLokasiMasuk) inputLokasiMasuk.value = '';
        if (inputLokasiKeluar) inputLokasiKeluar.value = '';
        currentDistanceMeters = null;

        if (!navigator.geolocation) {
            if (gpsStatus) {
                gpsStatus.innerHTML = '<span class="text-rose-600">Browser tidak mendukung geolokasi GPS</span>';
            }
            return;
        }

        if (gpsStatus) {
            gpsStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span> Mendeteksi koordinat lokasi GPS...';
            gpsStatus.className = 'text-xs font-bold text-amber-600 flex items-center gap-1.5 mt-0.5';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const rawLat = position.coords.latitude;
                const rawLng = position.coords.longitude;
                const lat = rawLat.toFixed(6);
                const lng = rawLng.toFixed(6);
                const coordString = `${lat}, ${lng}`;

                if (inputLokasiMasuk) inputLokasiMasuk.value = coordString;
                if (inputLokasiKeluar) inputLokasiKeluar.value = coordString;

                if (gpsStatus) {
                    gpsStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500"></span> Lokasi GPS Berhasil Terkunci';
                    gpsStatus.className = 'text-xs font-bold text-emerald-600 flex items-center gap-1.5 mt-0.5';
                }

                if (gpsCoords) gpsCoords.textContent = coordString;

                if (gpsMapLink) {
                    gpsMapLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
                }

                // Hitung jarak ke kantor
                currentDistanceMeters = calculateHaversineDistance(officeConfig.lat, officeConfig.lng, rawLat, rawLng);
                updateRadiusStatusUI(currentDistanceMeters);

                // Update marker user di peta Leaflet
                if (map) {
                    const userIcon = L.divIcon({
                        className: 'user-pin-icon',
                        html: `
                            <div style="background-color: #2563eb; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25); border: 2px solid #ffffff;">
                                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                        `,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                        popupAnchor: [0, -18]
                    });

                    if (userMarker) {
                        userMarker.setLatLng([rawLat, rawLng]);
                    } else {
                        userMarker = L.marker([rawLat, rawLng], { icon: userIcon }).addTo(map);
                    }

                    userMarker.bindPopup(`
                        <div style="text-align: center; font-size: 12px; font-family: sans-serif; line-height: 1.4;">
                            <strong style="color: #1e293b;">Posisi Anda Saat Ini</strong><br>
                            <span style="color: #64748b;">Jarak ke kantor: <b>${currentDistanceMeters.toLocaleString('id-ID')} m</b></span>
                        </div>
                    `);

                    // Tampilkan kedua titik (kantor & user) dalam view peta
                    const bounds = L.latLngBounds([
                        [officeConfig.lat, officeConfig.lng],
                        [rawLat, rawLng]
                    ]);
                    map.fitBounds(bounds.pad(0.25));
                }
            },
            function(error) {
                console.warn('GPS error:', error);
                let pesan = 'Gagal mendeteksi lokasi GPS.';
                if (error.code === error.PERMISSION_DENIED) {
                    pesan = 'Izin lokasi GPS ditolak oleh browser. Mohon izinkan akses lokasi.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    pesan = 'Sinyal lokasi GPS tidak tersedia. Pastikan GPS perangkat aktif.';
                } else if (error.code === error.TIMEOUT) {
                    pesan = 'Waktu permintaan sinyal GPS habis. Coba segarkan lokasi.';
                }

                // Kosongkan semua, jangan isi koordinat palsu
                if (inputLokasiMasuk) inputLokasiMasuk.value = '';
                if (inputLokasiKeluar) inputLokasiKeluar.value = '';
                currentDistanceMeters = null;

                if (gpsStatus) {
                    gpsStatus.innerHTML = `<span class="w-2 h-2 rounded-full bg-rose-500"></span> ${pesan}`;
                    gpsStatus.className = 'text-xs font-bold text-rose-600 flex items-center gap-1.5 mt-0.5';
                }
                if (gpsCoords) gpsCoords.textContent = '-';
                
                const distanceEl = document.getElementById('gps-distance');
                if (distanceEl) distanceEl.textContent = 'Tidak diketahui';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    if (btnRefreshGps) {
        btnRefreshGps.addEventListener('click', getGPSLocation);
    }

    // Auto-detect GPS on load
    if (inputLokasiMasuk || inputLokasiKeluar) {
        getGPSLocation();
    }

    // ==========================================
    // 4. FORM VALIDASI PRE-SUBMIT (FOTO, GPS, RADIUS)
    // ==========================================
    const formMasuk = document.getElementById('form-presensi-masuk');
    if (formMasuk) {
        formMasuk.addEventListener('submit', function(e) {
            if (!isLivenessVerified || !isValidSelfie(inputFotoMasuk.value)) {
                e.preventDefault();
                alert('Verifikasi wajah belum selesai atau tidak valid!\n\nSilakan ikuti instruksi tantangan gerakan di depan kamera hingga foto dan kecocokan wajah diverifikasi.');
                return;
            }
            if (!inputFaceDescriptor || !inputFaceDescriptor.value) {
                e.preventDefault();
                alert('Wajah Anda belum terverifikasi atau tidak cocok dengan data akun terdaftar! Silakan ulangi verifikasi wajah di kamera.');
                return;
            }
            if (!inputLokasiMasuk.value) {
                e.preventDefault();
                alert('Titik lokasi GPS belum terdeteksi. Silakan klik tombol "Segarkan Titik Lokasi GPS".');
                return;
            }

            const selectedMode = getCurrentWorkMode();
            if (selectedMode === 'onsite' && currentDistanceMeters !== null && currentDistanceMeters > officeConfig.radius) {
                e.preventDefault();
                alert(`Presensi Onsite tidak dapat diproses!\n\nPosisi Anda saat ini berjarak ${currentDistanceMeters.toLocaleString('id-ID')} meter dari kantor (Batas maksimal radius: ${officeConfig.radius} meter).\n\nSilakan mendekat ke area kantor atau pilih mode "WFH" jika Anda sedang bekerja remote.`);
                return;
            }

            if (selectedMode === 'tugas_luar') {
                const tujuan = inputTujuanTL ? inputTujuanTL.value.trim() : '';
                const keperluan = inputKeperluanTL ? inputKeperluanTL.value.trim() : '';
                const waktuMulai = inputWaktuMulaiTL ? inputWaktuMulaiTL.value.trim() : '';
                if (!tujuan || !keperluan || !waktuMulai) {
                    e.preventDefault();
                    alert('Harap lengkapi semua kolom wajib pada Formulir Rincian Tugas Luar (Tujuan, Keperluan, dan Waktu Mulai)!');
                    return;
                }
            }
        });
    }

    const formKeluar = document.getElementById('form-presensi-keluar');
    if (formKeluar) {
        formKeluar.addEventListener('submit', function(e) {
            if (!isLivenessVerified || !isValidSelfie(inputFotoKeluar.value)) {
                e.preventDefault();
                alert('Verifikasi wajah kepulangan belum selesai atau tidak valid!\n\nSilakan ikuti instruksi tantangan gerakan di depan kamera hingga foto dan kecocokan wajah diverifikasi.');
                return;
            }
            if (!inputFaceDescriptor || !inputFaceDescriptor.value) {
                e.preventDefault();
                alert('Wajah Anda belum terverifikasi atau tidak cocok dengan data akun terdaftar! Silakan ulangi verifikasi wajah di kamera.');
                return;
            }
            if (!inputLokasiKeluar.value) {
                e.preventDefault();
                alert('Titik lokasi GPS kepulangan belum terdeteksi. Silakan klik tombol "Segarkan Titik Lokasi GPS".');
                return;
            }

            const isTLToday = (document.getElementById('is-tugas-luar-today')?.value === '1');
            const currentMode = getCurrentWorkMode();
            if (!isTLToday && currentMode === 'onsite' && currentDistanceMeters !== null && currentDistanceMeters > officeConfig.radius) {
                e.preventDefault();
                alert(`Presensi Pulang Onsite tidak dapat diproses!\n\nPosisi Anda saat ini berjarak ${currentDistanceMeters.toLocaleString('id-ID')} meter dari kantor (Batas maksimal radius: ${officeConfig.radius} meter).\n\nSilakan lakukan presensi kepulangan di area kantor.`);
                return;
            }
        });
    }
});

// Kontrol Modal Pengajuan Tugas Luar (Global Window Scope)
window.openModalAjukanTugasLuar = function() {
    const modal = document.getElementById('modal-ajukan-tugas-luar');
    if (modal) modal.classList.remove('hidden');
};

window.closeModalAjukanTugasLuar = function() {
    const modal = document.getElementById('modal-ajukan-tugas-luar');
    if (modal) modal.classList.add('hidden');
};
</script>
@endsection
