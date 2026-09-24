window.FaceID = (function () {
    // Bisa diganti ke folder lokal: '/models/' (lihat catatan di akhir)
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
    let loading = null;

    function load() {
        if (!loading) {
            loading = Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
        }
        return loading;
    }

    // source: <canvas>, <img>, atau <video>. Melempar Error jika wajah 0 atau lebih dari 1.
    async function descriptorFrom(source) {
        await load();
        const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });
        const faces = await faceapi.detectAllFaces(source, opts).withFaceLandmarks().withFaceDescriptors();
        if (faces.length === 0) throw new Error('Wajah tidak terdeteksi pada foto.');
        if (faces.length > 1) throw new Error('Terdeteksi lebih dari satu wajah.');
        return Array.from(faces[0].descriptor);
    }

    return { load, descriptorFrom };
})();