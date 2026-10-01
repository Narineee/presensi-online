@extends('layouts.pembimbing')

@section('title', 'Edit Pekerjaan Binaan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Pekerjaan Binaan</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi tugas atau status pengerjaan peserta magang.</p>
        </div>
        <a href="{{ route('pembimbing.pekerjaan.show', $pekerjaan->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form action="{{ route('pembimbing.pekerjaan.update', $pekerjaan->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Peserta Magang Penerima -->
            <div>
                <label for="magang_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Peserta Magang Penerima Tugas <span class="text-rose-500">*</span>
                </label>
                <select
                    name="magang_id"
                    id="magang_id"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('magang_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                >
                    @foreach($supervisedMagang as $magang)
                        <option value="{{ $magang->id }}" {{ (old('magang_id', $pekerjaan->magang_id) == $magang->id) ? 'selected' : '' }}>
                            {{ $magang->nama_lengkap }} &bull; {{ $magang->no_induk ? 'NIM: ' . $magang->no_induk : 'Tanpa NIM' }} ({{ $magang->divisi->nama_divisi ?? 'Umum' }})
                        </option>
                    @endforeach
                </select>
                @error('magang_id')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Pekerjaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Jenis Pekerjaan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start p-3.5 rounded-2xl border-2 {{ old('jenis', $pekerjaan->jenis) === 'proyek' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200' }} hover:border-purple-400 cursor-pointer transition has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/40" id="label-jenis-proyek">
                        <input type="radio" name="jenis" value="proyek" class="sr-only" id="jenis-proyek" {{ old('jenis', $pekerjaan->jenis) === 'proyek' ? 'checked' : '' }} onchange="toggleJenisPekerjaan('proyek')">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">🚀</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Proyek (Memiliki Target &amp; Progress)</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                    Memiliki tahapan progress (0% - 100%).
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start p-3.5 rounded-2xl border-2 {{ old('jenis', $pekerjaan->jenis) === 'rutin' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200' }} hover:border-purple-400 cursor-pointer transition has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/40" id="label-jenis-rutin">
                        <input type="radio" name="jenis" value="rutin" class="sr-only" id="jenis-rutin" {{ old('jenis', $pekerjaan->jenis) === 'rutin' ? 'checked' : '' }} onchange="toggleJenisPekerjaan('rutin')">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">📋</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Rutin (Pekerjaan Berulang)</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                    Pekerjaan operasional harian berulang tanpa progres persentase.
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('jenis')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Pekerjaan -->
            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Status Pekerjaan <span class="text-rose-500">*</span>
                </label>
                <select
                    name="status"
                    id="status"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('status') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                >
                    <option value="aktif" {{ old('status', $pekerjaan->status) === 'aktif' ? 'selected' : '' }}>Aktif (Peserta dapat memilih tugas ini)</option>
                    <option value="selesai" {{ old('status', $pekerjaan->status) === 'selesai' ? 'selected' : '' }}>Selesai (Tugas tuntas 100%)</option>
                    <option value="nonaktif" {{ old('status', $pekerjaan->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif (Ditutup sementara)</option>
                </select>
                @error('status')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Judul Pekerjaan -->
            <div>
                <label for="judul" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Judul Pekerjaan / Tugas <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="judul"
                    id="judul"
                    value="{{ old('judul', $pekerjaan->judul) }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('judul') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                >
                @error('judul')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="deskripsi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Deskripsi / Petunjuk Tugas (Opsional)
                </label>
                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    rows="3"
                    class="w-full px-4 py-3 rounded-xl border {{ $errors->has('deskripsi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                >{{ old('deskripsi', $pekerjaan->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Mulai
                    </label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        value="{{ old('tanggal_mulai', $pekerjaan->tanggal_mulai ? $pekerjaan->tanggal_mulai->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_mulai') ? 'border-rose-400' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                    >
                    @error('tanggal_mulai')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Target Selesai
                    </label>
                    <input
                        type="date"
                        name="target_selesai"
                        id="target_selesai"
                        value="{{ old('target_selesai', $pekerjaan->target_selesai ? $pekerjaan->target_selesai->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('target_selesai') ? 'border-rose-400' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                    >
                    @error('target_selesai')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Progress (Khusus Proyek) -->
            <div id="wrapper-progress" class="p-4 rounded-2xl bg-purple-50/60 border border-purple-100 space-y-3">
                <div class="flex items-center justify-between">
                    <label for="progress" class="block text-xs font-bold text-purple-900 uppercase tracking-wider">
                        Progress Saat Ini (Khusus Proyek)
                    </label>
                    <span id="badge-progress" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-600 text-white font-mono">
                        {{ old('progress', $pekerjaan->progress ?? 0) }}%
                    </span>
                </div>
                <input
                    type="range"
                    name="progress"
                    id="progress"
                    min="0"
                    max="100"
                    step="5"
                    value="{{ old('progress', $pekerjaan->progress ?? 0) }}"
                    class="w-full h-2 bg-purple-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                    oninput="document.getElementById('badge-progress').textContent = this.value + '%'"
                >
                <div class="flex justify-between text-[11px] font-semibold text-purple-700">
                    <span>0% (Awal)</span>
                    <span>50% (Sedang Berjalan)</span>
                    <span>100% (Tuntas Selesai)</span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('pembimbing.pekerjaan.show', $pekerjaan->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
function toggleJenisPekerjaan(jenis) {
    const wrapperProgress = document.getElementById('wrapper-progress');
    const labelProyek = document.getElementById('label-jenis-proyek');
    const labelRutin = document.getElementById('label-jenis-rutin');

    if (jenis === 'rutin') {
        wrapperProgress.classList.add('hidden');
        labelRutin.classList.add('border-purple-600', 'bg-purple-50/40');
        labelProyek.classList.remove('border-purple-600', 'bg-purple-50/40');
    } else {
        wrapperProgress.classList.remove('hidden');
        labelProyek.classList.add('border-purple-600', 'bg-purple-50/40');
        labelRutin.classList.remove('border-purple-600', 'bg-purple-50/40');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const isRutin = document.getElementById('jenis-rutin').checked;
    toggleJenisPekerjaan(isRutin ? 'rutin' : 'proyek');
});
</script>
@endsection
