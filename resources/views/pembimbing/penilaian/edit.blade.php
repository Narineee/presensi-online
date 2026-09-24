@extends('layouts.pembimbing')

@section('title', 'Edit Penilaian Akhir Magang')

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
        <span class="text-slate-800 font-semibold">Ubah Penilaian</span>
    </div>

    <!-- Header & Info Peserta -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between pb-6 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 font-black text-lg flex items-center justify-center overflow-hidden border border-purple-200 shrink-0">
                    @if($penilaian->magang->foto)
                        <img src="{{ asset('storage/' . $penilaian->magang->foto) }}" alt="{{ $penilaian->magang->nama_lengkap }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($penilaian->magang->nama_lengkap, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $penilaian->magang->nama_lengkap }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">NIS/NIM: {{ $penilaian->magang->no_induk }} &bull; {{ $penilaian->magang->instansi_pendidikan }}</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Divisi / Penempatan</div>
                <div class="text-sm font-bold text-purple-700">{{ $penilaian->magang->divisi->nama_divisi ?? '-' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
            <div>
                <span class="text-slate-400 block">Jurusan</span>
                <span class="font-semibold text-slate-700">{{ $penilaian->magang->jurusan ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Periode Mulai</span>
                <span class="font-semibold text-slate-700">{{ $penilaian->magang->tanggal_mulai ? $penilaian->magang->tanggal_mulai->format('d M Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Periode Selesai</span>
                <span class="font-semibold text-slate-700">{{ $penilaian->magang->tanggal_selesai ? $penilaian->magang->tanggal_selesai->format('d M Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Status Magang</span>
                <span class="font-semibold text-slate-700 capitalize">{{ $penilaian->magang->status }}</span>
            </div>
        </div>
    </div>

    <!-- Form Edit Penilaian -->
    <form action="{{ route('pembimbing.penilaian.update', $penilaian->id) }}" method="POST" id="formPenilaian" class="space-y-6">
        @csrf
        @method('PUT')

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
                            Perubahan pada skor kriteria di bawah akan otomatis memperbarui nilai total berbobot dan predikat kelulusan peserta.
                        </p>
                    </div>

                    <!-- Main Score Highlight Badges -->
                    <div class="flex items-center gap-5 bg-white/10 p-4 rounded-2xl border border-white/15 backdrop-blur-md shrink-0 w-full sm:w-auto justify-center">
                        <div class="text-center px-2">
                            <span class="text-[10px] uppercase tracking-wider text-purple-200 font-bold block">Skor Akhir</span>
                            <div class="flex items-baseline justify-center gap-1">
                                <span id="liveTotalScore" class="text-4xl sm:text-5xl font-black text-white tracking-tight">{{ $penilaian->total_nilai }}</span>
                                <span class="text-xs text-purple-300 font-semibold">/100</span>
                            </div>
                            <span class="text-[10px] text-purple-300/80 block mt-0.5 font-mono">Presisi: <span id="liveRawScore" class="font-bold text-purple-200">{{ number_format($penilaian->total_nilai, 2) }}</span></span>
                        </div>
                        <div class="h-12 w-px bg-white/20"></div>
                        <div class="text-center px-2">
                            <span class="text-[10px] uppercase tracking-wider text-purple-200 font-bold block">Predikat Mutu</span>
                            <span id="livePredikatLetter" class="text-4xl sm:text-5xl font-black text-amber-300 tracking-tight block">{{ $penilaian->predikat }}</span>
                            <span id="livePredikatLabel" class="text-[11px] text-purple-200 font-semibold block mt-0.5 max-w-[130px] truncate">{{ $penilaian->keterangan_predikat }}</span>
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
                            Capaian Saat Ini: <b id="liveScorePercentText" class="text-white">{{ $penilaian->total_nilai }}%</b>
                        </span>
                    </div>

                    <!-- Interactive Segmented Bar -->
                    <div class="grid grid-cols-12 h-3.5 rounded-full overflow-hidden border border-white/20 text-[9px] font-bold text-center">
                        <!-- D: 0-59 (7 cols = 58.3%) -->
                        <div class="col-span-7 bg-rose-500/50 flex items-center justify-center text-rose-200 border-r border-slate-900/50" title="D (Kurang): 0 - 59">
                            Kurang (&lt;60)
                        </div>
                        <!-- C: 60-74 (2 cols = 16.7%) -->
                        <div class="col-span-2 bg-amber-500/50 flex items-center justify-center text-amber-200 border-r border-slate-900/50" title="C (Cukup): 60 - 74">
                            Cukup (60-74)
                        </div>
                        <!-- B: 75-84 (1 col = 8.3%) -->
                        <div class="col-span-1 bg-blue-500/50 flex items-center justify-center text-blue-200 border-r border-slate-900/50" title="B (Baik): 75 - 84">
                            B
                        </div>
                        <!-- A: 85-100 (2 cols = 16.7%) -->
                        <div class="col-span-2 bg-emerald-500/60 flex items-center justify-center text-emerald-200" title="A (Sangat Baik): 85 - 100">
                            Sangat Baik (&ge;85)
                        </div>
                    </div>

                    <!-- Active Progress Fill Bar -->
                    <div class="h-2 bg-slate-800/80 rounded-full overflow-hidden border border-white/10">
                        <div id="liveScoreProgressBar" class="h-full bg-gradient-to-r from-purple-400 via-indigo-300 to-emerald-400 transition-all duration-300 rounded-full" style="width: {{ min(100, max(0, $penilaian->total_nilai)) }}%"></div>
                    </div>

                    <!-- Range Marker Labels -->
                    <div class="flex justify-between text-[10px] text-purple-300/70 font-mono font-medium pt-0.5">
                        <span>0 pt (D)</span>
                        <span class="pl-24">60 pt (C)</span>
                        <span class="pl-12">75 pt (B)</span>
                        <span class="pl-8">85 pt (A)</span>
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
                        <span>📝</span> Form Pengubahan Skor Kriteria
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
                        $currentScore = old('nilai.' . $kriteria->id, $nilaiMap[$kriteria->id] ?? 80);
                        $currentContrib = round(($currentScore * $kriteria->bobot) / 100, 2);
                    @endphp
                    <div class="pt-6 first:pt-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div>
                                <label for="kriteria_{{ $kriteria->id }}" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-black flex items-center justify-center">
                                        {{ $loop->iteration }}
                                    </span>
                                    {{ $kriteria->nama }}
                                </label>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                    Bobot: <b>{{ $kriteria->bobot }}%</b>
                                </span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 shadow-xs">
                                    <span>Kontribusi:</span>
                                    <span id="contrib_{{ $kriteria->id }}" class="font-mono font-black">+{{ number_format($currentContrib, 2) }}</span>
                                    <span class="text-[11px] text-emerald-600/80 font-normal">/ {{ $kriteria->bobot }} pt</span>
                                </span>
                            </div>
                        </div>

                        <!-- Input Nilai & Slider -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                            <div class="sm:col-span-3 space-y-1.5">
                                <input type="range" min="0" max="100" step="1" 
                                    id="slider_{{ $kriteria->id }}" 
                                    value="{{ $currentScore }}" 
                                    class="w-full h-2.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                                    oninput="syncScore({{ $kriteria->id }}, this.value)">
                                <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                                    <span>0 (Kurang)</span>
                                    <span>60 (Cukup)</span>
                                    <span>75 (Baik)</span>
                                    <span>85+ (Sangat Baik)</span>
                                    <span>100</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono pt-0.5">
                                    Formula: Skor (<span id="formula_score_{{ $kriteria->id }}" class="font-bold text-slate-700">{{ $currentScore }}</span>) &times; Bobot ({{ $kriteria->bobot }}%) = <b id="formula_result_{{ $kriteria->id }}" class="text-emerald-700">+{{ number_format($currentContrib, 2) }} poin</b>
                                </div>
                            </div>
                            <div class="sm:col-span-1">
                                <div class="relative">
                                    <input type="number" 
                                        name="nilai[{{ $kriteria->id }}]" 
                                        id="input_{{ $kriteria->id }}" 
                                        value="{{ $currentScore }}" 
                                        min="0" max="100" required 
                                        data-id="{{ $kriteria->id }}"
                                        data-bobot="{{ $kriteria->bobot }}"
                                        class="score-input w-full text-center py-2.5 px-3 text-lg font-black rounded-xl border border-slate-200 text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-purple-500 focus:border-transparent transition shadow-xs"
                                        oninput="syncSlider({{ $kriteria->id }}, this.value); calculateTotal();">
                                </div>
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
                            @php
                                $scoreInit = old('nilai.' . $k->id, $nilaiMap[$k->id] ?? 80);
                                $contribInit = round(($scoreInit * $k->bobot) / 100, 2);
                            @endphp
                            <tr class="hover:bg-purple-50/20 transition">
                                <td class="px-5 py-3 font-bold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-bold text-slate-800">{{ $k->nama }}</td>
                                <td class="px-5 py-3 text-center font-mono font-bold text-purple-700 text-sm">
                                    <span id="table_score_{{ $k->id }}">{{ $scoreInit }}</span>
                                </td>
                                <td class="px-5 py-3 text-center font-semibold text-slate-600">
                                    {{ $k->bobot }}%
                                </td>
                                <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-500">
                                    <span id="table_calc_{{ $k->id }}">{{ $scoreInit }} &times; {{ $k->bobot }}%</span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600 text-sm">
                                    +<span id="table_contrib_{{ $k->id }}">{{ number_format($contribInit, 2) }}</span> pt
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
                                <span id="tableTotalScore">{{ number_format($penilaian->total_nilai, 2) }}</span> pt
                            </td>
                        </tr>
                        <tr class="bg-purple-100/70 border-t border-purple-200">
                            <td colspan="4" class="px-5 py-3.5 text-purple-950 font-black text-sm">
                                Nilai Akhir Magang (Dibulatkan) & Predikat:
                            </td>
                            <td colspan="2" class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <span class="text-xs font-semibold text-purple-700">Nilai Akhir:</span>
                                    <span id="tableFinalScore" class="text-2xl font-black text-purple-950 font-mono">{{ $penilaian->total_nilai }}</span>
                                    <span class="text-xs text-purple-500">/ 100</span>
                                    <span id="tablePredikatBadge" class="px-3 py-1 rounded-xl bg-purple-700 text-white font-black text-xs ml-1 shadow-xs">
                                        Predikat {{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Opsi Status Magang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Masa Magang</h3>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status_magang" value="selesai" {{ $penilaian->magang->status === 'selesai' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                    <span class="text-xs font-bold text-slate-800">Selesai (Lulus)</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status_magang" value="aktif" {{ $penilaian->magang->status === 'aktif' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                    <span class="text-xs font-medium text-slate-700">Masih Aktif</span>
                </label>
            </div>
        </div>

        <!-- Card Konfirmasi & Ringkasan Nilai Akhir Bawah -->
        <div class="bg-slate-900 text-white p-5 sm:p-6 rounded-2xl shadow-lg border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-5">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 border border-purple-400/30 text-purple-300 flex items-center justify-center font-black text-3xl shrink-0 shadow-inner">
                    <span id="bottomPredikatLetter">{{ $penilaian->predikat }}</span>
                </div>
                <div>
                    <div class="text-xs text-purple-300 font-semibold uppercase tracking-wider">Ringkasan Nilai Akhir Siap Disimpan:</div>
                    <div class="flex items-center gap-2.5 mt-0.5">
                        <span class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight"><span id="bottomTotalScore">{{ $penilaian->total_nilai }}</span> <span class="text-sm font-normal text-slate-400">/ 100</span></span>
                        <span class="text-slate-500">&bull;</span>
                        <span id="bottomPredikatLabel" class="text-xs sm:text-sm font-bold text-amber-300">{{ $penilaian->keterangan_predikat }}</span>
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
                    <span>Perbarui Penilaian</span>
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
        let letter = 'D';
        let label = 'Kurang (&lt; 60)';
        let badgeBg = 'bg-rose-600 text-white';
        let letterColor = 'text-rose-400';

        if (finalScore >= 85) {
            letter = 'A';
            label = 'Sangat Baik (Memuaskan)';
            badgeBg = 'bg-emerald-600 text-white';
            letterColor = 'text-emerald-400';
        } else if (finalScore >= 75) {
            letter = 'B';
            label = 'Baik';
            badgeBg = 'bg-blue-600 text-white';
            letterColor = 'text-blue-400';
        } else if (finalScore >= 60) {
            letter = 'C';
            label = 'Cukup';
            badgeBg = 'bg-amber-600 text-white';
            letterColor = 'text-amber-400';
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
