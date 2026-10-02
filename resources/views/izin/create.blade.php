@extends('layouts.user')

@section('title', 'Ajukan Permohonan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ajukan Izin / Sakit</h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi formulir secara bertahap dari jenis hingga lampiran berkas bukti.</p>
        </div>
        <a href="{{ route('izin.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Stepper Tracker (Visual Progres) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs">
        <div class="grid grid-cols-4 gap-2 text-center">
            <!-- Step Tracker 1 -->
            <div id="tracker-step-1" class="flex flex-col items-center gap-1 transition-all duration-300">
                <div id="tracker-circle-1" class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                    1
                </div>
                <span id="tracker-label-1" class="text-[11px] font-bold text-blue-700">Kategori</span>
            </div>

            <!-- Step Tracker 2 -->
            <div id="tracker-step-2" class="flex flex-col items-center gap-1 transition-all duration-300">
                <div id="tracker-circle-2" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <span id="tracker-label-2" class="text-[11px] font-semibold text-slate-400">Tanggal</span>
            </div>

            <!-- Step Tracker 3 -->
            <div id="tracker-step-3" class="flex flex-col items-center gap-1 transition-all duration-300">
                <div id="tracker-circle-3" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center">
                    3
                </div>
                <span id="tracker-label-3" class="text-[11px] font-semibold text-slate-400">Alasan</span>
            </div>

            <!-- Step Tracker 4 -->
            <div id="tracker-step-4" class="flex flex-col items-center gap-1 transition-all duration-300">
                <div id="tracker-circle-4" class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center">
                    4
                </div>
                <span id="tracker-label-4" class="text-[11px] font-semibold text-slate-400">Lampiran</span>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form id="form-pengajuan-izin" action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- LANGKAH 1: Pilihan Jenis Izin -->
            <div id="step-card-1" class="p-5 rounded-2xl border transition-all duration-300 bg-white border-blue-200 ring-2 ring-blue-500/10 shadow-xs">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2.5">
                        <span id="badge-num-1" class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">1</span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Jenis Permohonan <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-[11px] text-slate-400">Pilih salah satu kategori ketidakhadiran</p>
                        </div>
                    </div>
                    <span id="badge-status-1" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                        <span>Pilih Kategori</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-rose-400 cursor-pointer transition has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50">
                        <input type="radio" name="jenis_izin" value="sakit" class="sr-only" {{ old('jenis_izin') === 'sakit' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🩺</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Sakit</div>
                                <div class="text-[10px] text-slate-400">Dengan surat dokter</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                        <input type="radio" name="jenis_izin" value="izin" class="sr-only" {{ old('jenis_izin') === 'izin' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">📋</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Izin Keperluan</div>
                                <div class="text-[10px] text-slate-400">Ada halangan penting</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-purple-400 cursor-pointer transition has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/50">
                        <input type="radio" name="jenis_izin" value="cuti" class="sr-only" {{ old('jenis_izin') === 'cuti' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🌴</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Cuti</div>
                                <div class="text-[10px] text-slate-400">Hak libur resmi</div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('jenis_izin')
                    <p class="text-xs text-rose-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- LANGKAH 2: Rentang Tanggal -->
            <div id="step-card-2" class="p-5 rounded-2xl border transition-all duration-300 opacity-40 grayscale pointer-events-none select-none bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2.5">
                        <span id="badge-num-2" class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">2</span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rentang Tanggal <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-[11px] text-slate-400">Tentukan tanggal mulai dan selesai permohonan</p>
                        </div>
                    </div>
                    <span id="badge-status-2" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        <span>Terkunci</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            value="{{ old('tanggal_mulai') }}"
                            required
                            disabled
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_mulai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
                        >
                        @error('tanggal_mulai')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            value="{{ old('tanggal_selesai') }}"
                            required
                            disabled
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_selesai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
                        >
                        @error('tanggal_selesai')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Durasi Preview -->
                <div id="durasi-info-container" class="mt-3.5 p-2.5 rounded-xl bg-blue-50/80 border border-blue-100 flex items-center justify-between text-xs text-blue-900 hidden">
                    <span class="font-medium">Total Estimasi Durasi Izin:</span>
                    <span id="durasi-text" class="font-bold text-blue-700">-</span>
                </div>
            </div>

            <!-- LANGKAH 3: Alasan Pengajuan -->
            <div id="step-card-3" class="p-5 rounded-2xl border transition-all duration-300 opacity-40 grayscale pointer-events-none select-none bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2.5">
                        <span id="badge-num-3" class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">3</span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Alasan / Keterangan Tidak Hadir <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-[11px] text-slate-400">Jelaskan alasan ketidakhadiran Anda secara lengkap</p>
                        </div>
                    </div>
                    <span id="badge-status-3" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        <span>Terkunci</span>
                    </span>
                </div>

                <div>
                    <textarea
                        name="alasan"
                        id="alasan"
                        rows="3"
                        placeholder="Jelaskan alasan ketidakhadiran Anda secara jelas (minimal 5 karakter)..."
                        required
                        disabled
                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('alasan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-normal text-slate-800 leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
                    >{{ old('alasan') }}</textarea>
                    <div class="flex items-center justify-between mt-1 text-[11px]">
                        <span id="alasan-counter" class="text-slate-400">Minimal 5 karakter</span>
                        @error('alasan')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- LANGKAH 4: Upload File Bukti (Surat Dokter / Lampiran) -->
            <div id="step-card-4" class="p-5 rounded-2xl border transition-all duration-300 opacity-40 grayscale pointer-events-none select-none bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2.5">
                        <span id="badge-num-4" class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">4</span>
                        <div>
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Lampiran Bukti (Surat Dokter / Dokumen Pendukung) <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-[11px] text-slate-400">Unggah berkas bukti pendukung atau surat dokter</p>
                        </div>
                    </div>
                    <span id="badge-status-4" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        <span>Terkunci</span>
                    </span>
                </div>

                <div>
                    <input
                        type="file"
                        name="bukti_file"
                        id="bukti_file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        required
                        disabled
                        class="w-full px-3.5 py-2 rounded-xl border {{ $errors->has('bukti_file') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer bg-white"
                    >
                    <p class="text-[11px] text-slate-400 mt-1"><span class="text-rose-500 font-semibold">* Wajib dilampirkan.</span> Format yang didukung: JPG, PNG, atau PDF. Maksimal 2MB.</p>

                    <!-- Preview Nama & Ukuran Berkas Terpilih -->
                    <div id="file-info-preview" class="mt-2.5 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between hidden">
                        <div class="flex items-center gap-2 truncate">
                            <span>📎</span>
                            <span id="file-name-text" class="font-semibold truncate">nama-file.pdf</span>
                            <span id="file-size-text" class="text-[10px] text-emerald-600 font-mono">(0 KB)</span>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-700 shrink-0">✓ Siap diunggah</span>
                    </div>

                    @error('bukti_file')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Info Catatan Otomatis -->
            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-emerald-900 leading-relaxed">
                    <span class="font-bold">Sinkronisasi Presensi Otomatis:</span> Begitu permohonan ini disetujui oleh Pembimbing, status kehadiran Anda pada rentang tanggal tersebut akan otomatis tercatat sebagai <em>Sakit/Izin/Cuti</em> dan tidak dihitung alpa.
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('izin.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    id="btn-submit-izin"
                    disabled
                    class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed transition flex items-center gap-2 select-none"
                >
                    <svg id="btn-submit-icon-locked" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <svg id="btn-submit-icon-ready" class="w-4 h-4 text-white hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    <span id="btn-submit-text">Lengkapi Semua Langkah</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-pengajuan-izin');
    const radios = document.querySelectorAll('input[name="jenis_izin"]');
    const inputMulai = document.getElementById('tanggal_mulai');
    const inputSelesai = document.getElementById('tanggal_selesai');
    const inputAlasan = document.getElementById('alasan');
    const inputFile = document.getElementById('bukti_file');

    const card1 = document.getElementById('step-card-1');
    const card2 = document.getElementById('step-card-2');
    const card3 = document.getElementById('step-card-3');
    const card4 = document.getElementById('step-card-4');

    const badgeStatus1 = document.getElementById('badge-status-1');
    const badgeStatus2 = document.getElementById('badge-status-2');
    const badgeStatus3 = document.getElementById('badge-status-3');
    const badgeStatus4 = document.getElementById('badge-status-4');

    const badgeNum1 = document.getElementById('badge-num-1');
    const badgeNum2 = document.getElementById('badge-num-2');
    const badgeNum3 = document.getElementById('badge-num-3');
    const badgeNum4 = document.getElementById('badge-num-4');

    const trackerCircle1 = document.getElementById('tracker-circle-1');
    const trackerLabel1 = document.getElementById('tracker-label-1');
    const trackerCircle2 = document.getElementById('tracker-circle-2');
    const trackerLabel2 = document.getElementById('tracker-label-2');
    const trackerCircle3 = document.getElementById('tracker-circle-3');
    const trackerLabel3 = document.getElementById('tracker-label-3');
    const trackerCircle4 = document.getElementById('tracker-circle-4');
    const trackerLabel4 = document.getElementById('tracker-label-4');

    const durasiContainer = document.getElementById('durasi-info-container');
    const durasiText = document.getElementById('durasi-text');
    const alasanCounter = document.getElementById('alasan-counter');

    const filePreview = document.getElementById('file-info-preview');
    const fileNameText = document.getElementById('file-name-text');
    const fileSizeText = document.getElementById('file-size-text');

    const btnSubmit = document.getElementById('btn-submit-izin');
    const btnSubmitText = document.getElementById('btn-submit-text');
    const iconLocked = document.getElementById('btn-submit-icon-locked');
    const iconReady = document.getElementById('btn-submit-icon-ready');

    function setStepState(card, badgeStatus, badgeNum, trackerCircle, trackerLabel, state, stepNumber, textActive, textDone) {
        if (state === 'locked') {
            card.className = 'p-5 rounded-2xl border transition-all duration-300 opacity-40 grayscale pointer-events-none select-none bg-slate-50/70 border-slate-200';
            badgeStatus.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 flex items-center gap-1';
            badgeStatus.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg><span>Terkunci</span>`;
            badgeNum.className = 'w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold';
            badgeNum.textContent = stepNumber;

            trackerCircle.className = 'w-7 h-7 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center';
            trackerCircle.textContent = stepNumber;
            trackerLabel.className = 'text-[11px] font-semibold text-slate-400';

            card.querySelectorAll('input, textarea').forEach(el => {
                el.disabled = true;
            });
        } else if (state === 'active') {
            card.className = 'p-5 rounded-2xl border transition-all duration-300 opacity-100 bg-white border-blue-300 ring-2 ring-blue-500/10 shadow-xs';
            badgeStatus.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1';
            badgeStatus.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span><span>${textActive}</span>`;
            badgeNum.className = 'w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-xs';
            badgeNum.textContent = stepNumber;

            trackerCircle.className = 'w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs ring-4 ring-blue-100';
            trackerCircle.textContent = stepNumber;
            trackerLabel.className = 'text-[11px] font-bold text-blue-700';

            card.querySelectorAll('input, textarea').forEach(el => {
                el.disabled = false;
            });
        } else if (state === 'done') {
            card.className = 'p-5 rounded-2xl border transition-all duration-300 opacity-100 bg-white border-emerald-300 ring-1 ring-emerald-500/10 shadow-2xs';
            badgeStatus.className = 'text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1';
            badgeStatus.innerHTML = `<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg><span>${textDone}</span>`;
            badgeNum.className = 'w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold';
            badgeNum.innerHTML = '✓';

            trackerCircle.className = 'w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shadow-xs';
            trackerCircle.innerHTML = '✓';
            trackerLabel.className = 'text-[11px] font-bold text-emerald-700';

            card.querySelectorAll('input, textarea').forEach(el => {
                el.disabled = false;
            });
        }
    }

    function updateForm() {
        const checkedRadio = document.querySelector('input[name="jenis_izin"]:checked');
        const hasStep1 = Boolean(checkedRadio && checkedRadio.value);

        // Step 1: Kategori Izin
        if (hasStep1) {
            let labelText = checkedRadio.value.charAt(0).toUpperCase() + checkedRadio.value.slice(1);
            setStepState(card1, badgeStatus1, badgeNum1, trackerCircle1, trackerLabel1, 'done', 1, 'Pilih Kategori', `✓ ${labelText}`);
        } else {
            setStepState(card1, badgeStatus1, badgeNum1, trackerCircle1, trackerLabel1, 'active', 1, 'Pilih Kategori', 'Selesai');
        }

        // Step 2: Rentang Tanggal
        const tglMulai = inputMulai.value;
        const tglSelesai = inputSelesai.value;
        const hasDates = Boolean(tglMulai && tglSelesai && tglSelesai >= tglMulai);

        if (!hasStep1) {
            setStepState(card2, badgeStatus2, badgeNum2, trackerCircle2, trackerLabel2, 'locked', 2);
            durasiContainer.classList.add('hidden');
        } else if (hasDates) {
            const d1 = new Date(tglMulai);
            const d2 = new Date(tglSelesai);
            const diffDays = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            durasiText.textContent = `${diffDays} Hari Kalender`;
            durasiContainer.classList.remove('hidden');
            setStepState(card2, badgeStatus2, badgeNum2, trackerCircle2, trackerLabel2, 'done', 2, 'Isi Rentang Tanggal', `✓ ${diffDays} Hari`);
        } else {
            setStepState(card2, badgeStatus2, badgeNum2, trackerCircle2, trackerLabel2, 'active', 2, 'Isi Rentang Tanggal', 'Selesai');
            durasiContainer.classList.add('hidden');
        }

        // Sinkronisasi min date
        if (tglMulai) {
            inputSelesai.min = tglMulai;
            if (tglSelesai && tglSelesai < tglMulai) {
                inputSelesai.value = tglMulai;
            }
        }

        // Step 3: Alasan Pengajuan
        const alasanVal = inputAlasan.value.trim();
        const hasStep2 = hasStep1 && hasDates;
        const hasAlasan = alasanVal.length >= 5;

        if (alasanVal.length === 0) {
            alasanCounter.textContent = 'Minimal 5 karakter';
            alasanCounter.className = 'text-slate-400';
        } else if (alasanVal.length < 5) {
            alasanCounter.textContent = `Minimal 5 karakter (kurang ${5 - alasanVal.length} karakter lagi)`;
            alasanCounter.className = 'text-amber-600 font-semibold';
        } else {
            alasanCounter.textContent = `✓ ${alasanVal.length} karakter terisi`;
            alasanCounter.className = 'text-emerald-600 font-semibold';
        }

        if (!hasStep2) {
            setStepState(card3, badgeStatus3, badgeNum3, trackerCircle3, trackerLabel3, 'locked', 3);
        } else if (hasAlasan) {
            setStepState(card3, badgeStatus3, badgeNum3, trackerCircle3, trackerLabel3, 'done', 3, 'Tulis Alasan', '✓ Alasan Terisi');
        } else {
            setStepState(card3, badgeStatus3, badgeNum3, trackerCircle3, trackerLabel3, 'active', 3, 'Tulis Alasan', 'Selesai');
        }

        // Step 4: Berkas Bukti Lampiran
        const hasStep3 = hasStep2 && hasAlasan;
        const hasFile = Boolean(inputFile.files && inputFile.files.length > 0);

        if (hasFile) {
            const f = inputFile.files[0];
            fileNameText.textContent = f.name;
            const sizeKb = Math.round(f.size / 1024);
            fileSizeText.textContent = `(${sizeKb} KB)`;
            filePreview.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
        }

        if (!hasStep3) {
            setStepState(card4, badgeStatus4, badgeNum4, trackerCircle4, trackerLabel4, 'locked', 4);
        } else if (hasFile) {
            setStepState(card4, badgeStatus4, badgeNum4, trackerCircle4, trackerLabel4, 'done', 4, 'Unggah Berkas Bukti', '✓ Berkas Terlampir');
        } else {
            setStepState(card4, badgeStatus4, badgeNum4, trackerCircle4, trackerLabel4, 'active', 4, 'Unggah Berkas Bukti', 'Selesai');
        }

        // Tombol Submit
        const allCompleted = hasStep1 && hasStep2 && hasStep3 && hasFile;
        if (allCompleted) {
            btnSubmit.disabled = false;
            btnSubmit.className = 'px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer flex items-center gap-2';
            btnSubmitText.textContent = 'Kirim Permohonan';
            iconLocked.classList.add('hidden');
            iconReady.classList.remove('hidden');
        } else {
            btnSubmit.disabled = true;
            btnSubmit.className = 'px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed transition flex items-center gap-2 select-none';
            btnSubmitText.textContent = 'Lengkapi Semua Langkah';
            iconLocked.classList.remove('hidden');
            iconReady.classList.add('hidden');
        }
    }

    // Pastikan semua field enabled sebelum submit form
    form.addEventListener('submit', function () {
        form.querySelectorAll('input, textarea').forEach(el => {
            el.disabled = false;
        });
    });

    radios.forEach(radio => radio.addEventListener('change', updateForm));
    inputMulai.addEventListener('input', updateForm);
    inputMulai.addEventListener('change', updateForm);
    inputSelesai.addEventListener('input', updateForm);
    inputSelesai.addEventListener('change', updateForm);
    inputAlasan.addEventListener('input', updateForm);
    inputFile.addEventListener('change', updateForm);

    // Initial run
    updateForm();
});
</script>
@endpush
