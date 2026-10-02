Saya ingin menambahkan fitur **Tugas Luar (TL)** pada sistem presensi magang Laravel yang sudah berjalan.

**Konteks sistem saat ini:**

* Role pengguna: Admin, Pembimbing, Magang.
* Presensi saat ini memiliki mode kerja **Onsite** dan **WFH**.
* Status presensi saat ini: `hadir`, `izin`, `sakit`, `cuti`, `alpa`.
* Peserta melakukan presensi masuk dengan foto dan validasi lokasi untuk Onsite.
* WFH tidak menggunakan validasi radius lokasi kantor.
* Pengajuan Izin/Sakit/Cuti sudah memiliki mekanisme verifikasi oleh Pembimbing.
* Jangan merusak fitur presensi, izin, sakit, cuti, WFH, lokasi, foto, aktivitas, dan penilaian yang sudah ada.

## KONSEP FITUR TUGAS LUAR

Jangan menjadikan Tugas Luar sebagai status ketidakhadiran seperti Izin/Sakit/Cuti.

Tugas Luar adalah kondisi ketika peserta **tetap dianggap hadir**, tetapi menjalankan tugas di luar lokasi kantor.

Status utama tetap:

* Hadir
* Izin
* Sakit
* Cuti
* Alpa

Sedangkan Tugas Luar menjadi informasi/kondisi khusus pada presensi.

Ada dua skenario:

### SKENARIO 1 — Dari awal memang Tugas Luar

Peserta belum melakukan presensi masuk dan sejak awal hari sudah mendapat tugas di luar kantor.

Pada halaman presensi, pilihan mode kerja menjadi:

* Onsite
* WFH
* Tugas Luar

Jika peserta memilih **Tugas Luar**, jangan tampilkan validasi radius lokasi kantor seperti pada Onsite.

Tampilkan form khusus Tugas Luar:

* Tujuan/lokasi tugas luar
* Keperluan/kegiatan
* Waktu mulai
* Waktu selesai (boleh mengikuti mekanisme yang sudah ada jika sistem memang menggunakan absen keluar)
* Bukti tugas luar

Bukti dapat berupa:

* Surat tugas
* Foto kegiatan
* Dokumen pendukung
* Bukti lain yang diizinkan sistem

Setelah peserta mengajukan, status menjadi:

**Menunggu Verifikasi Pembimbing**

Jika disetujui:

* Status presensi = `hadir`
* Kondisi/keterangan = `tugas_luar`
* Data tugas luar tersimpan lengkap
* Peserta tetap dihitung hadir dalam rekap dan perhitungan nilai presensi.

### SKENARIO 2 — Sudah absen masuk Onsite, kemudian mendapat Tugas Luar

Contoh:

08.00 peserta datang ke kantor dan melakukan presensi masuk Onsite.

10.00 peserta mendapat tugas dari pembimbing untuk keluar kantor.

Jangan mengubah mode presensi masuk dari Onsite menjadi Tugas Luar.

Data awal tetap:

* Mode masuk = `onsite`
* Status = `hadir`
* Jam masuk tetap tersimpan.

Setelah berhasil absen masuk, tampilkan tombol:

**+ Ajukan Tugas Luar**

Saat tombol ditekan, tampilkan form:

* Tujuan/lokasi tugas luar
* Keperluan/kegiatan
* Waktu mulai
* Waktu selesai
* Bukti tugas luar

Setelah diajukan:
**Menunggu Verifikasi Pembimbing**

Jika disetujui:

* Status tetap `hadir`
* Presensi tetap memiliki mode masuk `onsite`
* Tambahkan informasi/kondisi `tugas_luar`
* Simpan waktu mulai dan selesai tugas luar
* Simpan bukti tugas luar
* Jam masuk tidak boleh berubah.

Tampilan riwayat menjadi kurang lebih:

**Hadir — Tugas Luar**

* Jam masuk: 08.00
* Mode masuk: Onsite
* Tugas luar: 10.00–15.00
* Tujuan: ...
* Keperluan: ...
* Bukti: tersedia

## STRUKTUR DATA

Gunakan struktur yang tidak merusak enum/status presensi yang sudah ada.

Jangan mengganti status `hadir` menjadi `tugas_luar`.

Jika diperlukan, buat tabel/model khusus untuk pengajuan Tugas Luar, misalnya:

`pengajuan_tugas_luar`

Relasikan dengan:

* pengguna/magang
* presensi
* pembimbing yang melakukan verifikasi

Field yang dapat digunakan antara lain:

* id
* presensi_id nullable
* magang_id / pengguna_id sesuai struktur relasi yang sudah ada
* tujuan
* keperluan
* waktu_mulai
* waktu_selesai
* bukti
* status_verifikasi (`menunggu`, `disetujui`, `ditolak`)
* catatan_pembimbing nullable
* verified_by nullable
* verified_at nullable
* timestamps

Sebelum membuat migration/model baru, periksa struktur database dan model yang sudah ada agar tidak membuat duplikasi atau relasi yang bertentangan.

## ALUR VERIFIKASI PEMBIMBING

Tambahkan menu/halaman untuk Pembimbing melihat pengajuan Tugas Luar.

Pembimbing dapat:

* melihat detail pengajuan
* melihat peserta
* melihat tanggal
* melihat tujuan
* melihat keperluan
* melihat waktu
* melihat bukti
* menyetujui
* menolak
* memberikan catatan

Jika disetujui, sistem harus otomatis menghubungkan data Tugas Luar dengan presensi hari tersebut.

Jika ditolak:

* presensi tidak boleh dianggap Tugas Luar
* berikan informasi penolakan kepada peserta.

## TAMPILAN PESERTA

Pada halaman presensi, buat UI dinamis.

Jika memilih:

**Onsite**
→ tampilkan lokasi/radius + kamera/foto seperti mekanisme sekarang.

**WFH**
→ gunakan mekanisme WFH yang sudah ada.

**Tugas Luar**
→ sembunyikan validasi radius kantor dan tampilkan form Tugas Luar + upload bukti.

Jika peserta sudah melakukan presensi masuk:
→ jangan tampilkan pilihan presensi masuk lagi.
→ tampilkan tombol **Ajukan Tugas Luar**.

## RIWAYAT PRESENSI

Pastikan Tugas Luar dapat terlihat jelas di:

* Dashboard peserta
* Riwayat presensi peserta
* Riwayat presensi admin
* Riwayat pembimbing
* Detail presensi
* Rekap/laporan presensi
* Cetak laporan jika relevan

Contoh tampilan:

`Hadir — Tugas Luar`

Jangan menampilkan Tugas Luar sebagai `Izin`, `Sakit`, atau `Cuti`.

## PERHITUNGAN PRESENSI

Tugas Luar yang sudah disetujui harus dihitung sebagai **Hadir**.

Jangan sampai Tugas Luar:

* dihitung sebagai ketidakhadiran
* mengurangi jumlah hari hadir
* otomatis menjadi alpa
* mengganggu perhitungan skor presensi yang sudah ada.

Periksa `PresensiScoreService` dan seluruh query rekap agar implementasi Tugas Luar tidak mengubah perhitungan yang sudah berjalan.

## ABsen KELUAR

Periksa mekanisme absen keluar yang sekarang.

Untuk Tugas Luar dari awal:

* jangan memaksa peserta melakukan validasi radius kantor saat berada di luar kantor.
* gunakan mekanisme yang paling sesuai dengan struktur presensi yang sudah ada.

Untuk peserta yang awalnya Onsite lalu Tugas Luar:

* jangan mengubah jam masuk.
* jangan merusak mekanisme aktivitas wajib sebelum absen keluar.
* jika absen keluar harus dilakukan dari lokasi tugas luar, sesuaikan mekanismenya tanpa menghilangkan validasi keamanan yang sudah ada.

Jika ada konflik dengan mekanisme lama, jelaskan terlebih dahulu bagian yang perlu disesuaikan dan gunakan solusi yang paling konsisten dengan arsitektur aplikasi.

## ADMIN

Admin harus dapat:

* melihat daftar pengajuan Tugas Luar
* melihat detail
* melihat status verifikasi
* melihat bukti
* melakukan koreksi jika memang fitur koreksi presensi admin sudah tersedia

Jangan membuat menu admin yang tidak diperlukan jika fitur tersebut sebenarnya bisa ditempatkan pada menu Presensi yang sudah ada.

## KETENTUAN IMPLEMENTASI

Sebelum coding:

1. Periksa struktur model, migration, controller, route, Blade, dan service yang berkaitan dengan presensi.
2. Identifikasi file yang perlu diubah.
3. Jangan membuat ulang fitur yang sudah tersedia.
4. Jangan menggunakan `migrate:fresh`.
5. Buat migration baru untuk perubahan database.
6. Pertahankan data presensi lama.
7. Pertahankan mekanisme Onsite, WFH, GPS, foto, aktivitas, izin/sakit/cuti, dan penilaian yang sudah berjalan.
8. Gunakan naming convention Laravel yang konsisten dengan project.
9. Pastikan authorization berdasarkan role tetap aman.
10. Validasi upload bukti dengan benar.
11. Gunakan storage Laravel yang sudah digunakan project jika memungkinkan.
12. Jangan menghapus kode lama sebelum memastikan penggantinya bekerja.

Setelah implementasi, berikan:

* daftar file yang dibuat/diubah
* migration
* model
* controller
* route
* Blade/UI
* relasi model
* perubahan pada service/query/rekap jika ada
* langkah menjalankan migration
* langkah testing

Berikan kode lengkap untuk setiap file yang memang perlu dibuat atau diubah, bukan hanya potongan kode.

Pastikan hasil akhirnya mendukung **dua skenario Tugas Luar**:

1. Tugas Luar sejak awal → peserta memilih Tugas Luar sebelum presensi.
2. Sudah absen masuk Onsite → kemudian mengajukan Tugas Luar tanpa mengubah jam masuk dan mode masuk Onsite.
