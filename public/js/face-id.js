window.FaceID = (function () {
    // Muat model lokal dari /models/ (super cepat) dengan fallback CDN jika diperlukan
    const LOCAL_MODEL_URL = '/models/';
    const CDN_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model/';
    let loading = null;

    function load() {
        if (!loading) {
            loading = Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(LOCAL_MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(LOCAL_MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(LOCAL_MODEL_URL),
            ]).catch(err => {
                console.warn('Gagal memuat model lokal, beralih ke CDN:', err);
                return Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(CDN_MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(CDN_MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(CDN_MODEL_URL),
                ]);
            });
        }
        return loading;
    }

    // source: <canvas>, <img>, atau <video>. Melempar Error jika wajah 0 atau lebih dari 1.
    async function descriptorFrom(source) {
        await load();
        let opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.35 });
        let faces = await faceapi.detectAllFaces(source, opts).withFaceLandmarks().withFaceDescriptors();
        if (faces.length === 0) {
            // Fallback toleran jika pencahayaan agak redup atau sudut sedikit bergeser
            const fallbackOpts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.2 });
            faces = await faceapi.detectAllFaces(source, fallbackOpts).withFaceLandmarks().withFaceDescriptors();
        }
        if (faces.length === 0) throw new Error('Wajah tidak terdeteksi pada foto.');
        if (faces.length > 1) throw new Error('Terdeteksi lebih dari satu wajah.');
        return Array.from(faces[0].descriptor);
    }

    return { 
        load, 
        descriptorFrom, 
        getDescriptorFromImage: descriptorFrom 
    };
})();