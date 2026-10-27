Silakan mulai audit keamanan aplikasi sekarang sesuai seluruh ketentuan dalam `cek-penting.md` yang telah kamu pahami.

Lakukan audit secara menyeluruh dan sistematis terhadap source code aplikasi Laravel 13, dengan memprioritaskan pemeriksaan autentikasi, otorisasi multi-role, Broken Access Control/IDOR, manipulasi presensi, keamanan upload file, serta integrasi API.

Ketentuan pelaksanaan:
1. Mulai dengan memetakan struktur proyek, route, middleware, controller, model, policy/gate, FormRequest, migration, konfigurasi keamanan, dan test yang relevan.
2. Lakukan pemeriksaan read-only. Jangan mengubah file, memasang atau memperbarui dependency, menjalankan migrasi yang mengubah database, menghapus data, atau melakukan tindakan destruktif.
3. Periksa alur dari route sampai database. Jangan menyimpulkan suatu fitur aman hanya karena validasi tersedia di frontend atau karena aplikasi menggunakan fitur bawaan Laravel.
4. Catat setiap temuan dengan bukti kode, path file, nomor baris, skenario penyalahgunaan, dampak, tingkat risiko, dan rekomendasi perbaikan.
5. Pisahkan temuan menjadi:
   - Confirmed vulnerability: bukti source code cukup untuk memastikan kelemahan.
   - Requires validation: terdapat indikasi risiko yang perlu pengujian lebih lanjut.
   - Informational: catatan keamanan yang belum menunjukkan kerentanan langsung.
6. Jika membutuhkan pengujian dinamis, jelaskan prasyarat, langkah pengujian yang aman, dan hasil yang perlu diamati. Jangan melakukan pengujian destruktif atau mengakses sistem eksternal tanpa izin.
7. Jangan memperbaiki kode selama tahap audit. Jika menemukan kerentanan kritis, laporkan segera beserta mitigasi sementara yang disarankan.
8. Jangan menyatakan aplikasi aman sepenuhnya jika pemeriksaan belum mencakup semua komponen atau pengujian dinamis belum dilakukan.

Hasil akhir yang saya minta:
- Ringkasan eksekutif dan tingkat risiko keseluruhan.
- Peta komponen dan alur akses yang telah diperiksa.
- Laporan setiap temuan dengan bukti kode dan nomor baris.
- Daftar area yang belum dapat diverifikasi.
- Matriks prioritas perbaikan berdasarkan risiko dan dampak.
- Rencana mitigasi bertahap, dimulai dari Critical dan High.
- Daftar pengujian regresi dan pengujian keamanan yang direkomendasikan.

Setelah laporan audit selesai, berhenti dan tunggu persetujuan saya sebelum melakukan perubahan kode apa pun.

Mulai audit sekarang dan laporkan perkembangan berdasarkan temuan nyata dari source code, bukan asumsi.