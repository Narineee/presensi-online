@extends('layouts.user')

@section('title', 'Daftarkan Wajah')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8">
        <h1 class="text-lg font-semibold text-slate-900">Daftarkan wajah Anda</h1>
        <p class="text-sm text-slate-600 mt-1">
            Sebelum presensi pertama, kami perlu satu foto wajah Anda sebagai pembanding.
            Langkah ini cukup dilakukan sekali.
        </p>

        @if (session('error'))
            <p class="mt-4 text-sm text-rose-700">{{ session('error') }}</p>
        @endif

        @if ($errors->any())
            <ul class="mt-4 text-sm text-rose-700 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <ol class="mt-5 text-sm text-slate-700 list-decimal list-inside space-y-1">
            <li>Cari tempat yang cukup terang.</li>
            <li>Lepas masker dan kacamata hitam.</li>
            <li>Hadap lurus ke kamera, lalu tekan "Ambil foto".</li>
        </ol>

        <form action="{{ route('wajah.store') }}" method="POST" class="mt-5">
            @csrf
            <input type="hidden" name="face_foto" id="face_foto">
            <input type="hidden" name="face_descriptors" id="face_descriptors">

            <div class="relative bg-slate-900 rounded-xl overflow-hidden aspect-[4/3]">
                <video id="enroll-video" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>
                <img id="enroll-preview" class="hidden w-full h-full object-cover" alt="Foto wajah Anda">
                <canvas id="enroll-canvas" class="hidden"></canvas>
            </div>

            <p id="enroll-status" class="mt-3 text-sm text-slate-600">Membuka kamera...</p>

            <div class="mt-3 flex gap-2">
                <button type="button" id="btn-capture" disabled
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Ambil foto
                </button>
                <button type="button" id="btn-retake"
                    class="hidden px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium">
                    Ambil ulang
                </button>
            </div>

            <label class="mt-5 flex items-start gap-2 text-sm text-slate-700">
                <input type="checkbox" name="persetujuan" value="1" required class="mt-1 rounded border-slate-300">
                <span>
                    Saya setuju foto dan data wajah saya disimpan dan hanya dipakai untuk memverifikasi presensi.
                    Foto hanya dapat dilihat admin, dan data wajah hanya dapat diubah oleh admin.
                </span>
            </label>

            <button type="submit" id="btn-submit" disabled
                class="mt-5 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                Simpan
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
<script src="{{ asset('js/face-id.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const video = document.getElementById('enroll-video');
    const canvas = document.getElementById('enroll-canvas');
    const preview = document.getElementById('enroll-preview');
    const statusEl = document.getElementById('enroll-status');
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');
    const btnSubmit = document.getElementById('btn-submit');
    const inFoto = document.getElementById('face_foto');
    const inDesc = document.getElementById('face_descriptors');
    const SAMPLES = 3;

    function setStatus(text, tone) {
        const colors = { info: 'text-slate-600', ok: 'text-emerald-700', error: 'text-rose-700' };
        statusEl.className = 'mt-3 text-sm ' + (colors[tone] || colors.info);
        statusEl.textContent = text;
    }

    // Snapshot dicerminkan agar konsisten dengan foto presensi
    function snapshot() {
        const w = video.videoWidth, h = video.videoHeight;
        const scale = Math.min(1, 1000 / Math.max(w, h));
        canvas.width = Math.round(w * scale);
        canvas.height = Math.round(h * scale);
        const ctx = canvas.getContext('2d');
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        return canvas.toDataURL('image/jpeg', 0.85);
    }

    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

    // 1. Buka kamera
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('no-media');
        }
        video.srcObject = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' },
            audio: false,
        });
    } catch (e) {
        setStatus('Kamera tidak bisa dibuka. Izinkan akses kamera di browser, dan pastikan alamat web memakai https.', 'error');
        return;
    }

    // 2. Muat model pengenal wajah
    try {
        setStatus('Memuat pengenal wajah, mohon tunggu...', 'info');
        await FaceID.load();
    } catch (e) {
        setStatus('Pengenal wajah gagal dimuat. Periksa koneksi internet lalu segarkan halaman.', 'error');
        return;
    }

    btnCapture.disabled = false;
    setStatus('Kamera siap. Hadap lurus ke kamera, lalu tekan "Ambil foto".', 'info');

    // 3. Ambil foto
    btnCapture.addEventListener('click', async function () {
        if (!video.videoWidth) {
            setStatus('Kamera belum siap, coba lagi sebentar.', 'error');
            return;
        }
        btnCapture.disabled = true;
        setStatus('Mengambil foto, mohon tetap diam...', 'info');

        try {
            const descriptors = [];
            let firstPhoto = null;
            for (let i = 0; i < SAMPLES; i++) {
                const photo = snapshot();
                descriptors.push(await FaceID.descriptorFrom(canvas));
                if (!firstPhoto) firstPhoto = photo;
                await sleep(400);
            }

            inFoto.value = firstPhoto;
            inDesc.value = JSON.stringify(descriptors);
            preview.src = firstPhoto;
            preview.classList.remove('hidden');
            video.classList.add('hidden');
            btnRetake.classList.remove('hidden');
            btnSubmit.disabled = false;
            setStatus('Foto berhasil diambil. Centang persetujuan lalu tekan Simpan.', 'ok');
        } catch (err) {
            setStatus(err.message + ' Silakan coba lagi.', 'error');
            btnCapture.disabled = false;
        }
    });

    // 4. Ambil ulang
    btnRetake.addEventListener('click', function () {
        inFoto.value = '';
        inDesc.value = '';
        preview.classList.add('hidden');
        video.classList.remove('hidden');
        btnRetake.classList.add('hidden');
        btnSubmit.disabled = true;
        btnCapture.disabled = false;
        setStatus('Hadap lurus ke kamera, lalu tekan "Ambil foto".', 'info');
    });
});
</script>
@endsection