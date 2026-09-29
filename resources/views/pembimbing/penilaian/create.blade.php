@extends('layouts.pembimbing')

@section('title', 'Input Penilaian Akhir Magang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Nav -->
    <div class="flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('pembimbing.penilaian.index') }}" class="hover:text-purple-600 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Penilaian
        </a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">Formulir Penilaian</span>
    </div>

    <!-- Header & Info Peserta -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between pb-6 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 font-black text-lg flex items-center justify-center overflow-hidden border border-purple-200 shrink-0">
                    @if($magang->foto)
                        <img src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($magang->nama_lengkap, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $magang->nama_lengkap }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">NIS/NIM: {{ $magang->no_induk }} &bull; {{ $magang->instansi_pendidikan }}</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Divisi / Penempatan</div>
                <div class="text-sm font-bold text-purple-700">{{ $magang->divisi->nama_divisi ?? '-' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
            <div>
                <span class="text-slate-400 block">Jurusan</span>
                <span class="font-semibold text-slate-700">{{ $magang->jurusan ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Periode Mulai</span>
                <span class="font-semibold text-slate-700">{{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d M Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Periode Selesai</span>
                <span class="font-semibold text-slate-700">{{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d M Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Status Saat Ini</span>
                <span class="font-semibold text-slate-700 capitalize">{{ $magang->status }}</span>
            </div>
        </div>
    </div>

    <!-- Form Penilaian -->
    <form action="{{ route('pembimbing.penilaian.store') }}" method="POST" id="formPenilaian" class="space-y-6">
        @csrf
        <input type="hidden" name="magang_id" value="{{ $magang->id }}">

        <!-- Live Score Summary Card -->
        <div class="bg-gradient-to-br from-slate-900 via-purple-950 to-indigo-950 rounded-3xl p-6 sm:p-7 text-white shadow-xl border border-purple-800/40 relative overflow-hidden">
            <!-- Background Glow Effect -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-6">
                <!-- Header Card & Main Score -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-5 border-b border-white/10">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 border border-purple-400/30 text-[11px] font-bold uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                            Live Realtime Calculation
                        </div>
                        <h3 class="text-xl font-extrabold text-white tracking-tight">Kalkulasi Nilai Akhir Magang</h3>
                        <p class="text-xs text-purple-200/80 mt-1 max-w-lg">
                            Nilai akhir dihitung secara otomatis dan transparan berdasarkan akumulasi nilai berbobot dari seluruh kriteria di bawah ini.
                        </p>
                    </div>

                    <!-- Main Score Highlight Badges -->
                    <div class="flex items-center gap-5 bg-white/10 p-4 rounded-2xl border border-white/15 backdrop-blur-md shrink-0 w-full sm:w-auto justify-center">
                        <div class="text-center px-2">
                            <span class="text-[10px] uppercase tracking-wider text-purple-200 font-bold block">Skor Akhir</span>
                            <div class="flex items-baseline justify-center gap-1">
                                <span id="liveTotalScore" class="text-4xl sm:text-5xl font-black text-white tracking-tight">0</span>
                                <span class="text-xs text-purple-300 font-semibold">/100</span>
                            </div>
                            <span class="text-[10px] text-purple-300/80 block mt-0.5 font-mono">Presisi: <span id="liveRawScore" class="font-bold text-purple-200">0.00</span></span>
                        </div>
                        <div class="h-12 w-px bg-white/20"></div>
                        <div class="text-center px-2">
                            <span class="text-[10px] uppercase tracking-wider text-purple-200 font-bold block">Predikat Mutu</span>
                            <span id="livePredikatLetter" class="text-4xl sm:text-5xl font-black text-amber-300 tracking-tight block">-</span>
                            <span id="livePredikatLabel" class="text-[11px] text-purple-200 font-semibold block mt-0.5 max-w-[130px] truncate">-</span>
                        </div>
                    </div>
                </div>

                <!-- Visual Meteran / Scale Predikat Nilai -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold text-purple-200">
                        <span class="flex items-center gap-1.5">
                            <span>📊 Skala Predikat Nilai:</span>
                        </span>
                        <span class="text-[11px] text-purple-300 font-mono">
                            Capaian Saat Ini: <b id="liveScorePercentText" class="text-white">0%</b>
                        </span>
                    </div>

                    <!-- Interactive Segmented Bar -->
                    <div class="grid grid-cols-10 h-3.5 rounded-full overflow-hidden border border-white/20 text-[9px] font-bold text-center">
                        <!-- E: 0-59 (6 cols = 60%) -->
                        <div class="col-span-6 bg-rose-500/50 flex items-center justify-center text-rose-200 border-r border-slate-900/50" title="E (Tidak Baik): 0 - 59">
                            Tidak Baik (&lt;60)
                        </div>
                        <!-- D: 60-69 (1 col = 10%) -->
                        <div class="col-span-1 bg-orange-500/50 flex items-center justify-center text-orange-200 border-r border-slate-900/50" title="D (Kurang Baik): 60 - 69">
                            D
                        </div>
                        <!-- C: 70-79 (1 col = 10%) -->
                        <div class="col-span-1 bg-amber-500/50 flex items-center justify-center text-amber-200 border-r border-slate-900/50" title="C (Cukup Baik): 70 - 79">
                            C
                        </div>
                        <!-- B: 80-89 (1 col = 10%) -->
                        <div class="col-span-1 bg-blue-500/50 flex items-center justify-center text-blue-200 border-r border-slate-900/50" title="B (Baik): 80 - 89">
                            B
                        </div>
                        <!-- A: 90-100 (1 col = 10%) -->
                        <div class="col-span-1 bg-emerald-500/60 flex items-center justify-center text-emerald-200" title="A (Sangat Baik): 90 - 100">
                            A (&ge;90)
                        </div>
                    </div>

                    <!-- Active Progress Fill Bar -->
                    <div class="h-2 bg-slate-800/80 rounded-full overflow-hidden border border-white/10">
                        <div id="liveScoreProgressBar" class="h-full bg-gradient-to-r from-purple-400 via-indigo-300 to-emerald-400 transition-all duration-300 rounded-full" style="width: 0%"></div>
                    </div>

                    <!-- Range Marker Labels -->
                    <div class="flex justify-between text-[10px] text-purple-300/70 font-mono font-medium pt-0.5">
                        <span>0 pt (E)</span>
                        <span>60 pt (D)</span>
                        <span>70 pt (C)</span>
                        <span>80 pt (B)</span>
                        <span>90 pt (A)</span>
                        <span>100 pt</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Kriteria Penilaian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📝</span> Form Pengisian Skor Kriteria
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Geser slider atau ketikkan angka (0 - 100). Kontribusi poin ke nilai akhir akan langsung terkalkulasi secara realtime.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                        Total Bobot: {{ $kriteriaList->sum('bobot') }}%
                    </span>
                </div>
            </div>

            <div class="p-6 divide-y divide-slate-100 space-y-6">
                @foreach($kriteriaList as $index => $kriteria)
                    @php
                        $isPresensi = $kriteria->is_presensi || ($presensiScore['kriteria_presensi'] && $kriteria->id === $presensiScore['kriteria_presensi']->id);
                        $initVal = $isPresensi ? $presensiScore['nilai_angka'] : old('nilai.' . $kriteria->id, 80);
                        $initContrib = round(($initVal * $kriteria->bobot) / 100, 2);
                    @endphp
                    <div class="pt-6 first:pt-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <label for="kriteria_{{ $kriteria->id }}" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full {{ $isPresensi ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700' }} text-xs font-black flex items-center justify-center">
                                        {{ $loop->iteration }}
                                    </span>
                                    {{ $kriteria->nama }}
                                </label>
                                @if($isPresensi)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Objektif (Presensi Otomatis)
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                    Bobot: <b>{{ $kriteria->bobot }}%</b>
                                </span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 shadow-xs">
                                    <span>Kontribusi:</span>
                                    <span id="contrib_{{ $kriteria->id }}" class="font-mono font-black">+{{ number_format($initContrib, 2) }}</span>
                                    <span class="text-[11px] text-emerald-600/80 font-normal">/ {{ $kriteria->bobot }} pt</span>
                                </span>
                            </div>
                        </div>

                        @if($isPresensi)
                            <!-- Card Rincian Objektif Presensi Digital -->
                            <div class="mb-4 bg-gradient-to-br from-emerald-50/80 via-teal-50/50 to-slate-50 p-4 rounded-2xl border border-emerald-200/90 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-200/60 pb-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            ⏱️
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-800">Kalkulasi Menit Kerja Presensi Digital</h4>
                                            <p class="text-[11px] text-slate-500">Nilai dihitung sistem secara otomatis & objektif dari rekaman presensi.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $presensiScore['badge_class'] }} border">
                                            Skor: {{ $presensiScore['skor_presensi'] }}% ({{ $presensiScore['predikat'] }})
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                    <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Target Jam Kerja</span>
                                        <span class="font-mono font-bold text-slate-800">{{ number_format($presensiScore['target_menit']) }} Menit</span>
                                        <span class="text-[10px] text-slate-400 block">({{ $presensiScore['target_hari'] }} hari kerja &times; 480m)</span>
                                    </div>
                                    <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Total Realisasi</span>
                                        <span class="font-mono font-bold text-emerald-700">{{ number_format($presensiScore['total_menit_realisasi']) }} Menit</span>
                                        <span class="text-[10px] text-emerald-600 block">Capaian: {{ $presensiScore['skor_presensi'] }}%</span>
                                    </div>
                                    <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Hadir Penuh / Izin</span>
                                        <span class="font-mono font-bold text-slate-700">{{ $presensiScore['total_hari_hadir'] }} Hadir &bull; {{ $presensiScore['total_hari_izin'] }} Izin</span>
                                        <span class="text-[10px] text-blue-600 block">Izin resmi disetujui (480m)</span>
                                    </div>
                                    <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Lupa Checkout / Telat</span>
                                        <span class="font-mono font-bold text-amber-700">{{ $presensiScore['total_hari_lupa_checkout'] }} Lupa (50%)</span>
                                        <span class="text-[10px] text-rose-500 block">-{{ $presensiScore['menit_terlambat_potong'] }}m ({{ $presensiScore['total_hari_terlambat'] }}x telat)</span>
                                    </div>
                                </div>

                                <!-- Collapsible Log Harian -->
                                <details class="text-xs group">
                                    <summary class="cursor-pointer font-semibold text-emerald-800 hover:text-emerald-900 select-none flex items-center gap-1.5 pt-1">
                                        <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                        <span>Lihat Rincian Log Harian ({{ count($presensiScore['rincian_harian']) }} catatan)</span>
                                    </summary>
                                    <div class="mt-2.5 max-h-48 overflow-y-auto border border-emerald-200/80 rounded-xl bg-white shadow-inner">
                                        <table class="w-full text-left text-[11px]">
                                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 sticky top-0 font-bold">
                                                <tr>
                                                    <th class="px-3 py-1.5">Tanggal</th>
                                                    <th class="px-2 py-1.5">Hari</th>
                                                    <th class="px-2 py-1.5 text-center">Masuk</th>
                                                    <th class="px-2 py-1.5 text-center">Keluar</th>
                                                    <th class="px-2 py-1.5 text-center">Menit</th>
                                                    <th class="px-3 py-1.5">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @forelse($presensiScore['rincian_harian'] as $rincian)
                                                    <tr class="hover:bg-slate-50">
                                                        <td class="px-3 py-1 font-mono text-slate-600">{{ \Carbon\Carbon::parse($rincian['tanggal'])->format('d/m/Y') }}</td>
                                                        <td class="px-2 py-1 text-slate-600">{{ $rincian['hari'] }}</td>
                                                        <td class="px-2 py-1 text-center font-mono font-medium">{{ $rincian['jam_masuk'] }}</td>
                                                        <td class="px-2 py-1 text-center font-mono font-medium">{{ $rincian['jam_keluar'] }}</td>
                                                        <td class="px-2 py-1 text-center font-mono font-bold {{ $rincian['menit'] > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                                            {{ $rincian['menit'] }}m
                                                        </td>
                                                        <td class="px-3 py-1 text-slate-600 truncate max-w-[200px]" title="{{ $rincian['keterangan'] }}">
                                                            {{ $rincian['keterangan'] }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="px-3 py-3 text-center text-slate-400">Belum ada riwayat presensi.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            </div>
                        @endif

                        <!-- Input Nilai & Slider -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                            <div class="sm:col-span-3 space-y-1.5">
                                <input type="range" min="0" max="100" step="1" 
                                    id="slider_{{ $kriteria->id }}" 
                                    value="{{ $initVal }}" 
                                    {{ $isPresensi ? 'disabled' : '' }}
                                    class="w-full h-2.5 {{ $isPresensi ? 'bg-emerald-200 cursor-not-allowed opacity-60 accent-emerald-600' : 'bg-slate-200 cursor-pointer accent-purple-600' }} rounded-lg appearance-none"
                                    oninput="syncScore({{ $kriteria->id }}, this.value)">
                                <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                    <span>0 (Tidak Baik)</span>
                                    <span>60 (Kurang Baik)</span>
                                    <span>70 (Cukup Baik)</span>
                                    <span>80 (Baik)</span>
                                    <span>90+ (Sangat Baik)</span>
                                    <span>100</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono pt-0.5">
                                    Formula: Skor (<span id="formula_score_{{ $kriteria->id }}" class="font-bold text-slate-700">{{ $initVal }}</span>) &times; Bobot ({{ $kriteria->bobot }}%) = <b id="formula_result_{{ $kriteria->id }}" class="text-emerald-700">+{{ number_format($initContrib, 2) }} poin</b>
                                </div>
                            </div>
                            <div class="sm:col-span-1">
                                <div class="relative">
                                    <input type="number" 
                                        name="nilai[{{ $kriteria->id }}]" 
                                        id="input_{{ $kriteria->id }}" 
                                        value="{{ $initVal }}" 
                                        min="0" max="100" required 
                                        {{ $isPresensi ? 'readonly' : '' }}
                                        data-id="{{ $kriteria->id }}"
                                        data-bobot="{{ $kriteria->bobot }}"
                                        class="score-input w-full text-center py-2.5 px-3 text-lg font-black rounded-xl border {{ $isPresensi ? 'border-emerald-300 text-emerald-800 bg-emerald-50/70 cursor-not-allowed shadow-inner' : 'border-slate-200 text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-purple-500 focus:border-transparent' }} transition shadow-xs"
                                        oninput="syncSlider({{ $kriteria->id }}, this.value); calculateTotal();">
                                </div>
                                @if($isPresensi)
                                    <p class="text-[11px] text-emerald-700 font-semibold flex items-center justify-center gap-1 mt-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                        <span>Terkunci Objektif</span>
                                    </p>
                                @endif
                                @error('nilai.' . $kriteria->id)
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rincian & Tabel Transparansi Kalkulasi Nilai Akhir -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="p-1 rounded-lg bg-purple-100 text-purple-700 text-base">🧮</span>
                        Rincian & Transparansi Kalkulasi Nilai Akhir
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tabel ini memperlihatkan akumulasi matematis dari setiap kriteria terhadap perolehan nilai akhir peserta magang.
                    </p>
                </div>
                <div class="text-xs bg-purple-50 text-purple-800 font-semibold px-3 py-1.5 rounded-xl border border-purple-200 shrink-0">
                    Rumus: <span class="font-mono font-bold">Nilai Akhir = &Sigma; (Skor &times; Bobot%)</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">No</th>
                            <th class="px-5 py-3.5">Kriteria Evaluasi</th>
                            <th class="px-5 py-3.5 text-center">Skor Input (0-100)</th>
                            <th class="px-5 py-3.5 text-center">Bobot Kriteria</th>
                            <th class="px-5 py-3.5 text-center">Perhitungan Terbobot</th>
                            <th class="px-5 py-3.5 text-right">Poin Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($kriteriaList as $k)
                            <tr class="hover:bg-purple-50/20 transition">
                                <td class="px-5 py-3 font-bold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-bold text-slate-800">{{ $k->nama }}</td>
                                <td class="px-5 py-3 text-center font-mono font-bold text-purple-700 text-sm">
                                    <span id="table_score_{{ $k->id }}">80</span>
                                </td>
                                <td class="px-5 py-3 text-center font-semibold text-slate-600">
                                    {{ $k->bobot }}%
                                </td>
                                <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-500">
                                    <span id="table_calc_{{ $k->id }}">80 &times; {{ $k->bobot }}%</span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600 text-sm">
                                    +<span id="table_contrib_{{ $k->id }}">0.00</span> pt
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/90 border-t-2 border-purple-200 text-xs font-bold text-slate-800">
                        <tr>
                            <td colspan="3" class="px-5 py-3.5 text-slate-700">Total Akumulasi Poin Terbobot:</td>
                            <td class="px-5 py-3.5 text-center text-purple-700 font-black">{{ $kriteriaList->sum('bobot') }}%</td>
                            <td class="px-5 py-3.5 text-center text-slate-500 font-mono text-[11px]">(Presisi Desimal)</td>
                            <td class="px-5 py-3.5 text-right font-mono text-base font-black text-purple-900">
                                <span id="tableTotalScore">0.00</span> pt
                            </td>
                        </tr>
                        <tr class="bg-purple-100/70 border-t border-purple-200">
                            <td colspan="4" class="px-5 py-3.5 text-purple-950 font-black text-sm">
                                Nilai Akhir Magang (Dibulatkan) & Predikat:
                            </td>
                            <td colspan="2" class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <span class="text-xs font-semibold text-purple-700">Nilai Akhir:</span>
                                    <span id="tableFinalScore" class="text-2xl font-black text-purple-950 font-mono">0</span>
                                    <span class="text-xs text-purple-500">/ 100</span>
                                    <span id="tablePredikatBadge" class="px-3 py-1 rounded-xl bg-purple-700 text-white font-black text-xs ml-1 shadow-xs">
                                        Predikat: -
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Opsi Status Magang & Konfirmasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Masa Magang</h3>
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" name="status_magang" value="selesai" checked class="mt-0.5 rounded border-slate-300 text-purple-600 focus:ring-purple-500 w-4 h-4">
                <div>
                    <span class="text-xs font-bold text-slate-800">Tandai masa magang peserta sebagai "Selesai"</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Mencentang opsi ini akan secara otomatis memperbarui status magang anak binaan menjadi telah menyelesaikan program magang.</p>
                </div>
            </label>
        </div>

        <!-- Card Konfirmasi & Ringkasan Nilai Akhir Bawah -->
        <div class="bg-slate-900 text-white p-5 sm:p-6 rounded-2xl shadow-lg border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-5">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 border border-purple-400/30 text-purple-300 flex items-center justify-center font-black text-3xl shrink-0 shadow-inner">
                    <span id="bottomPredikatLetter">-</span>
                </div>
                <div>
                    <div class="text-xs text-purple-300 font-semibold uppercase tracking-wider">Ringkasan Nilai Akhir Siap Disimpan:</div>
                    <div class="flex items-center gap-2.5 mt-0.5">
                        <span class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight"><span id="bottomTotalScore">0</span> <span class="text-sm font-normal text-slate-400">/ 100</span></span>
                        <span class="text-slate-500">&bull;</span>
                        <span id="bottomPredikatLabel" class="text-xs sm:text-sm font-bold text-amber-300">-</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('pembimbing.penilaian.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-semibold text-slate-300 hover:bg-slate-800 transition text-center">
                    Batal
                </a>
                <button type="submit" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-lg shadow-purple-600/30 transition cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>Simpan Penilaian Akhir</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    function syncScore(id, val) {
        document.getElementById('input_' + id).value = val;
        calculateTotal();
    }

    function syncSlider(id, val) {
        if (val > 100) val = 100;
        if (val < 0) val = 0;
        document.getElementById('slider_' + id).value = val;
        calculateTotal();
    }

    function calculateTotal() {
        const inputs = document.querySelectorAll('.score-input');
        let totalWeighted = 0;
        let totalBobot = 0;

        inputs.forEach(input => {
            const id = input.dataset.id;
            const score = parseFloat(input.value) || 0;
            const bobot = parseFloat(input.dataset.bobot) || 0;
            const contrib = (score * bobot) / 100;

            totalWeighted += (score * bobot);
            totalBobot += bobot;

            // Update baris kriteria
            const contribEl = document.getElementById('contrib_' + id);
            if (contribEl) contribEl.innerText = '+' + contrib.toFixed(2);

            const formulaScoreEl = document.getElementById('formula_score_' + id);
            if (formulaScoreEl) formulaScoreEl.innerText = score;

            const formulaResultEl = document.getElementById('formula_result_' + id);
            if (formulaResultEl) formulaResultEl.innerText = '+' + contrib.toFixed(2) + ' poin';

            // Update tabel rincian
            const tableScoreEl = document.getElementById('table_score_' + id);
            if (tableScoreEl) tableScoreEl.innerText = score;

            const tableCalcEl = document.getElementById('table_calc_' + id);
            if (tableCalcEl) tableCalcEl.innerHTML = `${score} &times; ${bobot}%`;

            const tableContribEl = document.getElementById('table_contrib_' + id);
            if (tableContribEl) tableContribEl.innerText = contrib.toFixed(2);
        });

        const rawScore = totalBobot > 0 ? (totalWeighted / totalBobot) : 0;
        const finalScore = Math.round(rawScore);

        // Update skor utama atas
        const liveScoreEl = document.getElementById('liveTotalScore');
        if (liveScoreEl) liveScoreEl.innerText = finalScore;

        const liveRawEl = document.getElementById('liveRawScore');
        if (liveRawEl) liveRawEl.innerText = rawScore.toFixed(2);

        const livePercentEl = document.getElementById('liveScorePercentText');
        if (livePercentEl) livePercentEl.innerText = finalScore + '%';

        // Update progress bar
        const progressBarEl = document.getElementById('liveScoreProgressBar');
        if (progressBarEl) progressBarEl.style.width = Math.min(100, Math.max(0, finalScore)) + '%';

        // Update tabel bawah
        const tableTotalScoreEl = document.getElementById('tableTotalScore');
        if (tableTotalScoreEl) tableTotalScoreEl.innerText = rawScore.toFixed(2);

        const tableFinalScoreEl = document.getElementById('tableFinalScore');
        if (tableFinalScoreEl) tableFinalScoreEl.innerText = finalScore;

        // Update card konfirmasi bawah
        const bottomScoreEl = document.getElementById('bottomTotalScore');
        if (bottomScoreEl) bottomScoreEl.innerText = finalScore;

        // Predikat
        let letter = 'E';
        let label = 'Tidak Baik (&lt; 60)';
        let badgeBg = 'bg-rose-600 text-white';
        let letterColor = 'text-rose-400';

        if (finalScore >= 90) {
            letter = 'A';
            label = 'Sangat Baik';
            badgeBg = 'bg-emerald-600 text-white';
            letterColor = 'text-emerald-400';
        } else if (finalScore >= 80) {
            letter = 'B';
            label = 'Baik';
            badgeBg = 'bg-blue-600 text-white';
            letterColor = 'text-blue-400';
        } else if (finalScore >= 70) {
            letter = 'C';
            label = 'Cukup Baik';
            badgeBg = 'bg-amber-600 text-white';
            letterColor = 'text-amber-400';
        } else if (finalScore >= 60) {
            letter = 'D';
            label = 'Kurang Baik';
            badgeBg = 'bg-orange-600 text-white';
            letterColor = 'text-orange-400';
        }

        const liveLetterEl = document.getElementById('livePredikatLetter');
        if (liveLetterEl) {
            liveLetterEl.innerText = letter;
            liveLetterEl.className = 'text-4xl sm:text-5xl font-black tracking-tight block ' + letterColor;
        }

        const liveLabelEl = document.getElementById('livePredikatLabel');
        if (liveLabelEl) liveLabelEl.innerHTML = label;

        const tableBadgeEl = document.getElementById('tablePredikatBadge');
        if (tableBadgeEl) {
            tableBadgeEl.className = 'px-3 py-1 rounded-xl font-black text-xs ml-1 shadow-xs ' + badgeBg;
            tableBadgeEl.innerText = 'Predikat ' + letter + ' (' + label.replace('&lt;', '<') + ')';
        }

        const bottomLetterEl = document.getElementById('bottomPredikatLetter');
        if (bottomLetterEl) bottomLetterEl.innerText = letter;

        const bottomLabelEl = document.getElementById('bottomPredikatLabel');
        if (bottomLabelEl) bottomLabelEl.innerHTML = label;
    }

    // Inisialisasi perhitungan saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
    });
</script>
@endsection
