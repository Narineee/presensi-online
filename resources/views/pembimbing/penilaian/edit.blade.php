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
        <div class="bg-linear-to-r from-purple-700 to-indigo-700 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-200">Kalkulasi Nilai Akhir (Live Preview)</span>
                <h3 class="text-lg font-bold mt-0.5">Nilai Terbobot Otomatis</h3>
                <p class="text-xs text-purple-100/80 mt-1 max-w-md">Perubahan pada skor di bawah akan otomatis memperbarui nilai total dan predikat kelulusan.</p>
            </div>
            <div class="flex items-center gap-6 bg-white/10 px-6 py-4 rounded-xl border border-white/20 backdrop-blur-xs">
                <div class="text-center">
                    <span class="text-[11px] uppercase tracking-wider text-purple-200 font-semibold block">Skor Akhir</span>
                    <span id="liveTotalScore" class="text-4xl font-black">{{ $penilaian->total_nilai }}</span>
                    <span class="text-[11px] text-purple-200 block">/ 100</span>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <span class="text-[11px] uppercase tracking-wider text-purple-200 font-semibold block">Predikat</span>
                    <span id="livePredikatLetter" class="text-3xl font-black">{{ $penilaian->predikat }}</span>
                    <span id="livePredikatLabel" class="text-[10px] text-purple-200 block mt-0.5">{{ $penilaian->keterangan_predikat }}</span>
                </div>
            </div>
        </div>

        <!-- Daftar Kriteria Penilaian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Form Pengubahan Skor Kriteria</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sesuaikan skor antara 0 sampai 100 untuk masing-masing kriteria evaluasi.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                        Total Bobot: {{ $kriteriaList->sum('bobot') }}%
                    </span>
                </div>
            </div>

            <div class="p-6 divide-y divide-slate-100 space-y-6">
                @foreach($kriteriaList as $index => $kriteria)
                    @php
                        $currentScore = old('nilai.' . $kriteria->id, $nilaiMap[$kriteria->id] ?? 80);
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
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200">
                                    Bobot: {{ $kriteria->bobot }}%
                                </span>
                            </div>
                        </div>

                        <!-- Input Nilai & Slider -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                            <div class="sm:col-span-3">
                                <input type="range" min="0" max="100" step="1" 
                                    id="slider_{{ $kriteria->id }}" 
                                    value="{{ $currentScore }}" 
                                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                                    oninput="syncScore({{ $kriteria->id }}, this.value)">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-medium">
                                    <span>0 (Kurang)</span>
                                    <span>60 (Cukup)</span>
                                    <span>75 (Baik)</span>
                                    <span>85+ (Sangat Baik)</span>
                                    <span>100</span>
                                </div>
                            </div>
                            <div class="sm:col-span-1">
                                <div class="relative">
                                    <input type="number" 
                                        name="nilai[{{ $kriteria->id }}]" 
                                        id="input_{{ $kriteria->id }}" 
                                        value="{{ $currentScore }}" 
                                        min="0" max="100" required 
                                        data-bobot="{{ $kriteria->bobot }}"
                                        class="score-input w-full text-center py-2 px-3 text-base font-black rounded-xl border border-slate-200 text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
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

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pembimbing.penilaian.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-500/20 transition cursor-pointer">
                Perbarui Penilaian
            </button>
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
    }

    function calculateTotal() {
        const inputs = document.querySelectorAll('.score-input');
        let totalWeighted = 0;
        let totalBobot = 0;

        inputs.forEach(input => {
            const score = parseFloat(input.value) || 0;
            const bobot = parseFloat(input.dataset.bobot) || 0;
            totalWeighted += (score * bobot);
            totalBobot += bobot;
        });

        let finalScore = 0;
        if (totalBobot > 0) {
            finalScore = Math.round(totalWeighted / totalBobot);
        }

        document.getElementById('liveTotalScore').innerText = finalScore;

        let letter = 'D';
        let label = 'Kurang';

        if (finalScore >= 85) {
            letter = 'A';
            label = 'Sangat Baik (Memuaskan)';
        } else if (finalScore >= 75) {
            letter = 'B';
            label = 'Baik';
        } else if (finalScore >= 60) {
            letter = 'C';
            label = 'Cukup';
        }

        document.getElementById('livePredikatLetter').innerText = letter;
        document.getElementById('livePredikatLabel').innerText = label;
    }

    // Hitung saat pertama kali halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
    });
</script>
@endsection
