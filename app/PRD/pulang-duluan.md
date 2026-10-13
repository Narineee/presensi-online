### Penyesuaian Presensi Pulang Sebelum Pukul 16.00

Sesuaikan fitur presensi pulang pada sistem SEIRAMA dengan ketentuan berikut:

1. **Presensi pulang normal** tetap mengikuti aturan yang sudah ada, yaitu dapat dilakukan mulai pukul 16.00.

2. Tambahkan pilihan alasan ketika peserta ingin melakukan presensi pulang sebelum pukul 16.00, yaitu:
   - Sakit
   - Izin

3. Jika peserta memilih Sakit atau Izin, izinkan peserta melakukan presensi pulang sebelum pukul 16.00 tanpa harus menunggu jam pulang normal.

4. Catat jam pulang secara otomatis berdasarkan waktu aktual ketika peserta menekan tombol presensi pulang dan proses presensi berhasil.

5. Simpan alasan yang dipilih ke bagian **Catatan** pada data presensi. Contoh:
   - Jam pulang: 14.00 — Catatan: Sakit.
   - Jam pulang: 13.30 — Catatan: Izin.

6. Jangan membuat status baru, label khusus, atau keterangan tambahan seperti "Pulang Lebih Awal". Jangan mengubah struktur status utama hanya untuk fitur ini.

7. Jika peserta sudah melakukan presensi masuk, status utama tetap Hadir meskipun pulang sebelum pukul 16.00 karena sakit atau izin. Jangan mengubahnya menjadi Sakit atau Izin seharian.

8. Pertahankan validasi yang sudah ada, termasuk kewajiban mengisi aktivitas harian, validasi lokasi untuk presensi onsite, pengambilan foto, dan aturan WFH. Jangan mengizinkan presensi pulang ganda.

9. Pastikan catatan alasan tersimpan di database dan tampil pada riwayat presensi, rekapitulasi, serta laporan cetak yang relevan. Jam masuk dan jam pulang harus tetap menggunakan waktu aktual, bukan waktu yang diinput manual.

10. Periksa implementasi controller, model, validasi, dan Blade yang sudah ada sebelum melakukan perubahan. Jangan merusak fitur lain dan jangan menjalankan migrate:fresh. Jika database perlu diubah, buat migration tambahan yang aman untuk data yang sudah ada.

Setelah implementasi, uji presensi pulang normal, pulang sebelum pukul 16.00 karena sakit, pulang karena izin, serta integrasi catatan dengan riwayat dan laporan.