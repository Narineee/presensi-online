Saya sedang mengembangkan aplikasi web presensi dan aktivitas magang menggunakan Laravel.

Saya ingin menambahkan konsep **Pekerjaan Magang** yang menjadi induk dari aktivitas harian peserta magang.

## TUJUAN FITUR

Pisahkan antara:

1. **Pekerjaan**

   * Pekerjaan/tugas yang diberikan pembimbing kepada peserta magang.
   * Contoh:

     * Membuat Aplikasi Presensi Magang
     * Membuat Video Profil
     * Melayani Tamu
     * Menginput Data
   * Satu pekerjaan dapat memiliki banyak aktivitas harian.

2. **Aktivitas Harian**

   * Catatan pekerjaan yang benar-benar dilakukan peserta pada tanggal tertentu.
   * Peserta memilih pekerjaan dari dropdown, kemudian menuliskan aktivitas hari itu.
   * Peserta TIDAK mengisi progress pekerjaan.

3. **Progress**

   * Hanya digunakan untuk pekerjaan yang memang memiliki proses sampai selesai.
   * Progress diisi dan dikendalikan oleh PEMBIMBING.
   * Peserta tidak boleh menentukan atau mengubah persentase progress.

---

# 1. JENIS PEKERJAAN

Pekerjaan memiliki dua jenis:

### A. Proyek

Untuk pekerjaan yang mempunyai proses dan target penyelesaian.

Contoh:

* Membuat Aplikasi Presensi
* Membuat Website
* Membuat Video Profil
* Membuat Sistem Informasi

Pekerjaan jenis proyek memiliki progress:

0% → 10% → 30% → 50% → 70% → 100%

Progress ditentukan oleh pembimbing ketika melakukan review aktivitas.

Jika progress sudah 100%, pekerjaan dapat dianggap selesai.

### B. Rutin

Untuk pekerjaan yang dilakukan berulang dan tidak mempunyai progress.

Contoh:

* Melayani tamu
* Melayani peserta
* Mengelola surat
* Membantu administrasi
* Input data rutin

Pekerjaan rutin TIDAK memiliki progress.

Jangan tampilkan progress 0% atau 100% untuk pekerjaan rutin.

---

# 2. SIAPA YANG MEMBUAT PEKERJAAN?

Pekerjaan dibuat oleh PEMBIMBING.

Pembimbing hanya dapat membuat pekerjaan untuk peserta magang yang memang berada di bawah bimbingannya.

Contoh:

Pembimbing A membina:

* Hasnia
* Siti
* Budi

Pembimbing A membuka:

Peserta Bimbingan
→ Hasnia
→ Pekerjaan
→ Tambah Pekerjaan

Lalu membuat:

Judul:
"Membuat Aplikasi Presensi Magang"

Jenis:
"Proyek"

Deskripsi:
"Membuat aplikasi presensi untuk peserta magang."

Tanggal mulai:
29 September 2026

Target selesai:
10 Oktober 2026

Progress awal:
0%

Pekerjaan tersebut hanya dimiliki oleh Hasnia.

Jangan membuat pekerjaan menjadi data global yang otomatis muncul pada semua peserta.

---

# 3. RELASI DATA

Gunakan relasi yang sesuai dengan struktur database aplikasi yang sudah ada.

Konsep relasi yang diinginkan:

Pembimbing
↓
memiliki banyak peserta magang
↓
Peserta Magang
↓
memiliki banyak pekerjaan
↓
Pekerjaan
↓
memiliki banyak aktivitas
↓
Aktivitas Harian

Secara konsep:

Pembimbing
hasMany Magang

Magang
belongsTo Pembimbing
hasMany Pekerjaan

Pekerjaan
belongsTo Magang
belongsTo Pembimbing
hasMany Aktivitas

Aktivitas
belongsTo Magang
belongsTo Pekerjaan

Jangan membuat relasi baru yang bertentangan dengan relasi pembimbing-magang yang sudah ada.

SEBELUM CODING:

* Periksa model yang sudah ada.
* Periksa migration yang sudah ada.
* Periksa foreign key yang sudah digunakan.
* Gunakan struktur existing jika memungkinkan.
* Jangan membuat tabel/model duplikat.

---

# 4. DATABASE

Evaluasi terlebih dahulu tabel `aktivitas` yang sudah ada.

Jika memang diperlukan, buat tabel `pekerjaan`.

Konsep minimal tabel `pekerjaan`:

* id
* magang_id
* pembimbing_id
* judul
* deskripsi
* jenis
* progress
* tanggal_mulai
* target_selesai
* status
* timestamps

Nilai `jenis`:

* proyek
* rutin

Untuk pekerjaan `rutin`, field progress boleh NULL.

Untuk pekerjaan `proyek`, progress dapat dimulai dari 0.

Status pekerjaan dapat menggunakan konsep:

* aktif / berjalan
* selesai
* nonaktif

Jangan menambahkan struktur database yang tidak diperlukan.

Untuk tabel `aktivitas`, tambahkan relasi ke `pekerjaan` jika belum ada.

Konsep field:

* id
* magang_id
* pekerjaan_id
* tanggal
* uraian/deskripsi aktivitas
* status approval
* catatan pembimbing
* timestamps

Sesuaikan nama field dengan struktur existing aplikasi.

JANGAN melakukan `migrate:fresh`.

Buat migration baru yang aman agar data existing tidak hilang.

---

# 5. ALUR PEMBIMBING

Pembimbing login.

Pembimbing melihat daftar peserta yang dibimbing.

Contoh:

## Peserta Bimbingan

Hasnia
Siti
Budi

Ketika memilih Hasnia:

## Detail Hasnia

Pekerjaan
Aktivitas
Progress
Riwayat

Pembimbing dapat:

* Tambah pekerjaan
* Edit pekerjaan
* Melihat aktivitas berdasarkan pekerjaan
* Melihat progress pekerjaan
* Review aktivitas
* Menyetujui aktivitas
* Meminta revisi aktivitas
* Mengubah progress untuk pekerjaan jenis proyek

Pembimbing TIDAK boleh melihat/mengelola pekerjaan peserta yang bukan bawahannya.

---

# 6. ALUR PESERTA MAGANG

Peserta login.

Peserta membuka menu Aktivitas.

Form:

Pekerjaan *
[ dropdown ]

Tanggal
[ otomatis tanggal hari ini / sesuai aturan sistem ]

Aktivitas Hari Ini *
[ textarea ]

Dokumentasi jika fitur existing mengharuskannya.

Peserta memilih pekerjaan dari dropdown.

Dropdown hanya menampilkan:

* pekerjaan yang memang diberikan kepada peserta tersebut
* pekerjaan yang masih aktif
* tidak menampilkan pekerjaan peserta lain

Contoh:

Pekerjaan *
┌──────────────────────────────────┐
│ Membuat Aplikasi Presensi     ▼ │
└──────────────────────────────────┘

Aktivitas:
"Membuat halaman dashboard admin."

Peserta mengirim aktivitas.

PESERTA TIDAK BOLEH:

* mengubah progress
* memasukkan persentase
* membuat pekerjaan sendiri jika aturan sistem menetapkan pekerjaan dibuat pembimbing
* memilih pekerjaan peserta lain

---

# 7. ALUR REVIEW PEMBIMBING

Setelah peserta mengirim aktivitas:

Status aktivitas:
"Menunggu Review"

Aktivitas otomatis masuk ke daftar review pembimbing yang membimbing peserta tersebut.

Contoh dashboard:

## Menunggu Review

Hasnia
Pekerjaan:
Membuat Aplikasi Presensi

30 September 2026
"Membuat fitur validasi lokasi."

[Review]

Siti
Pekerjaan:
Membuat Website

30 September 2026
"Membuat halaman login."

[Review]

Pembimbing tidak perlu mencari aktivitas secara manual.

Sistem harus memfilter berdasarkan relasi peserta yang dibimbing oleh pembimbing tersebut.

---

# 8. REVIEW UNTUK PEKERJAAN PROYEK

Pembimbing membuka aktivitas:

Pekerjaan:
Membuat Aplikasi Presensi

Progress saat ini:
40%

Aktivitas:
"Membuat fitur validasi lokasi."

Status:
Menunggu Review

Pada halaman review, pembimbing dapat:

Progress Pekerjaan Baru:
[ 50 ] %

Catatan:
[ Validasi lokasi sudah sesuai, lanjutkan pengujian. ]

Pilihan:

[ Setujui ]
[ Perlu Revisi ]

Jika disetujui:

* status aktivitas menjadi disetujui
* progress pekerjaan diperbarui sesuai nilai yang dimasukkan pembimbing
* catatan pembimbing disimpan

Contoh:

40% → 50%

Jika perlu revisi:

* status aktivitas menjadi `perlu_revisi`
* progress pekerjaan TIDAK otomatis bertambah
* catatan revisi wajib dapat disimpan
* peserta dapat melihat catatan tersebut

---

# 9. REVIEW UNTUK PEKERJAAN RUTIN

Untuk pekerjaan rutin:

Pekerjaan:
"Melayani Tamu"

Aktivitas:
"Melayani tamu yang datang untuk keperluan administrasi."

Pembimbing melihat:

Status:
Menunggu Review

Tidak boleh ada input progress.

Pembimbing hanya dapat:

[ Setujui ]
[ Perlu Revisi ]

Jika disetujui:

* aktivitas menjadi disetujui
* tidak ada perubahan progress

Jangan menampilkan progress pada pekerjaan rutin.

---

# 10. REVISI

Jika pembimbing memilih "Perlu Revisi":

Contoh catatan:

"Data yang dimasukkan masih belum lengkap, silakan lengkapi."

Peserta dapat melihat:

Status:
Perlu Revisi

Catatan Pembimbing:
"Data yang dimasukkan masih belum lengkap."

Peserta kemudian memperbaiki/mengirim aktivitas sesuai mekanisme existing.

Jangan menghapus riwayat aktivitas lama tanpa alasan.

Riwayat review harus tetap dapat ditelusuri jika struktur existing memungkinkan.

---

# 11. PEKERJAAN SELESAI

Untuk pekerjaan jenis proyek:

Progress 100% berarti pekerjaan selesai.

Contoh:

Membuat Aplikasi Presensi
Progress: 100%
Status: Selesai

Setelah selesai:

* pekerjaan tidak boleh dipilih lagi untuk aktivitas baru, kecuali memang diperlukan oleh aturan sistem
* tampilkan status "Selesai"
* riwayat aktivitas tetap dapat dilihat

Untuk pekerjaan rutin:

* tidak menggunakan progress
* dapat tetap berstatus aktif selama pekerjaan rutin tersebut masih diberikan kepada peserta

---

# 12. UI PESERTA

Pada daftar aktivitas peserta, tampilkan:

Pekerjaan
Aktivitas
Tanggal
Status Approval

Contoh:

Membuat Aplikasi Presensi
30 Sep
Membuat validasi lokasi
🟡 Menunggu Review

Melayani Tamu
30 Sep
Melayani tamu
🟢 Disetujui

Untuk proyek, boleh tampilkan progress pekerjaan:

Membuat Aplikasi Presensi
Progress: 50%

Untuk rutin:

Melayani Tamu
Tidak perlu menampilkan progress.

---

# 13. UI PEMBIMBING

Buat tampilan yang memudahkan pembimbing yang membina banyak peserta.

Contoh:

Dashboard Pembimbing

## Peserta Bimbingan

Hasnia

* 2 pekerjaan
* 1 aktivitas menunggu review

Siti

* 3 pekerjaan
* 2 aktivitas menunggu review

Budi

* 1 pekerjaan
* 0 aktivitas menunggu review

Ketika klik Hasnia:

Detail Peserta

Pekerjaan:

1. Membuat Aplikasi Presensi
   Proyek
   Progress 50%
   3 aktivitas

2. Melayani Tamu
   Rutin
   7 aktivitas

Pembimbing dapat masuk ke masing-masing pekerjaan untuk melihat riwayat aktivitas.

---

# 14. VALIDASI KEAMANAN

Pastikan authorization diterapkan.

Pembimbing hanya boleh:

* membuat pekerjaan untuk peserta yang dibimbingnya
* melihat pekerjaan peserta yang dibimbingnya
* melihat aktivitas peserta yang dibimbingnya
* melakukan review terhadap aktivitas peserta yang dibimbingnya
* mengubah progress pekerjaan milik peserta yang dibimbingnya

Peserta hanya boleh:

* melihat pekerjaan miliknya sendiri
* membuat aktivitas untuk pekerjaan miliknya sendiri
* melihat status review miliknya sendiri

Jangan hanya mengandalkan filter UI.

Authorization juga harus diterapkan di controller/service/policy/query database.

Cegah ID manipulation melalui URL/request.

---

# 15. KOMPATIBILITAS DENGAN SISTEM EXISTING

Ini sangat penting.

Sistem saya sudah memiliki fitur:

* presensi
* aktivitas
* peserta magang
* pembimbing
* admin
* approval aktivitas
* riwayat
* penilaian magang

JANGAN merombak fitur yang sudah berjalan hanya untuk membuat fitur ini.

Sebelum melakukan perubahan:

1. Baca model existing.
2. Baca migration existing.
3. Baca controller aktivitas.
4. Baca route aktivitas.
5. Baca Blade aktivitas peserta.
6. Baca Blade aktivitas pembimbing.
7. Baca authorization/role existing.
8. Identifikasi struktur approval yang sudah ada.

Kemudian integrasikan fitur pekerjaan ke struktur existing.

Jika nama field/model berbeda dengan contoh prompt ini, gunakan nama yang sudah ada dalam project daripada membuat duplikat.

---

# 16. MIGRATION

Jangan menggunakan:

php artisan migrate:fresh

Jangan menghapus database existing.

Buat migration baru.

Setelah migration dibuat, jelaskan:

* file migration yang dibuat
* kolom yang ditambahkan
* foreign key yang digunakan
* alasan perubahan

Pastikan migration aman dijalankan pada database yang sudah memiliki data.

---

# 17. TESTING

Setelah implementasi, lakukan pengecekan minimal:

### Pembimbing

* dapat membuat pekerjaan untuk peserta yang dibimbing
* tidak dapat membuat pekerjaan untuk peserta lain
* dapat melihat daftar pekerjaan peserta
* dapat melihat aktivitas
* dapat approve
* dapat meminta revisi
* dapat mengubah progress proyek
* tidak melihat progress pada pekerjaan rutin

### Peserta

* hanya melihat pekerjaan miliknya
* dapat memilih pekerjaan dari dropdown
* dapat membuat aktivitas
* tidak dapat mengubah progress
* dapat melihat status approval
* dapat melihat catatan revisi

### Multi peserta

Uji minimal:

Pembimbing A:

* Hasnia
* Siti

Pastikan:

* aktivitas Hasnia tidak muncul sebagai aktivitas Siti
* pekerjaan Hasnia tidak muncul pada Siti
* Pembimbing A dapat mengelola keduanya
* pembimbing lain tidak dapat mengelola pekerjaan/aktivitas mereka jika bukan bawahannya.

---

# 18. OUTPUT YANG SAYA INGINKAN DARI AGEN

Sebelum coding, jelaskan terlebih dahulu:

1. Struktur database existing yang ditemukan.
2. Struktur `aktivitas` existing.
3. Relasi `magang` dan `pembimbing` existing.
4. Perubahan database yang diperlukan.
5. Apakah perlu tabel `pekerjaan` baru atau dapat menggunakan struktur existing.
6. File yang akan dibuat/diubah.
7. Alur data setelah perubahan.

Setelah itu baru implementasikan.

Jangan langsung mengubah kode sebelum memahami struktur existing.

Setelah selesai:

* tampilkan daftar file yang diubah
* jelaskan perubahan setiap file
* berikan migration command yang harus dijalankan
* berikan langkah testing
* pastikan tidak ada data existing yang dihapus.

PRINSIP UTAMA:

Pembimbing membuat pekerjaan → pekerjaan ditujukan ke peserta tertentu → peserta memilih pekerjaan saat mengisi aktivitas harian → aktivitas masuk ke pembimbing yang membina peserta tersebut → pembimbing melakukan review → jika proyek, pembimbing menentukan progress → jika rutin, tidak ada progress → jika perlu revisi, peserta mendapatkan catatan revisi.
