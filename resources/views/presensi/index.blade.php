@extends('layouts.user')

@section('title', 'Presensi Harian')

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

    <!-- Profile & Live Digital Clock Header Card -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-500/10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Presensi & Monitoring Harian</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $user->magang->nama_lengkap ?? $user->cs->nama_lengkap ?? $user->username }}!
                </h1>
                <p class="text-blue-100 text-sm mt-1 max-w-xl leading-relaxed">
                    Catat kehadiran Anda hari ini dengan mengaktifkan GPS dan mengambil foto selfie sebagai bukti absensi yang sah.
                </p>

                <!-- Informasi Tambahan Shift untuk CS -->
                @if($user->role === 'cs')
                    <div class="mt-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs text-blue-50">
                        <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @if($shiftToday)
                            <span>Jadwal Shift: <strong>{{ $shiftToday->nama }}</strong> ({{ substr($shiftToday->jam_masuk, 0, 5) }} - {{ substr($shiftToday->jam_keluar, 0, 5) }}) &bull; Toleransi: {{ $shiftToday->toleransi_masuk }} menit</span>
                        @else
                            <span>Belum ada jadwal shift CS khusus untuk hari ini (Jam kerja standar)</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Live Digital Clock Widget -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 text-center shrink-0 min-w-[220px]">
                <div class="text-xs uppercase tracking-widest text-blue-200 font-bold" id="current-date">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                </div>
                <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white font-mono mt-1" id="live-clock">
                    --:--:-- WIB
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

                <form action="{{ route('presensi.masuk') }}" method="POST" id="form-presensi-masuk" class="space-y-6">
                    @csrf
                    <input type="hidden" name="foto_masuk" id="input_foto_masuk" value="">
                    <input type="hidden" name="lokasi_masuk" id="input_lokasi_masuk" value="">

                    <!-- Pilihan Mode Kerja -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Mode Kerja Hari Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="mode_kerja" value="onsite" class="sr-only" checked>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                        🏢
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Onsite (Di Kantor)</div>
                                        <div class="text-xs text-slate-500">Bekerja langsung di lokasi kantor</div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="mode_kerja" value="wfh" class="sr-only">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                        🏠
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">WFH (Work From Home)</div>
                                        <div class="text-xs text-slate-500">Bekerja dari rumah / remote</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Webcam Selfie & GPS Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Kamera Selfie -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Foto Selfie Masuk <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border border-slate-200 shadow-inner">
                                <!-- Video Stream -->
                                <video id="webcam-video" autoplay playsinline class="w-full h-full object-cover"></video>
                                
                                <!-- Canvas Hidden (Untuk Capture) -->
                                <canvas id="webcam-canvas" class="hidden"></canvas>
                                
                                <!-- Hasil Capture Preview -->
                                <img id="captured-preview" class="w-full h-full object-cover hidden" alt="Selfie Preview">

                                <!-- Placeholder Ketika Kamera Belum Aktif -->
                                <div id="camera-placeholder" class="text-center p-4 text-slate-400">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <span class="text-xs">Menyiapkan kamera webcam...</span>
                                </div>
                            </div>

                            <!-- Tombol Kontrol Kamera -->
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-capture" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <span>Ambil Foto Selfie</span>
                                </button>
                                <button type="button" id="btn-retake" class="hidden px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                                    Foto Ulang
                                </button>
                            </div>
                        </div>

                        <!-- Deteksi Lokasi GPS -->
                        <div class="space-y-3 flex flex-col justify-between">
                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Titik Lokasi GPS <span class="text-rose-500">*</span>
                                </label>

                                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
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

                                    <div class="pt-3 border-t border-slate-200/80 text-xs text-slate-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">Koordinat:</span>
                                            <span id="gps-coords" class="font-mono font-bold text-slate-800">-</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">Akurasi:</span>
                                            <span id="gps-accuracy" class="font-mono text-slate-600">-</span>
                                        </div>
                                        <div id="gps-map-container" class="pt-1 hidden">
                                            <a id="gps-map-link" href="#" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline text-xs font-semibold">
                                                Lihat di Google Maps &rarr;
                                            </a>
                                        </div>
                                    </div>

                                    <button type="button" id="btn-refresh-gps" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        <span>Segarkan Lokasi GPS</span>
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
                        <button type="submit" id="btn-submit-masuk" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 transition cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Kirim Presensi Masuk Sekarang</span>
                        </button>
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
                                Presensi Masuk: <span class="font-mono text-emerald-700">{{ substr($todayPresensi->jam_masuk, 0, 5) }} WIB</span>
                            </h3>
                            <p class="text-xs text-emerald-700 mt-0.5">
                                Mode Kerja: <strong class="capitalize">{{ $todayPresensi->mode_kerja }}</strong> &bull; Lokasi: {{ $todayPresensi->lokasi_masuk ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white text-emerald-800 border border-emerald-200/80 text-center">
                        Sedang Aktif Bekerja
                    </span>
                </div>

                <!-- Form Absen Pulang -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                            Langkah 2: Presensi Pulang
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Formulir Presensi Pulang</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Selesaikan jam kerja hari ini dengan mengambil foto selfie kepulangan dan mendeteksi lokasi GPS.</p>
                    </div>

                    <form action="{{ route('presensi.keluar') }}" method="POST" id="form-presensi-keluar" class="space-y-6">
                        @csrf
                        <input type="hidden" name="foto_keluar" id="input_foto_keluar" value="">
                        <input type="hidden" name="lokasi_keluar" id="input_lokasi_keluar" value="">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Kamera Pulang -->
                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Foto Selfie Pulang <span class="text-rose-500">*</span>
                                </label>
                                
                                <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border border-slate-200 shadow-inner">
                                    <video id="webcam-video" autoplay playsinline class="w-full h-full object-cover"></video>
                                    <canvas id="webcam-canvas" class="hidden"></canvas>
                                    <img id="captured-preview" class="w-full h-full object-cover hidden" alt="Selfie Preview">
                                    <div id="camera-placeholder" class="text-center p-4 text-slate-400">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                                        <span class="text-xs">Menyiapkan kamera webcam...</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-capture" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md shadow-amber-500/20 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                                        <span>Ambil Foto Selfie Pulang</span>
                                    </button>
                                    <button type="button" id="btn-retake" class="hidden px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                                        Foto Ulang
                                    </button>
                                </div>
                            </div>

                            <!-- GPS Pulang -->
                            <div class="space-y-3 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Titik Lokasi GPS Kepulangan <span class="text-rose-500">*</span>
                                    </label>

                                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
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

                                        <div class="pt-3 border-t border-slate-200/80 text-xs text-slate-600 space-y-1">
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Koordinat:</span>
                                                <span id="gps-coords" class="font-mono font-bold text-slate-800">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-slate-400">Akurasi:</span>
                                                <span id="gps-accuracy" class="font-mono text-slate-600">-</span>
                                            </div>
                                            <div id="gps-map-container" class="pt-1 hidden">
                                                <a id="gps-map-link" href="#" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline text-xs font-semibold">
                                                    Lihat di Google Maps &rarr;
                                                </a>
                                            </div>
                                        </div>

                                        <button type="button" id="btn-refresh-gps" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                            <span>Segarkan Lokasi GPS</span>
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
                            <button type="submit" id="btn-submit-keluar" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-lg shadow-amber-500/25 transition cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                <span>Kirim Presensi Pulang Sekarang</span>
                            </button>
                        </div>
                    </form>
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
                            <div class="text-sm font-bold text-slate-800 font-mono">{{ substr($todayPresensi->jam_masuk, 0, 5) }} WIB</div>
                            <div class="text-[11px] text-slate-500 capitalize">Mode: {{ $todayPresensi->mode_kerja }}</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                        @if($todayPresensi->foto_keluar_url)
                            <img src="{{ $todayPresensi->foto_keluar_url }}" alt="Foto Pulang" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0">
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Presensi Pulang</span>
                            <div class="text-sm font-bold text-slate-800 font-mono">{{ substr($todayPresensi->jam_keluar, 0, 5) }} WIB</div>
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

            <!-- Filter Bulan -->
            <form action="{{ route('presensi.index') }}" method="GET" class="flex items-center gap-2">
                <input
                    type="month"
                    name="bulan"
                    value="{{ request('bulan', date('Y-m')) }}"
                    class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
            </form>
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
                                @if ($item->mode_kerja === 'onsite')
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
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $item->jam_keluar ? substr($item->jam_keluar, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    {{ $item->status }}
                                </span>
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

</div>
@endsection

@section('scripts')
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
            clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ==========================================
    // 2. WEBCAM HTML5 STREAM & CAPTURE
    // ==========================================
    const video = document.getElementById('webcam-video');
    const canvas = document.getElementById('webcam-canvas');
    const preview = document.getElementById('captured-preview');
    const placeholder = document.getElementById('camera-placeholder');
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');

    // Input target hidden foto
    const inputFotoMasuk = document.getElementById('input_foto_masuk');
    const inputFotoKeluar = document.getElementById('input_foto_keluar');

    let streamObj = null;

    if (video) {
        // Minta akses kamera pengguna
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 640 }, 
                    height: { ideal: 480 },
                    facingMode: 'user' 
                } 
            })
            .then(function(stream) {
                streamObj = stream;
                video.srcObject = stream;
                video.play();
                if (placeholder) placeholder.classList.add('hidden');
            })
            .catch(function(err) {
                console.error("Gagal membuka kamera:", err);
                if (placeholder) {
                    placeholder.innerHTML = `
                        <div class="text-rose-400 text-xs">
                            <p class="font-bold">Izin kamera tidak diberikan atau perangkat tidak mendukung.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Pastikan izin kamera aktif di browser Anda.</p>
                        </div>
                    `;
                }
            });
        }

        // Jepret Foto Selfie
        if (btnCapture) {
            btnCapture.addEventListener('click', function() {
                if (!streamObj) {
                    alert('Kamera belum siap atau izin kamera belum diberikan.');
                    return;
                }

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to base64 JPEG
                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                // Set preview
                preview.src = dataUrl;
                preview.classList.remove('hidden');
                video.classList.add('hidden');

                // Simpan ke input hidden
                if (inputFotoMasuk) inputFotoMasuk.value = dataUrl;
                if (inputFotoKeluar) inputFotoKeluar.value = dataUrl;

                // Ganti tombol
                btnCapture.classList.add('hidden');
                if (btnRetake) btnRetake.classList.remove('hidden');
            });
        }

        // Foto Ulang
        if (btnRetake) {
            btnRetake.addEventListener('click', function() {
                preview.classList.add('hidden');
                video.classList.remove('hidden');

                if (inputFotoMasuk) inputFotoMasuk.value = '';
                if (inputFotoKeluar) inputFotoKeluar.value = '';

                btnCapture.classList.remove('hidden');
                btnRetake.classList.add('hidden');
            });
        }
    }

    // ==========================================
    // 3. HTML5 GPS GEOLOCATION
    // ==========================================
    const inputLokasiMasuk = document.getElementById('input_lokasi_masuk');
    const inputLokasiKeluar = document.getElementById('input_lokasi_keluar');
    const gpsStatus = document.getElementById('gps-status');
    const gpsCoords = document.getElementById('gps-coords');
    const gpsAccuracy = document.getElementById('gps-accuracy');
    const gpsMapContainer = document.getElementById('gps-map-container');
    const gpsMapLink = document.getElementById('gps-map-link');
    const btnRefreshGps = document.getElementById('btn-refresh-gps');

    function getGPSLocation() {
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
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                const accuracy = Math.round(position.coords.accuracy);
                const coordString = `${lat}, ${lng}`;

                if (inputLokasiMasuk) inputLokasiMasuk.value = coordString;
                if (inputLokasiKeluar) inputLokasiKeluar.value = coordString;

                if (gpsStatus) {
                    gpsStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500"></span> Lokasi GPS Berhasil Terkunci';
                    gpsStatus.className = 'text-xs font-bold text-emerald-600 flex items-center gap-1.5 mt-0.5';
                }

                if (gpsCoords) gpsCoords.textContent = coordString;
                if (gpsAccuracy) gpsAccuracy.textContent = `&plusmn; ${accuracy} meter`;

                if (gpsMapContainer && gpsMapLink) {
                    gpsMapLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
                    gpsMapContainer.classList.remove('hidden');
                }
            },
            function(error) {
                console.warn('GPS error:', error);
                let pesan = 'Gagal mendeteksi lokasi GPS.';
                if (error.code === error.PERMISSION_DENIED) {
                    pesan = 'Izin lokasi GPS ditolak oleh browser. Mohon izinkan lokasi.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    pesan = 'Sinyal lokasi tidak tersedia.';
                } else if (error.code === error.TIMEOUT) {
                    pesan = 'Waktu permintaan GPS habis.';
                }

                if (gpsStatus) {
                    gpsStatus.innerHTML = `<span class="text-rose-600">${pesan}</span>`;
                }

                // Fallback default coordinates jika pengujian di localhost tanpa sinyal GPS
                const defaultCoord = '-6.208800, 106.845600';
                if (inputLokasiMasuk) inputLokasiMasuk.value = defaultCoord;
                if (inputLokasiKeluar) inputLokasiKeluar.value = defaultCoord;
                if (gpsCoords) gpsCoords.textContent = `${defaultCoord} (Perkiraan)`;
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
    getGPSLocation();

    // ==========================================
    // 4. FORM VALIDASI PRE-SUBMIT
    // ==========================================
    const formMasuk = document.getElementById('form-presensi-masuk');
    if (formMasuk) {
        formMasuk.addEventListener('submit', function(e) {
            if (!inputFotoMasuk.value) {
                e.preventDefault();
                alert('Silakan ambil foto selfie masuk terlebih dahulu sebelum mengirim presensi.');
                return;
            }
            if (!inputLokasiMasuk.value) {
                e.preventDefault();
                alert('Titik lokasi GPS belum terdeteksi. Silakan klik tombol "Segarkan Lokasi GPS".');
                return;
            }
        });
    }

    const formKeluar = document.getElementById('form-presensi-keluar');
    if (formKeluar) {
        formKeluar.addEventListener('submit', function(e) {
            if (!inputFotoKeluar.value) {
                e.preventDefault();
                alert('Silakan ambil foto selfie pulang terlebih dahulu sebelum mengirim presensi.');
                return;
            }
            if (!inputLokasiKeluar.value) {
                e.preventDefault();
                alert('Titik lokasi GPS belum terdeteksi. Silakan klik tombol "Segarkan Lokasi GPS".');
                return;
            }
        });
    }
});
</script>
@endsection
