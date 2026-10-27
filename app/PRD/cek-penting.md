Bertindaklah sebagai Senior Application Security Engineer yang berpengalaman dalam keamanan aplikasi Laravel 13.

Lakukan audit keamanan terhadap seluruh source code aplikasi presensi magang saya sebelum memberikan rekomendasi perubahan.

Konteks aplikasi:
- Framework Laravel 13 dan database MySQL.
- Role pengguna: admin, pembimbing, dan peserta magang.
- Fitur meliputi login, presensi GPS, selfie dan liveness detection, aktivitas harian, pengajuan izin, penilaian pembimbing, serta integrasi API dengan aplikasi pendaftaran magang.

Periksa secara sistematis:

1. Autentikasi, session, password hashing, dan logout.
2. Authorization pada setiap route, controller, model, dan API.
3. Broken Access Control dan IDOR, termasuk akses data peserta lain.
4. SQL Injection, XSS, CSRF, validasi input, dan mass assignment.
5. Keamanan upload foto dan penyimpanan file.
6. Manipulasi request untuk jam presensi, koordinat GPS, status kehadiran, nilai, dan liveness.
7. Perlindungan API integrasi, autentikasi antar-aplikasi, replay request, dan validasi sumber data.
8. Kebocoran .env, APP_KEY, password, token, log, dan informasi debug.
9. Dependency rentan, konfigurasi produksi, HTTPS, rate limiting, dan pencatatan aktivitas.
10. Risiko akses tidak sah terhadap data pribadi peserta.

Ketentuan:
- Mulai dengan audit read-only. Jangan langsung mengubah kode, menghapus data, menjalankan migrate:fresh, atau mereset database.
- Jangan menganggap kode aman hanya karena menggunakan fitur bawaan Laravel.
- Untuk setiap temuan, jelaskan file dan baris yang terdampak, skenario penyalahgunaan, dampak, tingkat risiko, dan rekomendasi perbaikan.
- Bedakan kerentanan yang terbukti dari dugaan yang masih perlu diuji.
- Periksa kontrol di sisi server, bukan hanya tampilan atau JavaScript.
- Setelah laporan selesai, buat rencana perbaikan berdasarkan prioritas.
- Implementasikan perbaikan hanya setelah saya menyetujui rencananya, lalu jalankan pengujian regresi dan pengujian keamanan yang sesuai.

Jangan menyatakan aplikasi aman sepenuhnya hanya berdasarkan pemeriksaan source code. Laporkan batasan audit dan pengujian yang masih diperlukan.