@extends('layouts.pembimbing-mobile')

@section('title', 'Input Penilaian - ' . $magang->nama_lengkap)

@section('content')
@php
    $in = 'w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25 transition';
@endphp

<!-- Header Sticky -->
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 pb-3 pt-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.penilaian.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" aria-label="Kembali">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold leading-tight text-slate-900 truncate">Input Penilaian Magang</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $magang->nama_lengkap }} ({{ $magang->no_induk ?? '-' }})</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    <!-- Card Profil Peserta Magang -->
    <div class="rounded-3xl bg-white p-4 ring-1 ring-slate-200 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 shrink-0 rounded-full border border-slate-300 bg-brand-soft text-brand flex items-center justify-center font-black text-sm overflow-hidden">
                @if($magang->pengguna && $magang->pengguna->foto_url)
                    <img src="{{ $magang->pengguna->foto_url }}" alt="{{ $magang->nama_lengkap }}" class="h-full w-full object-cover">
                @elseif($magang->foto)
                    <img src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr($magang->nama_lengkap, 0, 2)) }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-extrabold text-slate-900 truncate">{{ $magang->nama_lengkap }}</h2>
                <p class="text-xs text-slate-500 truncate">{{ $magang->no_induk ? 'NIS/NIM: ' . $magang->no_induk . ' • ' : '' }}{{ $magang->instansi_pendidikan ?? '-' }}</p>
            </div>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-extrabold {{ $magang->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                {{ ucfirst($magang->status) }}
            </span>
        </div>

        <dl class="mt-3.5 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-xs">
            <div>
                <dt class="text-slate-500">Jurusan</dt>
                <dd class="font-bold text-slate-800 truncate">{{ $magang->jurusan ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Periode Magang</dt>
                <dd class="font-bold text-slate-800 truncate">
                    {{ $magang->tanggal_mulai ? $magang->tanggal_mulai->format('d/m/Y') : '-' }} - {{ $magang->tanggal_selesai ? $magang->tanggal_selesai->format('d/m/Y') : '-' }}
                </dd>
            </div>
        </dl>

        @if($penempatan)
            <div class="mt-3 rounded-2xl bg-brand-soft p-3 text-xs border border-blue-200/60">
                <span class="font-extrabold text-brand block">Unit Penempatan Dinilai: {{ $penempatan->divisi?->nama_divisi ?? ($magang->divisi->nama_divisi ?? '-') }}</span>
                <span class="text-slate-600 font-medium text-[11px]">
                    Periode: {{ $penempatan->tanggal_mulai?->format('d M Y') }} s/d {{ $penempatan->tanggal_selesai?->format('d M Y') }}
                </span>
            </div>
        @endif
    </div>

    <!-- Form Penilaian -->
    <form action="{{ route('pembimbing.penilaian.store') }}" method="POST" id="formPenilaian" class="space-y-4">
        @csrf
        <input type="hidden" name="magang_id" value="{{ $magang->id }}">
        @if(isset($penempatan) && $penempatan)
            <input type="hidden" name="penempatan_magang_id" value="{{ $penempatan->id }}">
        @endif

        <!-- Live Score Summary Card -->
        <div class="rounded-3xl bg-brand p-5 text-white shadow-md shadow-brand/20 relative overflow-hidden">
            <div class="flex items-center justify-between gap-2 border-b border-white/15 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-blue-300 animate-pulse"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-white/90">Kalkulasi Nilai Akhir</span>
                </div>
                <span class="text-[11px] font-medium text-blue-200">Total Bobot: {{ $kriteriaList->sum('bobot') }}%</span>
            </div>

            <div class="mt-4 flex items-center justify-between gap-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-white/70 block">Skor Akhir</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span id="liveTotalScore" class="text-4xl sm:text-5xl font-black tracking-tight text-white">0</span>
                        <span class="text-xs font-semibold text-white/70">/ 100</span>
                    </div>
                    <span class="text-[10px] text-white/60 block mt-0.5 font-mono">Presisi: <span id="liveRawScore" class="font-semibold text-white/90">0.00</span></span>
                </div>

                <div class="h-12 w-px bg-white/20"></div>

                <div class="text-right">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-white/70 block">Predikat Mutu</span>
                    <span id="livePredikatLetter" class="text-4xl sm:text-5xl font-black text-amber-300 tracking-tight block">-</span>
                    <span id="livePredikatLabel" class="text-xs font-bold text-white/90 block mt-0.5 max-w-[130px] truncate">-</span>
                </div>
            </div>

            <!-- Segmented Scale / Meteran Nilai -->
            <div class="mt-4 space-y-1.5 pt-3 border-t border-white/15">
                <div class="flex items-center justify-between text-[11px] text-white/80 font-medium">
                    <span>Skala Predikat</span>
                    <span class="font-mono">Capaian: <b id="liveScorePercentText" class="text-white">0%</b></span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-black/25">
                    <div id="liveScoreProgressBar" class="h-full rounded-full bg-gradient-to-r from-brand-light via-indigo-400 to-amber-300 transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-[10px] text-white/60 font-medium pt-0.5">
                    <span>&lt;60 (E)</span>
                    <span>60-69 (D)</span>
                    <span>70-79 (C)</span>
                    <span>80-89 (B)</span>
                    <span>&ge;90 (A)</span>
                </div>
            </div>
        </div>

        <!-- Daftar Kriteria Penilaian -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900">Form Skor Kriteria</h2>
                <span class="text-xs font-bold text-slate-500">{{ count($kriteriaList) }} Kriteria</span>
            </div>

            @foreach($kriteriaList as $index => $kriteria)
                @php
                    $isPresensi = $kriteria->is_presensi || ($presensiScore['kriteria_presensi'] && $kriteria->id === $presensiScore['kriteria_presensi']->id);
                    $initVal = $isPresensi ? $presensiScore['nilai_angka'] : old('nilai.' . $kriteria->id, 80);
                    $initContrib = round(($initVal * $kriteria->bobot) / 100, 2);
                @endphp

                <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full {{ $isPresensi ? 'bg-blue-100 text-brand' : 'bg-brand-soft text-brand' }} text-xs font-black">
                                    {{ $loop->iteration }}
                                </span>
                                <label for="input_{{ $kriteria->id }}" class="text-sm font-extrabold text-slate-900 truncate">
                                    {{ $kriteria->nama }}
                                </label>
                            </div>
                            @if($isPresensi)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-bold text-brand border border-blue-200">
                                        <svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Objektif (Presensi Otomatis)
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-right space-y-0.5">
                            <span class="inline-block rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                Bobot: {{ $kriteria->bobot }}%
                            </span>
                            <p class="text-[11px] font-bold text-brand">
                                Kontribusi: <span id="contrib_{{ $kriteria->id }}">+{{ number_format($initContrib, 2) }}</span> pt
                            </p>
                        </div>
                    </div>

                    @if($isPresensi)
                        <!-- Card Rincian Objektif Presensi Digital -->
                        <div class="rounded-xl bg-brand-soft p-3 text-xs border border-blue-200/70 space-y-2.5">
                            <div class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <span>⏱️</span>
                                    <span>Kalkulasi Menit Presensi Digital</span>
                                </div>
                                <span class="rounded-md {{ $presensiScore['badge_class'] }} px-2 py-0.5 text-[11px] font-extrabold border">
                                    {{ $presensiScore['skor_presensi'] }}% ({{ $presensiScore['predikat'] }})
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <div class="bg-white/80 p-2 rounded-lg border border-blue-100">
                                    <span class="text-slate-500 block text-[10px]">Target Menit</span>
                                    <span class="font-bold text-slate-800">{{ number_format($presensiScore['target_menit']) }} m</span>
                                    <span class="text-[9px] text-slate-400 block">({{ $presensiScore['target_hari'] }} hari kerja)</span>
                                </div>
                                <div class="bg-white/80 p-2 rounded-lg border border-blue-100">
                                    <span class="text-slate-500 block text-[10px]">Realisasi</span>
                                    <span class="font-bold text-brand">{{ number_format($presensiScore['total_menit_realisasi']) }} m</span>
                                    <span class="text-[9px] text-brand-light block font-semibold">Capaian: {{ $presensiScore['skor_presensi'] }}%</span>
                                </div>
                                <div class="bg-white/80 p-2 rounded-lg border border-blue-100">
                                    <span class="text-slate-500 block text-[10px]">Hadir / Izin</span>
                                    <span class="font-bold text-slate-800">{{ $presensiScore['total_hari_hadir'] }} Hadir • {{ $presensiScore['total_hari_izin'] }} Izin</span>
                                </div>
                                <div class="bg-white/80 p-2 rounded-lg border border-blue-100">
                                    <span class="text-slate-500 block text-[10px]">Lupa CO / Telat</span>
                                    <span class="font-bold text-amber-800">{{ $presensiScore['total_hari_lupa_checkout'] }} Lupa • {{ $presensiScore['total_hari_terlambat'] }} Telat</span>
                                </div>
                            </div>

                            <!-- Collapsible Rincian Log Harian -->
                            <details class="text-[11px] group">
                                <summary class="cursor-pointer font-bold text-brand hover:underline select-none flex items-center gap-1 pt-1">
                                    <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                    <span>Lihat Rincian Log Presensi ({{ count($presensiScore['rincian_harian']) }} data)</span>
                                </summary>
                                <div class="mt-2 max-h-40 overflow-y-auto rounded-lg border border-blue-200 bg-white">
                                    <table class="w-full text-left text-[10px]">
                                        <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 font-bold text-slate-600">
                                            <tr>
                                                <th class="px-2 py-1">Tgl</th>
                                                <th class="px-1 py-1 text-center">In</th>
                                                <th class="px-1 py-1 text-center">Out</th>
                                                <th class="px-1 py-1 text-center">Mnt</th>
                                                <th class="px-2 py-1">Ket</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($presensiScore['rincian_harian'] as $rincian)
                                                <tr>
                                                    <td class="px-2 py-1 font-mono">{{ \Carbon\Carbon::parse($rincian['tanggal'])->format('d/m') }}</td>
                                                    <td class="px-1 py-1 text-center font-mono">{{ $rincian['jam_masuk'] }}</td>
                                                    <td class="px-1 py-1 text-center font-mono">{{ $rincian['jam_keluar'] }}</td>
                                                    <td class="px-1 py-1 text-center font-mono font-bold {{ $rincian['menit'] > 0 ? 'text-brand' : 'text-slate-400' }}">{{ $rincian['menit'] }}m</td>
                                                    <td class="px-2 py-1 truncate max-w-[120px]">{{ $rincian['keterangan'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="p-2 text-center text-slate-400">Tidak ada riwayat.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                        <div class="sm:col-span-3 space-y-1.5">
                            <input type="range" min="0" max="100" step="1" 
                                id="slider_{{ $kriteria->id }}" 
                                value="{{ $initVal }}" 
                                {{ $isPresensi ? 'disabled' : '' }}
                                class="w-full h-2 {{ $isPresensi ? 'bg-blue-200 cursor-not-allowed opacity-60 accent-brand' : 'bg-slate-200 cursor-pointer accent-brand' }} rounded-lg appearance-none"
                                oninput="syncScore({{ $kriteria->id }}, this.value)">
                            <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                <span>0 (E)</span>
                                <span>60 (D)</span>
                                <span>70 (C)</span>
                                <span>80 (B)</span>
                                <span>90+ (A)</span>
                                <span>100</span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                Skor (<span id="formula_score_{{ $kriteria->id }}" class="font-bold text-slate-700">{{ $initVal }}</span>) &times; {{ $kriteria->bobot }}% = <b id="formula_result_{{ $kriteria->id }}" class="text-brand">+{{ number_format($initContrib, 2) }} poin</b>
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
                                    class="score-input w-full rounded-xl border {{ $isPresensi ? 'border-blue-300 bg-brand-soft text-brand' : 'border-slate-300 bg-white text-slate-800 focus:border-brand focus:ring-2 focus:ring-brand/25' }} py-2 text-center text-lg font-black shadow-xs transition"
                                    oninput="syncSlider({{ $kriteria->id }}, this.value); calculateTotal();">
                            </div>
                            @if($isPresensi)
                                <p class="text-[10px] text-brand font-bold text-center mt-1">
                                    Terkunci (Sistem)
                                </p>
                            @endif
                            @error('nilai.' . $kriteria->id)
                                <p class="text-rose-500 text-[11px] mt-1 text-center">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Rincian & Tabel Transparansi Kalkulasi Nilai Akhir -->
        <div class="rounded-2xl border border-slate-300 bg-white overflow-hidden shadow-xs">
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Transparansi Kalkulasi</h3>
                <span class="text-[11px] font-bold text-brand">Rumus: &Sigma; (Skor &times; Bobot%)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-600">
                        <tr>
                            <th class="px-3 py-2">Kriteria</th>
                            <th class="px-2 py-2 text-center">Skor</th>
                            <th class="px-2 py-2 text-center">Bobot</th>
                            <th class="px-3 py-2 text-right">Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($kriteriaList as $k)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-800 truncate max-w-[150px]">{{ $k->nama }}</td>
                                <td class="px-2 py-2 text-center font-mono font-bold text-slate-800">
                                    <span id="table_score_{{ $k->id }}">80</span>
                                </td>
                                <td class="px-2 py-2 text-center text-slate-500">{{ $k->bobot }}%</td>
                                <td class="px-3 py-2 text-right font-mono font-bold text-brand">
                                    +<span id="table_contrib_{{ $k->id }}">0.00</span>
                                </td>
                                <span id="table_calc_{{ $k->id }}" class="hidden"></span>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-xs">
                        <tr>
                            <td colspan="2" class="px-3 py-2.5 text-slate-800">Total Akumulasi:</td>
                            <td class="px-2 py-2.5 text-center text-slate-600">{{ $kriteriaList->sum('bobot') }}%</td>
                            <td class="px-3 py-2.5 text-right font-mono font-black text-brand text-sm">
                                <span id="tableTotalScore">0.00</span> pt
                            </td>
                        </tr>
                        <tr class="bg-brand-soft/60 border-t border-indigo-100">
                            <td colspan="2" class="px-3 py-2.5 text-brand font-extrabold">Nilai Akhir &amp; Predikat:</td>
                            <td colspan="2" class="px-3 py-2.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <span id="tableFinalScore" class="font-mono font-black text-slate-900 text-base">0</span>
                                    <span class="text-[11px] text-slate-500">/100</span>
                                    <span id="tablePredikatBadge" class="rounded-lg bg-brand px-2 py-0.5 text-xs font-bold text-white shadow-2xs">
                                        Predikat: -
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Input Hasil Karya Peserta Magang (Opsional - Point per Point) -->
        <div x-data="{ points: {{ Js::from(old('hasil_karya', [''])) }} }" class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-lg bg-brand-soft text-brand flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900">Hasil Karya Peserta Magang</h3>
                        <p class="text-[11px] text-slate-500">Tuliskan karya atau proyek yang dihasilkan peserta selama magang.</p>
                    </div>
                </div>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">
                    Opsional
                </span>
            </div>

            <div class="space-y-2">
                <template x-for="(point, index) in points" :key="index">
                    <div class="flex items-center gap-2">
                        <span class="h-9 w-9 shrink-0 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center font-mono" x-text="(index + 1) + '.'"></span>
                        <input type="text" 
                               name="hasil_karya[]" 
                               x-model="points[index]" 
                               placeholder="Contoh: Modul Laporan Presensi Digital berbasis Web" 
                               class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25 transition">
                        <button type="button" 
                                @click="points.splice(index, 1)" 
                                class="h-9 w-9 shrink-0 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition" 
                                title="Hapus poin">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <div x-show="points.length === 0" class="text-center py-3 px-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-slate-400 text-xs">
                    Belum ada poin hasil karya yang ditambahkan.
                </div>
            </div>

            <div class="pt-1">
                <button type="button" 
                        @click="points.push('')" 
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-brand/30 bg-brand-soft text-brand text-xs font-bold hover:bg-brand/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Tambah Poin Hasil Karya</span>
                </button>
            </div>
        </div>

        <!-- Opsi Status Magang & Konfirmasi -->
        <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-xs">
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" name="status_magang" value="selesai" checked class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                <div>
                    <span class="text-xs font-extrabold text-slate-900">Tandai masa magang peserta sebagai "Selesai"</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Otomatis mengubah status magang peserta menjadi telah selesai program magang.</p>
                </div>
            </label>
        </div>

        <!-- Card Konfirmasi & Tombol Simpan Bawah -->
        <div class="rounded-2xl bg-slate-900 text-white p-4 shadow-md space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Ringkasan Nilai</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span id="bottomTotalScore" class="text-2xl font-black text-white font-mono">0</span>
                        <span class="text-xs text-slate-400">/ 100</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Predikat Mutu</span>
                    <div class="flex items-center gap-1.5 justify-end mt-0.5">
                        <span id="bottomPredikatLetter" class="text-2xl font-black text-amber-300">-</span>
                        <span id="bottomPredikatLabel" class="text-xs font-bold text-slate-200">(-)</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800">
                <a href="{{ route('pembimbing.penilaian.index') }}" class="rounded-xl border border-slate-700 bg-slate-800 py-2.5 text-center text-xs font-bold text-slate-300 hover:bg-slate-700 transition">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-brand hover:bg-brand-dark py-2.5 text-center text-xs font-extrabold text-white shadow-xs transition active:scale-[.99] flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>Simpan Nilai</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
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
        let label = 'Tidak Baik (< 60)';
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
            badgeBg = 'bg-teal-600 text-white';
            letterColor = 'text-teal-400';
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
        }

        const liveLabelEl = document.getElementById('livePredikatLabel');
        if (liveLabelEl) liveLabelEl.innerHTML = label;

        const tableBadgeEl = document.getElementById('tablePredikatBadge');
        if (tableBadgeEl) {
            tableBadgeEl.className = 'rounded-lg px-2 py-0.5 text-xs font-bold shadow-2xs ' + badgeBg;
            tableBadgeEl.innerText = 'Predikat ' + letter + ' (' + label + ')';
        }

        const bottomLetterEl = document.getElementById('bottomPredikatLetter');
        if (bottomLetterEl) bottomLetterEl.innerText = letter;

        const bottomLabelEl = document.getElementById('bottomPredikatLabel');
        if (bottomLabelEl) bottomLabelEl.innerHTML = '(' + label + ')';
    }

    // Inisialisasi perhitungan saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
    });
</script>
@endpush
