# Sistem Informasi Presensi Digital & Monitoring Aktivitas Magang
### Dinas Komunikasi dan Informatika Provinsi Kalimantan Selatan

Platform presensi digital dan pemantauan aktivitas peserta magang/PKL berbasis web yang mengintegrasikan validasi lokasi fisik (**Geofencing GPS**) dan verifikasi biometrik wajah (**AI Face Recognition & Liveness Detection**).

---

## 🚀 Tumpukan Teknologi (Technology Stack)

Sistem ini dibangun dengan arsitektur modern berkinerja tinggi:

- **Backend Framework**: Laravel 11 / 12 (PHP 8.3)
- **Basis Data**: MySQL / MariaDB (InnoDB Engine)
- **Frontend & Styling**: Blade Templating, Tailwind CSS, Glassmorphism UI
- **State Management & UI**: Alpine.js & Vanilla JavaScript
- **Geolokasi & Pemetaan**: HTML5 Geolocation API, Leaflet.js v1.9.4, OpenStreetMap, Nominatim Reverse Geocoding API, Formula Haversine
- **Biometrik & Computer Vision**: HTML5 MediaDevices API, Canvas API, Face-API.js (`@vladmandic/face-api` v1.7.13 / WebGL TensorFlow.js), Tiny Face Detector, Face Landmark 68 Net, Face Recognition Net (ResNet-34 FaceNet)
- **Sinkronisasi Hari Libur**: API Indonesia (`use.apiindonesia.id/api/v1/libur`) via HTTP Header `x-api-key`
- **Mesin Cetak Dokumen Kedinasan**: mPDF Engine (Standar Dokumen A4 Resmi Pemprov Kalsel)

---

## 📍 1. Teknologi Geolokasi & Geofencing GPS

Fitur geolokasi memastikan presensi hanya dapat dilakukan jika peserta berada di lokasi yang ditentukan (kantor Diskominfo atau titik divisi terkait):

1. **HTML5 Geolocation API (`navigator.geolocation`)**:
   - Berjalan dengan opsi `enableHighAccuracy: true` untuk memprioritaskan hardware GPS dan sinyal WiFi berpresisi tinggi.
   - Dilengkapi `maximumAge: 0` untuk menolak penggunaan titik lokasi *cache* lama.
2. **Leaflet.js (v1.9.4) & OpenStreetMap**:
   - Merender visualisasi peta interaktif ringan tanpa ketergantungan API berbayar.
   - Menampilkan marker titik pengguna (`L.circleMarker`) dan zona radius kantor (`L.circle`).
3. **Nominatim Reverse Geocoding API**:
   - Mengonversi titik koordinat $(lat, lng)$ menjadi nama alamat jalan dan kelurahan fisik yang mudah dibaca dan diaudit.
4. **Formula Haversine (Great-Circle Distance)**:
   - Menghitung jarak terpendek antara titik koordinat pengguna dengan titik kantor dinas:
     $$d = 2R \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta \phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta \lambda}{2}\right)}\right)$$
     (dengan $R = 6.371.000\text{ meter}$).
   - **Validasi Ganda**: Dihitung di sisi frontend (peringatan visual real-time) dan divalidasi ulang di backend (`PresensiController::calculateDistance`) sebelum presensi tersimpan ke database.
5. **Kebijakan Radius Presensi**:
   - **ONSITE**: Wajib berada dalam radius batas ($\le 40\text{ meter}$). Tombol presensi otomatis terkunci jika di luar radius.
   - **WFH**: Radius dilewati, namun koordinat riil tetap dicatat ke sistem.
   - **Tugas Luar**: Mengharuskan pengisian surat tugas dan persetujuan pembimbing lapangan.

---

## 👤 2. Teknologi Verifikasi Wajah Biometrik (AI Face Recognition)

Fitur verifikasi wajah memastikan bahwa yang melakukan absensi adalah peserta yang bersangkutan (mencegah titip absen) dan dilakukan secara langsung:

1. **HTML5 MediaDevices & Canvas API**:
   - Mengakses feed video kamera depan (`facingMode: 'user'`) secara real-time melalui protokol HTTPS.
   - Mengambil cuplikan (*snapshot frame*) dan mengompresinya menjadi foto JPEG berkualitas 80% (base64).
2. **Face-API.js (`@vladmandic/face-api` v1.7.13)**:
   - Menjalankan inferensi *deep learning* langsung di peramban klien (*client-side AI*) menggunakan akselerasi grafis WebGL / TensorFlow.js, menghemat daya komputasi server.
   - Menggunakan 3 model neural network terlatih:
     - **Tiny Face Detector**: Model berbasis MobileNet-SSD ringan (~190 KB) untuk mendeteksi kotak batas wajah (*bounding box*) dengan FPS tinggi ($15 - 30\text{ FPS}$).
     - **Face Landmark 68 Net**: Memetakan 68 titik kontur anatomis wajah (mata, hidung, bibir, rahang) secara presisi.
     - **Face Recognition Net**: Jaringan konvolusional (ResNet-34 FaceNet) yang mengekstrak geometri citra wajah menjadi **Vektor Embedding 128 Dimensi (*128-D Descriptors*)**.
3. **Pemeriksaan Keaktifan / Liveness Detection (Anti-Spoofing)**:
   - Mencegah manipulasi presensi menggunakan foto cetak atau foto dari layar HP lain.
   - Menggunakan perhitungan rasio landmark ujung hidung (#30) terhadap tepi kiri (#0) dan kanan (#16) rahang:
     $$\text{Rasio Pose} = \frac{X_{\text{landmarks}[30]} - X_{\text{landmarks}[0]}}{X_{\text{landmarks}[16]} - X_{\text{landmarks}[0]}}$$
   - **Tantangan 3 Pose Sekuensial**:
     1. Menghadap lurus ke kamera (`pose === 'front'`, skor $> 0.6$).
     2. Menolehkan kepala ke kanan (`pose === 'right'`, rasio $< 0.38$).
     3. Kembali menghadap ke depan (`pose === 'front'`, rasio $0.42 - 0.58$).
4. **Pencocokan Biometrik di Server (`FaceVerificationService`)**:
   - Backend menghitung **Jarak Euclidean (*Euclidean Distance*)** antara vektor presensi (*probe*) dengan 4 sampel wajah yang didaftarkan saat orientasi:
     $$D(A, B) = \sqrt{\sum_{i=1}^{128} (A_i - B_i)^2}$$
   - Jika jarak minimum $D_{min} \le 0.50$ (ambang batas `config('face.threshold')`), wajah dinyatakan **cocok dan sah**.

---

## 📅 3. Sinkronisasi Hari Libur Nasional & Cuti Bersama (API Indonesia)

Sistem mengintegrasikan data hari libur nasional dan cuti bersama resmi pemerintah RI untuk dispensasi hari kerja presensi:

1. **Penyedia API**: **API Indonesia** (`https://docs.apiindonesia.id/docs/#hari-libur`)
2. **Endpoint**: `GET https://use.apiindonesia.id/api/v1/libur?tahun={tahun}`
3. **Autentikasi**: Header `x-api-key: {API_KEY}` via konfigurasi `.env` (`HARI_LIBUR_API_KEY`).
4. **Resilient Local Database (Single Source of Truth)**:
   - Presensi harian dan perhitungan menit kerja (`PresensiScoreService`) **tidak pernah memanggil API secara langsung**. Data tersimpan di tabel lokal `hari_libur`, sehingga sistem tetap bekerja 100% normal meski koneksi internet terputus atau server API eksternal sedang down.
5. **Proteksi Data Libur Manual Admin**:
   - Hari libur lokal kedinasan (misal: HUT Pemprov Kalsel) yang diinput manual oleh admin (`sumber = 'manual'`) diproteksi agar tidak tertimpa saat sinkronisasi API dijalankan.
6. **Kalkulasi Jam Kerja Magang**:
   - Tanggal merah resmi secara otomatis memotong target hari kerja magang, sehingga peserta tidak diwajibkan hadir dan terhindar dari penalti alpa/mangkir.

---

## 🔑 Fitur Utama Sistem

- **Multi-Penempatan & Riwayat Rotasi Magang**: Mendukung peserta berpindah divisi dan pembimbing tanpa merusak riwayat presensi maupun aktivitas sebelumnya.
- **Log Aktivitas Harian Format Baru (6 Kolom)**: Pelaporan berbasis rencana kerja dengan output terukur (**Selesai** / **Belum**) dan alasan kendala.
- **Kalkulasi Otomatis Skor Presensi Kedinasan (`PresensiScoreService`)**: Menghitung jam kerja efektif, toleransi keterlambatan, penalti lupa checkout, dan kehadiran tugas luar (skala 0 - 100).
- **Evaluasi Penilaian Akhir Terintegrasi**: Menggabungkan penilaian kedisiplinan objektif dari log absensi dengan penilaian kompetensi teknis dari pembimbing lapangan (*Weighted Average*).
- **Standarisasi Dokumen Cetak Kedinasan (mPDF)**: Format cetak PDF A4 presisi sesuai tata naskah dinas Pemprov Kalsel (Kop Surat resmi, tanda tangan ganda pembimbing & kepala bidang, serta sertifikat kelulusan).

---

## 📋 Panduan Seminar Hasil / Sidang Akhir

Dokumen spesifikasi lengkap, alur bisnis, formula matematis, serta daftar pertanyaan penguji dan jawaban solutif tersedia pada file:  
👉 [`app/PRD/RANGKUMAN_SISTEM_SEMINAR_HASIL.md`](app/PRD/RANGKUMAN_SISTEM_SEMINAR_HASIL.md)

---

## 🛠️ Instalasi & Menjalankan Aplikasi

1. Clone repositori dan masuk ke direktori:
   ```bash
   git clone <repository-url>
   cd presensi-digital
   ```
2. Pasang dependensi PHP dan Node.js:
   ```bash
   composer install
   npm install
   ```
3. Konfigurasi file lingkungan:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan server lokal:
   ```bash
   npm run build
   php artisan serve
   ```
