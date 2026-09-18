@extends('layouts.user')

@section('title', 'Catat Aktivitas Harian')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Aktivitas Harian</h1>
            <p class="text-sm text-slate-500 mt-1">Tuliskan rincian tugas yang Anda kerjakan hari ini untuk divalidasi oleh Pembimbing.</p>
        </div>
        <a href="{{ route('aktivitas.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form action="{{ route('aktivitas.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Tanggal Aktivitas -->
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tanggal Aktivitas <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ old('tanggal', date('Y-m-d')) }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                >
                @error('tanggal')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Uraian Aktivitas Pekerjaan -->
            <div>
                <label for="isi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Uraian Pekerjaan / Tugas yang Dikerjakan <span class="text-rose-500">*</span>
                </label>
                <textarea
                    name="isi"
                    id="isi"
                    rows="6"
                    placeholder="Contoh:&#10;1. Melayani 15 pertanyaan pelanggan terkait aktivasi layanan baru&#10;2. Menyelesaikan laporan harian monitoring data&#10;3. Melakukan koordinasi dengan tim teknis terkait kendala pelanggan"
                    required
                    class="w-full px-4 py-3 rounded-xl border {{ $errors->has('isi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-normal text-slate-800 leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                >{{ old('isi') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Uraikan tugas atau progres yang dicapai secara rinci agar mempermudah pembimbing mengevaluasi kinerja harian Anda.</p>
                @error('isi')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Persentase Progres Pekerjaan -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="progress" class="block text-xs font-semibold text-slate-700">
                        Progres Capaian Hari Ini <span class="text-rose-500">*</span>
                    </label>
                    <span id="progress-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        100% Selesai
                    </span>
                </div>
                
                <div class="space-y-3">
                    <input
                        type="range"
                        name="progress"
                        id="progress"
                        min="0"
                        max="100"
                        step="5"
                        value="{{ old('progress', 100) }}"
                        class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                    >
                    <div class="flex justify-between text-[11px] font-semibold text-slate-400">
                        <span>0% (Baru Mulai)</span>
                        <span>50% (Sedang Berjalan)</span>
                        <span>100% (Tuntas)</span>
                    </div>
                </div>
                @error('progress')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Informasi PRD -->
            <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div class="text-xs text-blue-900 leading-relaxed">
                    <span class="font-bold">Alur Validasi:</span> Aktivitas ini akan dikirimkan ke pembimbing Anda dengan status <em>Pending</em>. Pembimbing dapat menyetujui langsung atau memberikan catatan revisi jika ada hal yang perlu dilengkapi.
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('aktivitas.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Simpan Aktivitas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('progress');
    const badge = document.getElementById('progress-badge');

    function updateBadge(val) {
        if (val == 100) {
            badge.textContent = `${val}% (Tuntas)`;
            badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
        } else if (val >= 50) {
            badge.textContent = `${val}% (Sedang Berjalan)`;
            badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200';
        } else {
            badge.textContent = `${val}% (Tahap Awal)`;
            badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200';
        }
    }

    if (slider && badge) {
        updateBadge(slider.value);
        slider.addEventListener('input', function() {
            updateBadge(this.value);
        });
    }
});
</script>
@endsection
