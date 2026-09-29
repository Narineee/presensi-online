Saya memiliki aplikasi **Presensi Magang berbasis Laravel 13**. Saya ingin menambahkan dan menyempurnakan fitur **pengelolaan hari libur nasional/cuti bersama** serta mengintegrasikannya dengan **perhitungan nilai presensi peserta magang**.

Sebelum melakukan perubahan, **periksa dan pahami struktur project yang sudah ada terlebih dahulu**. Jangan langsung membuat file atau mengubah kode sebelum memahami model, migration, controller, service, route, Blade, scheduler, dan struktur database yang sudah digunakan.

## 1. TUJUAN UTAMA

Saya ingin sistem dapat:

1. Menyimpan data hari libur di database lokal.
2. Mengambil/sinkronisasi data hari libur dari API.
3. Memungkinkan admin menambahkan hari libur secara manual.
4. Memungkinkan admin mengedit dan menghapus data hari libur.
5. Memungkinkan admin melakukan sinkronisasi ulang berdasarkan tahun.
6. Mendukung tahun yang berbeda secara dinamis, misalnya 2026, 2027, 2028, dan seterusnya.
7. Tidak bergantung langsung kepada API ketika menghitung nilai presensi.
8. Menggunakan tabel `hari_libur` sebagai **source of truth** untuk perhitungan presensi.
9. Menghindari data hari libur duplikat.
10. Tetap dapat menghitung nilai presensi apabila API sedang tidak dapat diakses.
11. Mengintegrasikan hari libur dengan `PresensiScoreService`.
12. Menampilkan informasi hari libur secara transparan kepada admin/pembimbing jika diperlukan.

---

# 2. FITUR ADMIN — KELOLA HARI LIBUR

Tambahkan menu baru pada halaman admin:

**Kelola Hari Libur**

Contoh struktur menu:

* Dashboard
* Kelola Peserta Magang
* Kelola Pembimbing
* Kelola Divisi
* Kelola Kriteria 
* **Kelola Hari Libur**

Menu **Kelola Hari Libur** harus memiliki halaman utama berupa tabel.

## Tabel Hari Libur

Kolom minimal:

| Kolom           | Keterangan                         |
| --------------- | ---------------------------------- |
| Tanggal         | Tanggal hari libur                 |
| Nama Hari Libur | Contoh: Tahun Baru                 |
| Jenis           | Hari Libur Nasional / Cuti Bersama |
| Tahun           | Tahun dari tanggal                 |
| Sumber          | API / Manual                       |
| Keterangan      | Opsional                           |
| Aksi            | Edit / Hapus                       |

Gunakan tampilan yang mengikuti desain/interface aplikasi yang sudah ada. Jangan membuat desain yang berbeda jauh dari halaman admin lainnya.

---

# 3. FORM TAMBAH HARI LIBUR

Buat tombol:

**+ Tambah Hari Libur**

Form minimal:

* Tanggal
* Nama hari libur
* Jenis

  * Hari Libur Nasional
  * Cuti Bersama
* Keterangan (opsional)

Contoh:

Tanggal:
`17-08-2026`

Nama:
`Hari Kemerdekaan Republik Indonesia`

Jenis:
`Hari Libur Nasional`

Keterangan:
`Hari libur nasional`

Data yang dibuat melalui form ini harus disimpan dengan:

`sumber = manual`

Lakukan validasi:

* tanggal wajib diisi
* nama wajib diisi
* jenis wajib diisi
* tanggal harus berupa tanggal valid
* jangan izinkan data duplikat untuk tanggal + jenis yang sama

---

# 4. FORM EDIT HARI LIBUR

Admin dapat mengedit:

* tanggal
* nama hari libur
* jenis
* keterangan

Jika data berasal dari API, admin tetap boleh mengubah data jika sistem memang mengizinkannya.

Namun jangan sampai sinkronisasi API berikutnya menyebabkan data manual atau perubahan admin hilang tanpa alasan.

Gunakan mekanisme yang aman untuk membedakan:

* data API
* data manual
* data yang telah diedit admin

---

# 5. HAPUS HARI LIBUR

Admin dapat menghapus data hari libur.

Sebelum menghapus, tampilkan konfirmasi.

Jangan menggunakan `migrate:fresh`, truncate, atau operasi destruktif terhadap tabel lain.

Penghapusan hari libur tidak boleh menghapus data:

* presensi
* aktivitas
* penilaian
* peserta magang
* pembimbing

---

# 6. SINKRONISASI API HARI LIBUR

Tambahkan tombol:

**Sinkronkan Hari Libur**

Admin dapat memilih tahun.

Contoh:

`Tahun: 2026`

Kemudian:

`[ Sinkronkan Hari Libur 2026 ]`

Sistem mengambil data dari API hari libur yang digunakan project.

PENTING:

* Jangan hard-code tahun 2026.
* Tahun harus dinamis.
* Jika admin memilih 2027, sistem mengambil data 2027.
* Jika memilih 2028, sistem mengambil data 2028.
* Gunakan konfigurasi `.env` untuk API key/token jika API membutuhkannya.
* Jangan menyimpan API key langsung di source code.

Contoh `.env` jika diperlukan:

`HOLIDAY_API_KEY=...`

Nama variabel boleh disesuaikan dengan API yang benar-benar digunakan.

---

# 7. HASIL SINKRONISASI API

Data dari API harus masuk ke tabel `hari_libur`.

Jangan langsung menggunakan data API untuk menghitung nilai presensi.

Alurnya:

API
↓
Validasi/normalisasi data
↓
Simpan/update ke `hari_libur`
↓
PresensiScoreService membaca `hari_libur`

Gunakan mekanisme seperti `updateOrCreate()` atau mekanisme setara agar sinkronisasi tidak membuat data duplikat.

Identitas data minimal dapat menggunakan:

* tanggal
* jenis

atau kombinasi identifier yang paling tepat berdasarkan struktur database.

---

# 8. SUMBER DATA

Tabel `hari_libur` harus dapat membedakan sumber data.

Contoh:

`sumber = api`

atau:

`sumber = manual`

Jika diperlukan, tambahkan field:

* `sumber`
* `external_id`
* `last_synced_at`

Tetapi jangan menambahkan kolom yang tidak diperlukan.

Periksa struktur database terlebih dahulu sebelum membuat migration.

---

# 9. JIKA API GAGAL

Jika API tidak dapat diakses:

* jangan membuat aplikasi error total
* jangan menghapus data hari libur yang sudah ada
* jangan menghapus data presensi
* jangan menghapus data penilaian

Tampilkan pesan yang jelas kepada admin, misalnya:

"Sinkronisasi gagal. Data hari libur yang tersimpan sebelumnya tetap digunakan."

Jika memungkinkan, simpan log error menggunakan Laravel logging.

---

# 10. SCHEDULER OTOMATIS

Tambahkan mekanisme scheduler Laravel untuk melakukan sinkronisasi otomatis.

Tujuannya agar admin tidak harus memasukkan tahun baru secara manual setiap tahun.

Contoh:

Saat memasuki tahun baru, sistem dapat melakukan sinkronisasi hari libur untuk tahun tersebut.

Namun jangan hanya mengandalkan pergantian tahun.

Jika memungkinkan, scheduler melakukan sinkronisasi secara berkala dengan frekuensi yang wajar.

Contoh:

* sinkronisasi tahun berjalan
* dapat juga mempersiapkan tahun berikutnya

Jangan melakukan request API secara berlebihan.

Gunakan Laravel Scheduler sesuai struktur Laravel 13 project.

Sebelum membuat scheduler, periksa apakah project sudah memiliki scheduler/console commands.

---

# 11. MANUAL SYNC TETAP HARUS ADA

Walaupun scheduler otomatis dibuat, admin tetap harus mempunyai tombol:

**Sinkronkan Sekarang**

Tujuannya apabila:

* ada perubahan data hari libur
* ada penambahan cuti bersama
* sinkronisasi otomatis gagal
* admin ingin memperbarui data

---

# 12. JANGAN HAPUS DATA LAMA SECARA SEMBARANGAN

Ketika sinkronisasi API dilakukan:

JANGAN melakukan:

`DELETE FROM hari_libur`

kemudian memasukkan ulang semua data.

Gunakan mekanisme sinkronisasi yang aman.

Alasannya:

Data lama dapat sudah digunakan sebagai referensi sistem.

Jika API tidak mengembalikan data tertentu, jangan langsung menganggap data tersebut harus dihapus.

Prioritaskan keamanan dan konsistensi data.

---

# 13. STRUKTUR DATABASE HARI LIBUR

Jika belum ada tabel `hari_libur`, buat migration.

Struktur minimal yang dibutuhkan:

* id
* tanggal
* nama
* jenis
* sumber
* keterangan
* created_at
* updated_at

Sesuaikan nama kolom dengan standar project yang sudah ada.

Tambahkan index/unique constraint yang sesuai untuk mencegah duplikasi.

Jangan membuat struktur yang bertentangan dengan database existing.

---

# 14. INTEGRASI DENGAN PRESENSI SCORE SERVICE

Sistem saya memiliki konsep penilaian presensi.

Gunakan aturan berikut.

## Jam kerja

Jam kerja:

`08:00 - 16:00`

Total:

`8 jam = 480 menit`

Hari kerja:

`Senin - Jumat`

Sabtu dan Minggu bukan hari kerja.

---

# 15. PERHITUNGAN TARGET MENIT

Target menit dihitung berdasarkan jumlah hari kerja Senin-Jumat dalam periode magang.

Rumus:

`Target Menit = Jumlah Hari Kerja × 480`

Contoh:

Jika terdapat 80 hari kerja:

`80 × 480 = 38.400 menit`

Jangan selalu menggunakan angka 38.400.

Angka tersebut hanya contoh.

Sistem harus menghitung secara dinamis berdasarkan:

`tanggal_mulai` peserta magang

sampai

`tanggal_selesai` peserta magang.

---

# 16. HARI LIBUR

Hari libur nasional dan cuti bersama yang tercatat di tabel `hari_libur` tidak boleh dihitung sebagai hari kerja.

Contoh:

Senin-Jumat = 5 hari

Tetapi salah satu hari adalah hari libur nasional.

Maka:

`5 - 1 = 4 hari kerja efektif`

Target:

`4 × 480 = 1.920 menit`

Sabtu dan Minggu juga tidak dihitung.

---

# 17. MENIT REALISASI PRESENSI

Gunakan batas jam kerja:

Jam masuk efektif:

`max(jam_masuk, 08:00)`

Jam keluar efektif:

`min(jam_keluar, 16:00)`

Kemudian:

`Menit Realisasi = Jam Keluar Efektif - Jam Masuk Efektif`

Contoh 1:

Masuk 08:00
Keluar 16:00

= 480 menit

Contoh 2:

Masuk 08:30
Keluar 16:00

= 450 menit

Contoh 3:

Masuk 08:00
Keluar 15:00

= 420 menit

Contoh 4:

Masuk 07:30
Keluar 16:00

Tetap:

= 480 menit

Karena datang sebelum jam kerja tidak memberikan tambahan menit.

Contoh 5:

Masuk 08:00
Keluar 17:00

Tetap:

= 480 menit

Karena waktu setelah 16:00 tidak dihitung sebagai tambahan.

---

# 18. TIDAK HADIR

Jika peserta tidak memiliki presensi dan tidak memiliki izin/sakit/cuti yang disetujui:

`Menit Realisasi = 0`

---

# 19. IZIN/SAKIT/CUTI YANG DISETUJUI

Jika izin/sakit/cuti sudah disetujui dan tanggal tersebut merupakan hari kerja:

`Menit Realisasi = 480 menit`

Ini berarti izin/sakit/cuti yang sudah disetujui tidak merugikan nilai presensi.

Namun tetap jangan menghitung Sabtu/Minggu sebagai hari kerja.

---

# 20. LUPA CHECKOUT

Jika peserta sudah melakukan check-in tetapi tidak melakukan checkout:

Gunakan aturan:

`50% dari menit potensial hari tersebut`

Contoh:

Masuk 08:00 tetapi tidak checkout:

`480 × 50% = 240 menit`

Jika masuk 08:30 tetapi tidak checkout:

Potensi:

`16:00 - 08:30 = 450 menit`

Kemudian:

`450 × 50% = 225 menit`

Jangan memberikan 480 menit penuh apabila checkout tidak dilakukan.

---

# 21. RUMUS NILAI PRESENSI

Setelah seluruh menit terkumpul:

`Skor Presensi = min(100, round((Total Menit Realisasi / Total Target Menit) × 100))`

Contoh:

Target:

`38.400 menit`

Realisasi:

`35.712 menit`

Maka:

`35.712 / 38.400 × 100`

=

`93`

Jadi:

`Skor Presensi = 93`

Nilai tidak boleh lebih dari 100.

---

# 22. PREDIKAT NILAI PRESENSI

Gunakan:

| Nilai  | Predikat    |
| ------ | ----------- |
| 91–100 | Sangat Baik |
| 80–89  | Baik        |
| 70–79  | Cukup Baik  |
| 60–69  | Kurang Baik |
| <60    | Tidak Baik  |

Pastikan rentang 90/89 ditangani secara eksplisit dan konsisten.

Jangan membuat kondisi yang menyebabkan nilai tertentu tidak memiliki predikat.

---

# 23. INTEGRASI DENGAN PENILAIAN MAGANG

Kriteria **Presensi** harus menjadi kriteria objektif.

Artinya:

* nilainya dihitung otomatis oleh sistem
* pembimbing tidak dapat mengubah nilainya secara manual
* field presensi pada form penilaian dibuat readonly/disabled
* sistem menghitung ulang nilainya ketika diperlukan

Kriteria lain seperti:

* kualitas kerja
* inisiatif
* kerjasama

tetap dapat dinilai manual oleh pembimbing.

---

# 24. CONTOH NILAI AKHIR

Misalnya:

Presensi = 93, bobot 25%

Kualitas Kerja = 85, bobot 30%

Inisiatif = 80, bobot 20%

Kerjasama = 88, bobot 25%

Perhitungan:

`93 × 25% = 23,25`

`85 × 30% = 25,50`

`80 × 20% = 16`

`88 × 25% = 22`

Total:

`86,75`

Sesuaikan pembulatan dengan mekanisme penilaian yang sudah digunakan aplikasi.

---

# 25. TRANSPARANSI PERHITUNGAN

Jangan hanya menampilkan angka nilai presensi.

Admin/pembimbing/peserta harus dapat mengetahui dasar perhitungannya.

Jika memungkinkan tampilkan:

* periode magang
* jumlah hari kerja
* jumlah hari libur
* jumlah hari izin/sakit/cuti disetujui
* target menit
* total menit realisasi
* jumlah hari terlambat
* jumlah hari lupa checkout
* skor presensi
* predikat

Contoh:

```text
Periode Magang
01 Januari 2026 - 30 April 2026

Hari Kerja Efektif : 78 hari
Hari Libur         : 2 hari
Target Menit       : 37.440 menit
Realisasi          : 35.712 menit
Izin/Sakit Disetujui : 2 hari
Lupa Checkout      : 1 hari

Skor Presensi      : 95
Predikat           : Sangat Baik
```

Angka di atas hanya contoh tampilan.

---

# 26. PENTING — PERHITUNGAN HARUS DINAMIS

Jangan hard-code:

* 80 hari
* 38.400 menit
* tahun 2026
* jumlah hari libur tertentu

Semua harus dihitung berdasarkan:

`tanggal_mulai`

`tanggal_selesai`

`hari kerja Senin-Jumat`

dan

`hari_libur` pada database.

---

# 27. HUBUNGAN DENGAN STATUS PRESENSI

Periksa sistem presensi existing.

Status database saat ini dapat berupa:

* hadir
* izin
* sakit
* alpa
* cuti

Jika status `hadir` tetapi keterangannya menunjukkan terlambat, jangan menganggapnya otomatis tepat waktu.

Contoh:

```text
status = hadir
keterangan = Terlambat 30 menit
```

Maka sistem penilaian harus tetap menghitung waktu aktual masuk:

08:30 → 450 menit potensial sampai 16:00.

Jangan mengubah enum database hanya untuk membuat status "terlambat" jika struktur existing memang menggunakan `keterangan`.

---

# 28. SAME-DAY IZIN/SAKIT

Pertahankan aturan existing:

Peserta dapat melakukan presensi hadir terlebih dahulu.

Kemudian peserta masih dapat mengajukan izin/sakit pada tanggal yang sama.

Jika pengajuan disetujui:

* status kehadiran dapat berubah menjadi izin/sakit sesuai aturan aplikasi
* jam masuk tetap tersimpan
* riwayat presensi tetap menampilkan data
* pengajuan izin/sakit tetap tercatat
* perhitungan nilai mengikuti aturan yang telah ditentukan untuk izin/sakit yang disetujui

Jangan menghapus riwayat jam masuk.

Periksa implementasi existing sebelum mengubah bagian ini.

---

# 29. API TIDAK BOLEH MENJADI SATU-SATUNYA SUMBER

Jangan membuat:

`PresensiScoreService → API Hari Libur`

Yang benar:

`PresensiScoreService → Database hari_libur`

Sedangkan API hanya digunakan untuk:

`API → Sinkronisasi → Database hari_libur`

Dengan demikian jika API mati, perhitungan penilaian tetap dapat berjalan.

---

# 30. KEAMANAN API KEY

Jika API menggunakan API key:

Simpan di:

`.env`

Jangan:

* hard-code API key
* commit API key ke Git
* menampilkan API key pada Blade
* menyimpan API key di JavaScript frontend

Tambahkan konfigurasi ke file config jika diperlukan.

---

# 31. TESTING

Buat atau sesuaikan test untuk memastikan:

1. Senin-Jumat dihitung sebagai hari kerja.
2. Sabtu tidak dihitung.
3. Minggu tidak dihitung.
4. Hari libur nasional tidak dihitung.
5. Cuti bersama tidak dihitung jika jenis tersebut ditetapkan sebagai non-working day.
6. Hadir 08:00-16:00 = 480 menit.
7. Terlambat mengurangi menit.
8. Pulang lebih awal mengurangi menit.
9. Masuk sebelum 08:00 tidak menambah menit.
10. Pulang setelah 16:00 tidak menambah menit.
11. Tidak hadir = 0.
12. Izin/sakit/cuti yang disetujui = 480 menit.
13. Lupa checkout = 50% dari menit potensial.
14. Skor maksimal 100.
15. API sync tidak menghasilkan duplikasi.
16. API gagal tidak menghapus data existing.
17. Data manual tetap ada.
18. Tahun dapat berubah secara dinamis.
19. Sinkronisasi 2026 tidak mencampur data dengan 2027.
20. Target menit sesuai jumlah hari kerja efektif.

---

# 32. STRUKTUR IMPLEMENTASI

Sebelum membuat file baru, periksa apakah project sudah memiliki komponen yang setara.

Komponen yang kemungkinan dibutuhkan:

### Model

`HariLibur.php`

### Migration

Migration tabel `hari_libur`

### Controller

Misalnya:

`HariLiburController.php`

Sesuaikan dengan pola controller existing.

### Service

Misalnya:

`HariLiburService.php`

untuk:

* mengambil data API
* normalisasi
* sinkronisasi
* menangani error

### Command

Misalnya:

`SyncHariLibur.php`

untuk scheduler.

### Service Existing

Gunakan atau sesuaikan:

`PresensiScoreService.php`

Jangan membuat service kedua yang memiliki fungsi sama jika service tersebut sudah tersedia.

---

# 33. ROUTE ADMIN

Tambahkan route sesuai pola route admin existing.

Contoh fungsi:

* index
* create
* store
* edit
* update
* destroy
* sync

Pastikan seluruh route hanya dapat diakses admin sesuai middleware existing.

Jangan membuat middleware baru jika middleware existing sudah dapat digunakan.

---

# 34. BLADE

Buat:

* index hari libur
* create hari libur
* edit hari libur

Jika project menggunakan komponen/layout Blade tertentu, gunakan layout existing.

Jangan mengganti keseluruhan desain aplikasi.

Tambahkan:

* alert sukses
* alert gagal
* validasi error
* confirmation sebelum hapus
* tombol sinkronisasi
* filter tahun jika diperlukan

---

# 35. FILTER TAHUN

Pada halaman Kelola Hari Libur, tambahkan filter tahun.

Contoh:

`2026 ▼`

Jika dipilih 2026:

hanya tampil data 2026.

Jika dipilih 2027:

hanya tampil data 2027.

Tambahkan tombol:

`Sinkronkan Tahun Ini`

sehingga admin tidak perlu mengetik tahun berulang kali.

---

# 36. PERLINDUNGAN DATA

Jangan mengubah atau menghapus:

* presensi lama
* aktivitas lama
* penilaian lama
* peserta magang
* pembimbing
* pengguna

hanya karena fitur hari libur ditambahkan.

Migration harus aman untuk database existing.

---

# 37. MIGRATION

Sebelum menjalankan migration:

1. Periksa migration existing.
2. Periksa apakah tabel `hari_libur` sudah ada.
3. Jika sudah ada, jangan membuat tabel duplikat.
4. Periksa apakah field yang dibutuhkan sudah tersedia.
5. Buat migration tambahan hanya jika memang diperlukan.

Jangan menggunakan:

`php artisan migrate:fresh`

pada database existing saya.

Jangan menghapus seluruh data.

---

# 38. ENV DAN KONFIGURASI

Jika API membutuhkan:

* base URL
* API key
* token

gunakan `.env`.

Contoh:

```env
HOLIDAY_API_URL=
HOLIDAY_API_KEY=
```

Nama dapat disesuaikan dengan API yang digunakan.

Jangan memasukkan credential asli ke dalam source code.

---

# 39. LOGGING

Gunakan Laravel Log untuk mencatat:

* waktu sinkronisasi
* tahun yang disinkronkan
* jumlah data diterima
* jumlah data ditambahkan
* jumlah data diperbarui
* error API jika terjadi

Jangan menyimpan API key/token ke log.

---

# 40. HASIL AKHIR YANG SAYA INGINKAN

Setelah selesai, admin harus dapat melakukan:

```text
Admin
 ↓
Kelola Hari Libur
 ↓
Pilih Tahun 2026
 ↓
Sinkronkan Hari Libur
 ↓
Data API masuk ke database
 ↓
Data tampil di tabel
```

Admin juga dapat:

```text
Kelola Hari Libur
 ↓
Tambah Hari Libur
 ↓
Isi tanggal + nama + jenis
 ↓
Simpan
```

Dan sistem presensi:

```text
Periode Magang
       ↓
Cari Senin-Jumat
       ↓
Cek tabel hari_libur
       ↓
Jika hari libur → jangan dihitung
       ↓
Jika hari kerja → target 480 menit
       ↓
Hitung realisasi presensi
       ↓
Hitung skor presensi
       ↓
Masukkan otomatis ke penilaian
```

---

# 41. ATURAN PENTING SAAT MENGERJAKAN

1. Gunakan Laravel 13.
2. Gunakan struktur project yang sudah ada.
3. Jangan melakukan rewrite seluruh aplikasi.
4. Jangan mengubah fitur yang tidak berkaitan.
5. Jangan menghapus data existing.
6. Jangan menggunakan `migrate:fresh`.
7. Jangan mengubah enum/status existing tanpa alasan yang benar-benar diperlukan.
8. Jangan membuat duplicate model/service/controller jika sudah ada.
9. Ikuti naming convention project.
10. Ikuti UI/layout yang sudah digunakan.
11. Gunakan validation Laravel.
12. Gunakan authorization/middleware admin existing.
13. Gunakan transaction jika diperlukan untuk operasi database.
14. API hanya untuk sinkronisasi.
15. Database `hari_libur` adalah sumber data yang digunakan dalam perhitungan.
16. Sistem harus tetap berjalan jika API sedang down.
17. Tahun harus dinamis.
18. Data manual harus tetap dapat digunakan.
19. Jangan menghapus data lama hanya karena data API berubah.
20. Setelah implementasi, berikan daftar file yang dibuat/diubah.
21. Berikan migration yang perlu dijalankan.
22. Berikan command Artisan yang perlu dijalankan.
23. Berikan langkah menjalankan scheduler.
24. Berikan langkah testing.
25. Jika menemukan bagian existing yang tidak sesuai, jelaskan terlebih dahulu sebelum melakukan perubahan besar.

---

# 42. OUTPUT YANG SAYA INGINKAN DARI ANDA

Setelah menganalisis project:

1. Jelaskan struktur existing yang berkaitan dengan hari libur dan penilaian.
2. Sebutkan file yang perlu dibuat.
3. Sebutkan file yang perlu diubah.
4. Jelaskan perubahan masing-masing file.
5. Berikan kode lengkap untuk setiap file yang dibuat/diubah.
6. Jangan hanya memberikan potongan kode jika file tersebut perlu diganti secara keseluruhan.
7. Berikan migration.
8. Berikan model.
9. Berikan controller.
10. Berikan service.
11. Berikan command/scheduler.
12. Berikan route.
13. Berikan Blade.
14. Berikan test.
15. Berikan `.env.example` atau konfigurasi yang diperlukan.
16. Berikan langkah menjalankan migration.
17. Berikan langkah menjalankan scheduler.
18. Berikan langkah menguji sinkronisasi API.
19. Berikan contoh hasil di database.
20. Berikan contoh perhitungan skor presensi.
21. Jelaskan jika ada bagian yang tidak dapat dipastikan karena API yang digunakan belum diketahui.

**PENTING:** Jangan mengarang endpoint API. Jika endpoint/API yang digunakan belum tersedia di project, beri tahu saya dan gunakan konfigurasi placeholder yang jelas sampai API yang benar ditentukan.
