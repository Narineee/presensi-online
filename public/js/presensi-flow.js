/* ===== Helper face-api.js ===== */
const FaceEngine = {
    load() { return window.FaceID.load(); }, // model dimuat oleh face-id.js (lokal, fallback CDN)
    async start(video) {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: 640, height: 480 }, audio: false,
        });
        video.srcObject = stream;
        await video.play();
    },
    stop(video) {
        if (video && video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
    },
    detect(video) {
        return faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
            .withFaceLandmarks();
    },
    // Rasio posisi hidung di antara tepi rahang: ~0.5 = menghadap depan.
    // Koordinat mentah (tidak dimirror): menoleh ke KANAN pengguna => rasio kecil.
    // Jika di perangkat Anda terbalik, tukar 'right' dan 'left' di bawah.
    pose(det) {
        const p = det.landmarks.positions;
        const r = (p[30].x - p[0].x) / (p[16].x - p[0].x);
        if (r < 0.38) return 'right';
        if (r > 0.62) return 'left';
        if (r > 0.42 && r < 0.58) return 'front';
        return 'other';
    },
    snapshot(video, max = 480) {
        const s = max / Math.max(video.videoWidth, video.videoHeight);
        const c = document.createElement('canvas');
        c.width = Math.round(video.videoWidth * s);
        c.height = Math.round(video.videoHeight * s);
        c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
        return c.toDataURL('image/jpeg', 0.8);
    },
};

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371000, rad = d => d * Math.PI / 180;
    const a = Math.sin(rad(lat2 - lat1) / 2) ** 2
        + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(rad(lon2 - lon1) / 2) ** 2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

/* ===== Presensi masuk / pulang (langkah 1 lokasi, langkah 2 wajah) ===== */
function presensiFlow(cfg) {
    return {
        step: 1, mode: cfg.mode, office: cfg.office, checkRadius: cfg.checkRadius !== false,
        loc: { lat: null, lng: null, address: 'Mencari lokasi…', jarak: null, state: 'loading', msg: '' },
        checks: [
            { t: 'Wajah terlihat jelas', ok: false },
            { t: 'Hadap ke kanan', ok: false },
            { t: 'Menghadap ke depan', ok: false },
        ],
        cam: 'idle', verified: false, faceMsg: '', descriptor: '', photo: '', submitting: false, map: null,

        get done() { return this.checks.filter(c => c.ok).length; },
        get coord() { return this.loc.lat === null ? '' : this.loc.lat + ',' + this.loc.lng; },
        get needRadius() { return this.mode === 'onsite' && this.checkRadius; },
        get outside() { return this.needRadius && this.loc.jarak !== null && this.loc.jarak > this.office.radius; },
        get locReady() { return this.loc.lat !== null && !this.outside; },

        init() {
            this.locate();
            this.$watch('mode', () => setTimeout(() => this.map && this.map.invalidateSize(), 80));
        },

        locate() {
            this.loc.state = 'loading'; this.loc.address = 'Mencari lokasi…';
            if (!navigator.geolocation) { this.loc.state = 'error'; this.loc.msg = 'Browser tidak mendukung GPS.'; return; }
            navigator.geolocation.getCurrentPosition(p => {
                const lat = p.coords.latitude, lng = p.coords.longitude;
                this.loc.lat = lat; this.loc.lng = lng; this.loc.state = 'ok';
                this.loc.jarak = Math.round(haversine(this.office.lat, this.office.lng, lat, lng));
                this.loc.address = lat.toFixed(5) + ', ' + lng.toFixed(5);
                this.drawMap(); this.reverse(lat, lng);
            }, e => {
                this.loc.state = 'error'; this.loc.address = '-';
                this.loc.msg = e.code === 1
                    ? 'Izin lokasi ditolak. Aktifkan lokasi di pengaturan browser, lalu coba lagi.'
                    : 'Lokasi belum bisa dideteksi. Pastikan GPS aktif, lalu coba lagi.';
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        },

        reverse(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(r => r.json()).then(j => { if (j.display_name) this.loc.address = j.display_name; })
                .catch(() => {});
        },

        drawMap() {
            if (typeof L === 'undefined' || !this.$refs.map) return;
            if (!this.map) {
                this.map = L.map(this.$refs.map, { zoomControl: false, attributionControl: false });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
                L.circle([this.office.lat, this.office.lng], { radius: this.office.radius, color: '#14664B', fillOpacity: .15 }).addTo(this.map);
            }
            if (this.me) this.map.removeLayer(this.me);
            this.me = L.circleMarker([this.loc.lat, this.loc.lng], { radius: 8, color: '#fff', weight: 3, fillColor: '#2563eb', fillOpacity: 1 }).addTo(this.map);
            this.map.fitBounds(L.latLngBounds([[this.office.lat, this.office.lng], [this.loc.lat, this.loc.lng]]).pad(.4), { maxZoom: 18 });
        },

        next() {
            if (!this.$root.reportValidity()) return;
            this.step = 2; this.startFace();
        },
        back() {
            FaceEngine.stop(this.$refs.video); this.cam = 'idle'; this.resetFace();
            this.step = 1; setTimeout(() => this.map && this.map.invalidateSize(), 80);
        },

        resetFace() {
            this.checks.forEach(c => c.ok = false);
            this.verified = false; this.descriptor = ''; this.photo = ''; this.faceMsg = '';
        },
        async startFace() {
            this.resetFace(); this.cam = 'loading';
            try {
                await FaceEngine.load(cfg.models);
                await FaceEngine.start(this.$refs.video);
                this.cam = 'ready'; this.faceMsg = 'Posisikan wajah di dalam oval.';
                this.loop();
            } catch (e) {
                this.cam = 'error';
                this.faceMsg = 'Kamera atau model wajah tidak dapat dimuat: ' + (e.message || e);
            }
        },
        async loop() {
            if (this.cam !== 'ready' || this.step !== 2) return;
            const det = await FaceEngine.detect(this.$refs.video);
            if (!det) {
                this.faceMsg = 'Wajah tidak terdeteksi. Pastikan pencahayaan cukup.';
            } else {
                const pose = FaceEngine.pose(det);
                if (!this.checks[0].ok) {
                    if (pose === 'front' && det.detection.score > 0.6) this.checks[0].ok = true;
                    else this.faceMsg = 'Hadap lurus ke kamera.';
                } else if (!this.checks[1].ok) {
                    this.faceMsg = 'Tolehkan kepala ke kanan.';
                    if (pose === 'right') this.checks[1].ok = true;
                } else {
                    this.faceMsg = 'Kembali menghadap ke depan.';
                    if (pose === 'front') {
                        try {
                            this.descriptor = JSON.stringify([await FaceID.descriptorFrom(this.$refs.video)]);
                        } catch (e) {
                            this.faceMsg = e.message; // mis. lebih dari satu wajah
                            return setTimeout(() => this.loop(), 400);
                        }
                        this.checks[2].ok = true;
                        this.photo = FaceEngine.snapshot(this.$refs.video);
                        FaceEngine.stop(this.$refs.video); this.cam = 'done';
                        return this.verify();
                    }
                }
            }
            setTimeout(() => this.loop(), 200);
        },
        async verify() {
            this.faceMsg = 'Mencocokkan wajah…';
            try {
                const r = await fetch(cfg.verifyUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                    body: JSON.stringify({ face_descriptor: this.descriptor }),
                });
                const j = await r.json();
                this.verified = r.ok && j.success === true;
                this.faceMsg = j.message || (this.verified ? 'Verifikasi berhasil.' : 'Verifikasi gagal.');
            } catch (e) {
                this.verified = false; this.faceMsg = 'Gagal menghubungi server. Periksa koneksi Anda.';
            }
        },
        retry() { this.startFace(); },
    };
}

/* ===== Pendaftaran wajah biometrik (4 pose) ===== */
function wajahRegister(cfg) {
    return {
        poses: [
            { k: 'front', t: 'Hadap lurus ke kamera.' },
            { k: 'right', t: 'Tolehkan kepala sedikit ke kanan.' },
            { k: 'left', t: 'Tolehkan kepala sedikit ke kiri.' },
            { k: 'front', t: 'Kembali menghadap ke depan.' },
        ],
        shots: [null, null, null, null], descs: [], cam: 'idle', msg: '', hold: 0, agree: false,

        get idx() { return this.descs.length; },
        get complete() { return this.descs.length === 4; },
        get json() { return JSON.stringify(this.descs); },

        async start() {
            this.shots = [null, null, null, null]; this.descs = []; this.hold = 0; this.cam = 'loading';
            try {
                await FaceEngine.load(cfg.models);
                await FaceEngine.start(this.$refs.video);
                this.cam = 'ready'; this.loop();
            } catch (e) {
                this.cam = 'error'; this.msg = 'Kamera atau model wajah tidak dapat dimuat: ' + (e.message || e);
            }
        },
        async loop() {
            if (this.cam !== 'ready') return;
            const det = await FaceEngine.detect(this.$refs.video);
            const want = this.poses[this.idx];
            if (!det) { this.hold = 0; this.msg = 'Wajah tidak terdeteksi.'; }
            else if (FaceEngine.pose(det) === want.k && det.detection.score > 0.6) {
                this.msg = want.t + ' Tahan sebentar…';
                if (++this.hold >= 4) {
                    let d;
                    try { d = await FaceID.descriptorFrom(this.$refs.video); }
                    catch (e) { this.hold = 0; this.msg = e.message; return setTimeout(() => this.loop(), 400); }
                    this.shots[this.idx] = FaceEngine.snapshot(this.$refs.video, this.idx === 0 ? 480 : 240);
                    this.descs.push(d);
                    this.hold = 0;
                    if (this.complete) {
                        FaceEngine.stop(this.$refs.video); this.cam = 'done';
                        this.msg = 'Perekaman selesai. Centang persetujuan lalu simpan.';
                        return;
                    }
                }
            } else { this.hold = 0; this.msg = want.t; }
            setTimeout(() => this.loop(), 200);
        },
    };
}