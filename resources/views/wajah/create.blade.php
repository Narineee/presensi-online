@extends('layouts.user')

@section('title', 'Daftarkan Wajah Biometrik')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Header Informasi -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100">
                🛡️
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900">Pendaftaran Wajah Biometrik</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Langkah ini hanya perlu dilakukan satu kali sebelum Anda dapat melakukan presensi.
                </p>
            </div>
        </div>

        @if (session('error'))
            <div class="mt-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Stepper Indikator 4 Langkah -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 mb-2">
                <span>Tahapan Perekaman Biometrik</span>
                <span id="step-counter-text" class="text-blue-600 font-bold">Langkah 1 dari 4</span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-4">
                <div id="enroll-progress-bar" class="h-full bg-blue-600 rounded-full transition-all duration-500" style="width: 25%;"></div>
            </div>

            <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
                <div id="step-badge-0" class="p-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 font-medium transition-all">
                    <span class="block text-base mb-0.5">👁️</span>
                    <span>1. Kedip</span>
                </div>
                <div id="step-badge-1" class="p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-200/60 font-medium transition-all">
                    <span class="block text-base mb-0.5">⬅️</span>
                    <span>2. Pose Kiri</span>
                </div>
                <div id="step-badge-2" class="p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-200/60 font-medium transition-all">
                    <span class="block text-base mb-0.5">➡️</span>
                    <span>3. Pose Kanan</span>
                </div>
                <div id="step-badge-3" class="p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-200/60 font-medium transition-all">
                    <span class="block text-base mb-0.5">😊</span>
                    <span>4. Depan</span>
                </div>
            </div>
        </div>

        <form action="{{ route('wajah.store') }}" method="POST" id="form-enroll" class="mt-6">
            @csrf
            <input type="hidden" name="face_foto" id="face_foto">
            <input type="hidden" name="face_descriptors" id="face_descriptors">

            <!-- Container Kamera & Panduan Oval -->
            <div class="relative bg-slate-900 rounded-2xl overflow-hidden aspect-[4/3] flex items-center justify-center border-2 border-slate-200 shadow-inner">
                <!-- Video Stream (Mirrored) -->
                <video id="enroll-video" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>

                <!-- Canvas Hidden untuk Snapshot & Ekstraksi Descriptor -->
                <canvas id="enroll-canvas" class="hidden"></canvas>

                <!-- Face Oval Guide SVG Overlay -->
                <div id="face-guide-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center transition-all duration-300">
                    <svg class="w-48 h-60 sm:w-56 sm:h-68" viewBox="0 0 200 260" fill="none">
                        <ellipse id="guide-oval" cx="100" cy="130" rx="72" ry="100" stroke="#38bdf8" stroke-width="3" stroke-dasharray="8 6" class="transition-colors duration-300" />
                    </svg>
                </div>

                <!-- Active Illumination Halo / Glow Border -->
                <div id="illumination-halo" class="absolute inset-0 pointer-events-none transition-all duration-500 ring-4 ring-inset ring-transparent"></div>

                <!-- Challenge Instruction Banner (Top Overlay) -->
                <div id="challenge-banner" class="absolute top-3 inset-x-3 pointer-events-none transition-all duration-300 z-10">
                    <div class="bg-slate-950/85 backdrop-blur-md text-white px-3.5 py-2.5 rounded-xl border border-white/10 shadow-lg flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span id="challenge-icon" class="text-xl shrink-0 transition-transform duration-300">👁️</span>
                            <div class="min-w-0">
                                <div id="challenge-instruction" class="text-xs sm:text-sm font-bold text-white truncate">
                                    Memulai pendeteksian wajah...
                                </div>
                                <div id="challenge-subtext" class="text-[10px] sm:text-[11px] text-slate-300 truncate">
                                    Posisikan wajah Anda tepat di dalam bingkai oval
                                </div>
                            </div>
                        </div>
                        <div id="challenge-step-badge" class="shrink-0 px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-[10px] font-bold">
                            Langkah 1/4
                        </div>
                    </div>
                </div>

                <!-- Active Screen Flash Overlay -->
                <div id="camera-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-200 z-20"></div>

                <!-- Hasil Capture Preview -->
                <img id="enroll-preview" class="w-full h-full object-cover hidden z-10" alt="Foto Wajah Terdaftar">

                <!-- Placeholder Ketika Kamera & AI Sedang Dimuat -->
                <div id="camera-placeholder" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center p-4 text-slate-300 text-center z-30">
                    <div class="w-9 h-9 border-3 border-blue-500 border-t-transparent rounded-full animate-spin mb-2.5"></div>
                    <span class="text-xs font-semibold text-slate-200">Menyiapkan Kamera & AI Biometrik...</span>
                    <span class="text-[10px] text-slate-400 mt-1">Pastikan Anda telah mengizinkan akses kamera di browser</span>
                </div>
            </div>

            <!-- Status Box Informasi -->
            <div id="enroll-status-box" class="mt-3.5 p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center justify-between gap-2 transition-all">
                <div class="flex items-center gap-2 min-w-0">
                    <span id="enroll-status-dot" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span id="enroll-status-text" class="text-slate-600 font-medium truncate">Membuka kamera dan memuat model AI...</span>
                </div>
                <button type="button" id="btn-retake" class="hidden shrink-0 px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg shadow-2xs transition">
                    🔄 Ambil Ulang
                </button>
            </div>

            <!-- Checklist Petunjuk Wajah -->
            <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 space-y-1.5">
                <p class="font-bold text-slate-800">Petunjuk Pendaftaran:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600">✓</span> Pastikan pencahayaan ruangan cukup terang
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600">✓</span> Lepas masker dan kacamata hitam
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600">✓</span> Ikuti setiap instruksi gerakan di layar
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-emerald-600">✓</span> Sistem otomatis merekam 3 sudut wajah
                    </div>
                </div>
            </div>

            <!-- Persetujuan -->
            <label class="mt-5 flex items-start gap-2.5 text-xs text-slate-600 select-none cursor-pointer">
                <input type="checkbox" name="persetujuan" id="persetujuan" value="1" required class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>
                    Saya menyetujui foto dan data biometrik wajah saya disimpan dengan aman dan hanya dipergunakan untuk keperluan verifikasi presensi kehadiran.
                </span>
            </label>

            <!-- Tombol Simpan -->
            <button type="submit" id="btn-submit" disabled
                class="mt-5 w-full py-3 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span>Simpan & Kunci Data Wajah</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script src="{{ asset('js/face-id.js') }}"></script>
<!-- MediaPipe FaceMesh & Camera Utils -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('enroll-video');
    const canvas = document.getElementById('enroll-canvas');
    const preview = document.getElementById('enroll-preview');
    const placeholder = document.getElementById('camera-placeholder');
    const guideOverlay = document.getElementById('face-guide-overlay');
    const guideOval = document.getElementById('guide-oval');
    const halo = document.getElementById('illumination-halo');
    const flashEl = document.getElementById('camera-flash');

    const bannerIcon = document.getElementById('challenge-icon');
    const bannerInstruction = document.getElementById('challenge-instruction');
    const bannerSubtext = document.getElementById('challenge-subtext');
    const bannerStepBadge = document.getElementById('challenge-step-badge');

    const statusBox = document.getElementById('enroll-status-box');
    const statusDot = document.getElementById('enroll-status-dot');
    const statusText = document.getElementById('enroll-status-text');
    const btnRetake = document.getElementById('btn-retake');
    const btnSubmit = document.getElementById('btn-submit');
    const checkAgreement = document.getElementById('persetujuan');

    const inFoto = document.getElementById('face_foto');
    const inDesc = document.getElementById('face_descriptors');

    const progressBar = document.getElementById('enroll-progress-bar');
    const stepCounterText = document.getElementById('step-counter-text');

    // Stepper definitions
    const STEPS = [
        {
            type: 'blink',
            title: 'Kedipkan Kedua Mata',
            subtext: 'Posisikan wajah di tengah oval lalu kedipkan mata Anda',
            icon: '👁️',
            haloColor: 'ring-sky-400/40'
        },
        {
            type: 'turn_left',
            title: 'Tengok Perlahan ke KIRI',
            subtext: 'Palingkan kepala sedikit ke arah kiri Anda',
            icon: '⬅️',
            haloColor: 'ring-purple-400/40'
        },
        {
            type: 'turn_right',
            title: 'Tengok Perlahan ke KANAN',
            subtext: 'Palingkan kepala sedikit ke arah kanan Anda',
            icon: '➡️',
            haloColor: 'ring-pink-400/40'
        },
        {
            type: 'face_center',
            title: 'Hadap Lurus ke DEPAN',
            subtext: 'Tahan posisi di tengah oval dengan tenang untuk foto utama',
            icon: '😊',
            haloColor: 'ring-emerald-400/50'
        }
    ];

    let currentStepIndex = 0;
    let isEnrolling = false;
    let isProcessingStep = false;
    let isEnrollmentComplete = false;
    let isSendingFrame = false;

    // Trackers
    let consecutiveFrames = 0;
    let centerHoldStartTime = null;
    let blinkState = 'waiting_close';

    // Descriptors container: [center, left, right]
    let descriptorLeft = null;
    let descriptorRight = null;
    let descriptorCenter = null;
    let primaryPhotoDataUrl = null;

    let faceMeshInstance = null;
    let cameraInstance = null;

    // Web Audio Synthesizer untuk Audio Feedback
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
            // Audio context fallback
        }
    }

    // Ambil Snapshot dari Video (dicerminkan secara horizontal agar natural)
    function ambilSnapshot() {
        const srcW = video.videoWidth || 640;
        const srcH = video.videoHeight || 480;
        const scale = Math.min(1, 1000 / Math.max(srcW, srcH));

        canvas.width = Math.round(srcW * scale);
        canvas.height = Math.round(srcH * scale);

        const ctx = canvas.getContext('2d');
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();

        return canvas.toDataURL('image/jpeg', 0.85);
    }

    // Update UI Teks Instruksi
    function updateStepUI(customTitle = null, customSubtext = null, customIcon = null) {
        if (isEnrollmentComplete) return;

        const step = STEPS[currentStepIndex];
        bannerIcon.textContent = customIcon || step.icon;
        bannerInstruction.textContent = customTitle || step.title;
        bannerSubtext.textContent = customSubtext || step.subtext;
        bannerStepBadge.textContent = `Langkah ${currentStepIndex + 1}/4`;
        stepCounterText.textContent = `Langkah ${currentStepIndex + 1} dari 4`;

        progressBar.style.width = `${((currentStepIndex + 1) / 4) * 100}%`;

        // Update step badges
        for (let i = 0; i < 4; i++) {
            const badge = document.getElementById(`step-badge-${i}`);
            if (!badge) continue;

            if (i < currentStepIndex) {
                badge.className = 'p-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-300 font-semibold transition-all';
                badge.querySelector('span:first-child').textContent = '✅';
            } else if (i === currentStepIndex) {
                badge.className = 'p-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-300 font-bold shadow-2xs transition-all ring-2 ring-blue-400/30';
                badge.querySelector('span:first-child').textContent = STEPS[i].icon;
            } else {
                badge.className = 'p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-200/60 font-medium transition-all';
                badge.querySelector('span:first-child').textContent = STEPS[i].icon;
            }
        }

        // Update illumination halo border
        if (halo) {
            halo.className = `absolute inset-0 pointer-events-none transition-all duration-500 ring-4 ring-inset ${step.haloColor}`;
        }
    }

    // Flash Cahaya Layar (Active Illumination Flash)
    function triggerActiveFlash() {
        if (!flashEl) return;
        flashEl.classList.remove('opacity-0');
        flashEl.classList.add('opacity-90');
        setTimeout(() => {
            flashEl.classList.remove('opacity-90');
            flashEl.classList.add('opacity-0');
        }, 300);
    }

    // Selesaikan Perekaman Semua Sudut Wajah
    function completeEnrollment() {
        isEnrollmentComplete = true;
        isEnrolling = false;

        // Kumpulkan 3 descriptor: Center (Utama), Left, Right
        const allDescriptors = [descriptorCenter, descriptorLeft, descriptorRight];

        inFoto.value = primaryPhotoDataUrl;
        inDesc.value = JSON.stringify(allDescriptors);

        // Hentikan stream video
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }

        // Tampilkan foto preview
        preview.src = primaryPhotoDataUrl;
        preview.classList.remove('hidden');
        video.classList.add('hidden');
        guideOverlay.classList.add('hidden');
        document.getElementById('challenge-banner').classList.add('hidden');

        // Status selesai
        statusBox.className = 'mt-3.5 p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-xs flex items-center justify-between gap-2 transition-all';
        statusDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0';
        statusText.className = 'text-emerald-800 font-bold truncate';
        statusText.textContent = 'Perekaman biometrik selesai! 3 sampel sudut wajah berhasil disimpan.';

        btnRetake.classList.remove('hidden');

        // Tandai semua badge langkah selesai
        for (let i = 0; i < 4; i++) {
            const badge = document.getElementById(`step-badge-${i}`);
            if (badge) {
                badge.className = 'p-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-300 font-semibold transition-all';
                badge.querySelector('span:first-child').textContent = '✅';
            }
        }
        progressBar.style.width = '100%';
        progressBar.className = 'h-full bg-emerald-600 rounded-full transition-all duration-500';

        // Victory Chime
        playTone(880, 'triangle', 0.15);
        setTimeout(() => playTone(1100, 'triangle', 0.25), 160);

        checkSubmitState();
    }

    function checkSubmitState() {
        btnSubmit.disabled = !(isEnrollmentComplete && checkAgreement.checked);
    }

    checkAgreement.addEventListener('change', checkSubmitState);

    // Reset dan Ambil Ulang
    btnRetake.addEventListener('click', function () {
        if (confirm('Ulangi proses pendaftaran wajah dari awal?')) {
            window.location.reload();
        }
    });

    // Callback Frame FaceMesh MediaPipe Realtime
    async function onFaceMeshResults(results) {
        if (!isEnrolling || isEnrollmentComplete || isProcessingStep) return;

        // 1. Cek apakah ada wajah di depan kamera
        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            consecutiveFrames = 0;
            centerHoldStartTime = null;
            blinkState = 'waiting_close';
            if (guideOval) guideOval.setAttribute('stroke', '#f43f5e'); // Merah
            updateStepUI('Arahkan wajah ke kamera', 'Posisikan wajah Anda tepat di dalam bingkai oval', '👤');
            return;
        }

        const landmarks = results.multiFaceLandmarks[0];

        const nose = landmarks[1];          // Hidung
        const cheekRight = landmarks[234];  // Sisi kanan wajah anatomi
        const cheekLeft = landmarks[454];   // Sisi kiri wajah anatomi
        const forehead = landmarks[10];     // Dahi
        const chin = landmarks[152];        // Dagu

        // 2. Cek jarak dan skala wajah dalam frame
        const faceHeight = Math.hypot(chin.x - forehead.x, chin.y - forehead.y);

        if (faceHeight < 0.22) {
            consecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b'); // Kuning
            updateStepUI('Dekatkan wajah Anda', 'Posisikan wajah lebih dekat ke dalam bingkai oval', '🔍');
            return;
        }

        if (faceHeight > 0.85) {
            consecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b');
            updateStepUI('Mundurkan sedikit wajah', 'Wajah terlalu dekat dengan kamera', '🔍');
            return;
        }

        if (nose.x < 0.22 || nose.x > 0.78 || nose.y < 0.20 || nose.y > 0.80) {
            consecutiveFrames = 0;
            if (guideOval) guideOval.setAttribute('stroke', '#f59e0b');
            updateStepUI('Posisikan wajah di tengah', 'Arahkan wajah tepat di tengah bingkai oval', '🎯');
            return;
        }

        // Posisi tepat di dalam oval
        if (guideOval) guideOval.setAttribute('stroke', '#22c55e'); // Hijau

        // 3. Hitung Rasio Yaw Rotasi Kepala
        const dRight = Math.abs(nose.x - cheekRight.x);
        const dLeft = Math.abs(cheekLeft.x - nose.x);
        const yawRatio = (dRight + dLeft) > 0 ? (dRight / (dRight + dLeft)) : 0.5;

        // 4. Hitung EAR (Eye Aspect Ratio) untuk Kedipan
        const vr = Math.hypot(landmarks[159].x - landmarks[145].x, landmarks[159].y - landmarks[145].y);
        const hr = Math.hypot(landmarks[33].x - landmarks[133].x, landmarks[33].y - landmarks[133].y);
        const earR = hr > 0 ? (vr / hr) : 0;

        const vl = Math.hypot(landmarks[386].x - landmarks[374].x, landmarks[386].y - landmarks[374].y);
        const hl = Math.hypot(landmarks[263].x - landmarks[362].x, landmarks[263].y - landmarks[362].y);
        const earL = hl > 0 ? (vl / hl) : 0;

        const avgEar = (earR + earL) / 2;

        const currentStep = STEPS[currentStepIndex];
        updateStepUI();

        // 5. Evaluasi Langkah Aktif
        if (currentStep.type === 'blink') {
            if (blinkState === 'waiting_close') {
                if (avgEar < 0.15) blinkState = 'closed';
            } else if (blinkState === 'closed') {
                if (avgEar > 0.20) {
                    // Kedipan berhasil terdeteksi!
                    isProcessingStep = true;
                    playTone(720, 'sine', 0.12);
                    currentStepIndex++;
                    updateStepUI();
                    setTimeout(() => { isProcessingStep = false; }, 400);
                }
            }
        } else if (currentStep.type === 'turn_left') {
            // Toleh ke kiri (yawRatio > 0.65)
            if (yawRatio > 0.65) {
                consecutiveFrames++;
                if (consecutiveFrames >= 3) {
                    isProcessingStep = true;
                    statusText.textContent = 'Merekam sampel sudut wajah kiri...';
                    ambilSnapshot();

                    try {
                        const desc = await FaceID.descriptorFrom(canvas);
                        descriptorLeft = desc;
                        playTone(780, 'sine', 0.12);
                        consecutiveFrames = 0;
                        currentStepIndex++;
                        updateStepUI();
                        statusText.textContent = 'Sampel sudut kiri berhasil direkam!';
                        setTimeout(() => { isProcessingStep = false; }, 500);
                    } catch (err) {
                        updateStepUI('Tengok sedikit saja ke Kiri', 'Palingkan wajah perlahan, jangan terlalu jauh', '⬅️');
                        consecutiveFrames = 0;
                        isProcessingStep = false;
                    }
                }
            } else {
                consecutiveFrames = Math.max(0, consecutiveFrames - 1);
            }
        } else if (currentStep.type === 'turn_right') {
            // Toleh ke kanan (yawRatio < 0.35)
            if (yawRatio < 0.35) {
                consecutiveFrames++;
                if (consecutiveFrames >= 3) {
                    isProcessingStep = true;
                    statusText.textContent = 'Merekam sampel sudut wajah kanan...';
                    ambilSnapshot();

                    try {
                        const desc = await FaceID.descriptorFrom(canvas);
                        descriptorRight = desc;
                        playTone(820, 'sine', 0.12);
                        consecutiveFrames = 0;
                        currentStepIndex++;
                        updateStepUI();
                        statusText.textContent = 'Sampel sudut kanan berhasil direkam!';
                        setTimeout(() => { isProcessingStep = false; }, 500);
                    } catch (err) {
                        updateStepUI('Tengok sedikit saja ke Kanan', 'Palingkan wajah perlahan, jangan terlalu jauh', '➡️');
                        consecutiveFrames = 0;
                        isProcessingStep = false;
                    }
                }
            } else {
                consecutiveFrames = Math.max(0, consecutiveFrames - 1);
            }
        } else if (currentStep.type === 'face_center') {
            // Hadap depan lurus stabil selama 550ms
            if (yawRatio >= 0.42 && yawRatio <= 0.58) {
                if (!centerHoldStartTime) {
                    centerHoldStartTime = performance.now();
                } else if (performance.now() - centerHoldStartTime >= 550) {
                    isProcessingStep = true;
                    statusText.textContent = 'Mengambil foto utama & sampel biometrik...';

                    // Active Flash Illumination
                    triggerActiveFlash();
                    const photo = ambilSnapshot();

                    try {
                        const desc = await FaceID.descriptorFrom(canvas);
                        descriptorCenter = desc;
                        primaryPhotoDataUrl = photo;

                        completeEnrollment();
                    } catch (err) {
                        updateStepUI('Wajah belum stabil', 'Tetap diam dan hadap lurus ke kamera', '😊');
                        centerHoldStartTime = null;
                        isProcessingStep = false;
                    }
                }
            } else {
                centerHoldStartTime = null;
            }
        }
    }

    // Inisialisasi Kamera & FaceMesh
    async function initBiometrics() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Akses kamera tidak didukung di peramban ini.');
            }

            statusText.textContent = 'Memuat pengenal wajah & FaceMesh AI...';

            // Muat model Face-API & FaceMesh secara paralel
            await Promise.all([
                FaceID.load(),
                new Promise((resolve, reject) => {
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
                        resolve();
                    } catch (e) {
                        reject(e);
                    }
                })
            ]);

            cameraInstance = new Camera(video, {
                onFrame: async () => {
                    if (isEnrolling && !isEnrollmentComplete && video.videoWidth > 0 && !isSendingFrame) {
                        isSendingFrame = true;
                        try {
                            await faceMeshInstance.send({ image: video });
                        } catch (err) {
                            console.warn('FaceMesh frame processing error:', err);
                        } finally {
                            isSendingFrame = false;
                        }
                    }
                },
                width: 640,
                height: 480
            });

            await cameraInstance.start();

            // Sembunyikan placeholder pemuatan
            placeholder.classList.add('hidden');
            isEnrolling = true;

            statusDot.className = 'w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0';
            statusText.textContent = 'Kamera siap. Ikuti instruksi pada bingkai oval.';

            updateStepUI();
        } catch (e) {
            console.error('Inisialisasi biometrik gagal:', e);
            placeholder.innerHTML = `
                <div class="p-4 text-center">
                    <p class="text-rose-400 font-bold text-sm">Gagal Mengakses Kamera / AI</p>
                    <p class="text-slate-400 text-xs mt-1">Pastikan izin kamera diaktifkan dan koneksi internet stabil untuk mengunduh model AI.</p>
                    <button type="button" onclick="window.location.reload()" class="mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold">
                        Coba Lagi
                    </button>
                </div>
            `;
            statusDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
            statusText.textContent = 'Gagal memuat kamera/AI.';
        }
    }

    // Periksa kesiapan pustaka
    let attempts = 0;
    function checkLibrariesReady() {
        if (typeof FaceMesh !== 'undefined' && typeof Camera !== 'undefined' && typeof faceapi !== 'undefined' && typeof FaceID !== 'undefined') {
            initBiometrics();
        } else if (attempts < 50) {
            attempts++;
            setTimeout(checkLibrariesReady, 150);
        } else {
            placeholder.innerHTML = `
                <div class="p-4 text-center">
                    <p class="text-rose-400 font-bold text-sm">Gagal Mengunduh Pustaka AI</p>
                    <p class="text-slate-400 text-xs mt-1">Periksa koneksi internet Anda lalu segarkan halaman.</p>
                </div>
            `;
        }
    }

    checkLibrariesReady();
});
</script>
@endsection