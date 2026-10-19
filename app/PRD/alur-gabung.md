# TUGAS: INTEGRASI APLIKASI PENDAFTARAN MAGANG DENGAN APLIKASI PRESENSI MAGANG

## 1. Konteks Proyek

Saya memiliki dua aplikasi Laravel yang terpisah:

1. **Aplikasi Pendaftaran Magang** — dibuat oleh teman saya, digunakan untuk pendaftaran peserta magang dan proses penerimaan.
2. **Aplikasi Presensi Magang** — aplikasi yang sedang saya kembangkan, digunakan untuk mengelola akun peserta, periode magang, pembimbing, divisi, presensi, aktivitas, pengajuan, rekapitulasi, dan penilaian.

Saya ingin mengintegrasikan kedua aplikasi agar peserta yang sudah dinyatakan diterima pada aplikasi pendaftaran dapat otomatis masuk ke aplikasi presensi tanpa perlu melakukan pendaftaran ulang.

Sebelum melakukan perubahan, pelajari struktur proyek, model, migration, controller, middleware, autentikasi, dan fitur yang sudah tersedia. Jangan langsung menulis kode sebelum memahami arsitektur yang ada.

## 2. Tujuan Integrasi

Ketika status pengajuan peserta pada aplikasi pendaftaran berubah menjadi `diterima`, data peserta tersebut harus dapat disinkronkan ke aplikasi presensi.

Data yang masuk otomatis hanya berupa akun dan profil dasar peserta. Data penempatan magang tidak langsung ditentukan karena akan dikelola oleh admin pada aplikasi presensi.

Alur yang diinginkan:

1. Peserta mendaftar melalui aplikasi pendaftaran.
2. Pengajuan peserta diproses hingga berstatus `diterima`.
3. Data peserta yang diterima disinkronkan ke aplikasi presensi.
4. Akun dan profil peserta dibuat secara otomatis dengan status proses administrasi `BARU`.
5. Admin membuka daftar peserta baru pada aplikasi presensi.
6. Admin melengkapi tanggal mulai, tanggal selesai, pembimbing, dan divisi.
7. Setelah seluruh data wajib valid, admin dapat mengaktifkan peserta untuk menggunakan fitur presensi.

Peserta dengan status `BARU` belum boleh melakukan presensi atau mengakses fitur peserta aktif.

## 3. Struktur Database Aplikasi Pendaftaran

Gunakan struktur aktual aplikasi pendaftaran berikut sebagai acuan awal. Verifikasi kembali melalui kode sumber dan migration aplikasi tersebut.

### Tabel `users`
- `id`
- `email`
- `password`
- `role`

Peserta menggunakan role `peserta`.

### Tabel `pesertas`
- `id`
- `user_id`
- `nomor_identitas`
- `nim_nisn`
- `nama_lengkap`
- `foto`
- `no_hp`

Relasi:
`pesertas.user_id` mengarah ke `users.id`.

### Tabel `pengajuan_magangs`
- `id`
- `peserta_id`
- `status`

Relasi:
`pengajuan_magangs.peserta_id` mengarah ke `pesertas.id`.

Status pengajuan yang tersedia:
- `menunggu`
- `diverifikasi`
- `perlu_perbaikan`
- `diterima`
- `ditolak`

Hanya sinkronkan peserta dengan role `peserta` yang memiliki pengajuan berstatus `diterima`.

Periksa kemungkinan seorang peserta memiliki lebih dari satu pengajuan. Pastikan peserta yang sama tidak menghasilkan beberapa akun di aplikasi presensi.

## 4. Struktur Database Aplikasi Presensi Saat Ini

### Model `Pengguna`

Model menggunakan tabel `pengguna` dengan struktur utama:

- `id`
- `username` — unik
- `password`
- `role` — `admin`, `pembimbing`, atau `magang`
- `is_active` — boolean, default `true`
- `created_at`
- `updated_at`

Model menggunakan autentikasi Laravel dan cast:

`'password' => 'hashed'`

Perhatikan mekanisme hashing password tersebut agar password yang sudah berupa hash tidak di-hash ulang secara tidak sengaja.

Relasi yang sudah tersedia meliputi `pembimbing()`, `magang()`, `presensi()`, `aktivitas()`, `pengajuanIzin()`, dan `pengajuanTugasLuar()`. Pertahankan relasi yang sudah ada.

### Model `Magang`

Tabel `magang` memiliki kolom utama:

- `id`
- `pengguna_id` — unik dan foreign key ke `pengguna.id`
- `pembimbing_id` — nullable
- `divisi_id` — nullable
- `no_induk` — nullable
- `nama_lengkap` — wajib
- `jurusan` — nullable
- `instansi_pendidikan` — nullable
- `no_hp` — nullable
- `foto` — nullable
- `tanggal_mulai` — saat ini wajib
- `tanggal_selesai` — saat ini wajib
- `status` — enum `aktif`, `selesai`, `cuti`, default `aktif`
- `created_at`
- `updated_at`

Model `Magang` juga memiliki relasi dan fitur lain, termasuk pemeriksaan apakah akun dapat login dan apakah periode magang berakhir. Pelajari implementasinya sebelum mengubah logika.

## 5. Penyesuaian Status dan Database

Saat ini tabel `magang` belum mendukung peserta yang belum memiliki periode magang karena `tanggal_mulai` dan `tanggal_selesai` wajib diisi.

Lakukan penyesuaian agar peserta dapat tersimpan sebagai data baru tanpa tanggal periode.

Ketentuan:

1. Ubah `tanggal_mulai` dan `tanggal_selesai` agar nullable melalui migration baru yang aman.
2. Tambahkan status proses administrasi `baru` untuk peserta yang baru disinkronkan.
3. Pertahankan status `aktif`, `selesai`, dan `cuti` yang sudah digunakan oleh sistem.
4. Periksa seluruh kode yang membaca atau mengubah `magang.status`, terutama pemeriksaan periode, dashboard, autentikasi, presensi, rekap, dan penilaian.
5. Jangan menganggap peserta berstatus `baru` sebagai peserta aktif.
6. Jangan mengisi tanggal palsu atau menentukan periode secara otomatis.
7. Jangan menghapus data lama atau menggunakan `migrate:fresh`.
8. Buat migration tambahan yang aman dan pertimbangkan data lama yang sudah tersimpan.

Jika perubahan enum langsung tidak aman atau database memiliki keterbatasan, gunakan pendekatan migration yang kompatibel dengan database proyek dan jelaskan implementasinya.

Status `BARU` adalah status proses administrasi, bukan status kehadiran.

## 6. Data yang Disinkronkan

Lakukan pemetaan berikut setelah memverifikasi tipe dan format data dari aplikasi sumber:

| Aplikasi Pendaftaran | Aplikasi Presensi |
|---|---|
| `pesertas.nama_lengkap` | `magang.nama_lengkap` |
| `pesertas.nim_nisn` atau `pesertas.nomor_identitas` | `magang.no_induk` |
| `pesertas.no_hp` | `magang.no_hp` |
| `pesertas.foto` | `magang.foto`, jika file foto dapat diakses dengan benar |
| `users.email` | `pengguna.username`, jika sesuai dengan mekanisme login aplikasi presensi |
| ID peserta atau akun dari aplikasi sumber | Kolom referensi unik pada aplikasi presensi |

Untuk `no_induk`, tentukan prioritas antara `nim_nisn` dan `nomor_identitas` berdasarkan kebutuhan aplikasi serta data aktual. Hindari menimpa data yang valid dengan nilai kosong.

Kolom berikut tidak boleh ditentukan secara otomatis dari integrasi ini:
- `pembimbing_id`
- `divisi_id`
- `tanggal_mulai`
- `tanggal_selesai`

Kolom tersebut diisi admin pada aplikasi presensi.

Pertahankan kolom dan fitur lain yang sudah ada pada model `Magang`, termasuk data foto wajah atau face recognition. Jangan menghapus atau mereset data tersebut ketika melakukan sinkronisasi ulang.

## 7. Referensi Unik untuk Mencegah Duplikasi

Tambahkan referensi ID dari aplikasi pendaftaran ke aplikasi presensi, misalnya `id_user_pendaftaran` atau nama lain yang paling sesuai dengan desain database.

Tentukan lokasi kolom yang tepat setelah memeriksa relasi dan migration yang ada. Kolom tersebut harus memiliki constraint unik yang sesuai.

Ketentuan:
- Sinkronisasi yang dijalankan berulang kali tidak boleh membuat akun duplikat.
- Gunakan ID stabil dari aplikasi sumber, bukan nama atau nomor telepon sebagai identitas utama.
- Jika peserta sudah ada, perbarui hanya data dasar yang memang dikelola oleh aplikasi pendaftaran.
- Jangan menimpa periode, pembimbing, divisi, status penempatan, password lokal, atau data presensi yang sudah dikelola aplikasi presensi.
- Jika ditemukan konflik username atau referensi peserta, tangani dengan aman dan tampilkan pesan yang dapat dipahami admin.

## 8. Mekanisme Integrasi

Gunakan API yang aman antara dua aplikasi Laravel, dengan database masing-masing tetap terpisah.

Pada aplikasi pendaftaran:
- Buat endpoint API untuk mengambil peserta yang pengajuannya sudah diterima.
- Kembalikan hanya data yang diperlukan.
- Gunakan autentikasi token atau mekanisme antaraplikasi yang aman.
- Validasi role dan status pengajuan.
- Jangan mengekspos password, hash password, atau data sensitif yang tidak diperlukan.
- Pastikan relasi dan format respons API sesuai dengan struktur database aktual.

Pada aplikasi presensi:
- Buat service khusus untuk berkomunikasi dengan API pendaftaran.
- Simpan URL API dan token di `.env`.
- Jangan menulis token langsung di source code atau memasukkannya ke Git.
- Tambahkan timeout, penanganan kegagalan koneksi, validasi respons, dan pencatatan error yang tidak membocorkan rahasia.
- Gunakan transaksi database saat membuat atau memperbarui data lokal yang saling berelasi.
- Pisahkan logika sinkronisasi ke service agar controller tetap rapi dan mudah dirawat.

Jangan menghubungkan kedua aplikasi dengan asumsi bahwa keduanya menggunakan database yang sama.

## 9. Password dan Aktivasi Akun

Jangan langsung menyalin hash password dari aplikasi pendaftaran tanpa memastikan kompatibilitas autentikasi kedua aplikasi.

Untuk tahap ini:
- Akun peserta dibuat secara otomatis.
- Password tidak perlu diambil dari aplikasi pendaftaran.
- Jangan memberikan password default yang sama kepada seluruh peserta.
- Jangan membuat akun baru otomatis dapat login sebelum proses administrasi selesai.
- Rancang aktivasi akun setelah admin melengkapi data wajib.
- Jika password lokal perlu ditetapkan, gunakan mekanisme aman seperti pengaturan password atau reset password, sesuai kemampuan aplikasi yang ada.

Periksa hubungan antara `pengguna.is_active`, status `magang`, dan metode `dapatLogin()` sebelum menentukan aturan final aktivasi.

Pastikan status `BARU` tetap mencegah akses ke presensi meskipun ada jalur autentikasi lain.

## 10. Halaman Admin untuk Peserta Baru

Tambahkan tampilan pada halaman admin untuk menampilkan peserta yang baru disinkronkan.

Halaman setidaknya memiliki:
- Nama peserta.
- Nomor identitas atau NIM/NISN.
- Instansi pendidikan.
- Nomor HP.
- Tanggal data diterima atau disinkronkan, jika tersedia.
- Status `BARU`.
- Informasi bahwa periode dan pembimbing belum ditentukan.
- Tombol `Proses Peserta`.

Saat tombol `Proses Peserta` dipilih, tampilkan formulir untuk:
- Tanggal mulai magang.
- Tanggal selesai magang.
- Pembimbing.
- Divisi.

Validasi:
- Semua data wajib harus diisi sebelum peserta dapat diaktifkan.
- Tanggal selesai tidak boleh mendahului tanggal mulai.
- Pembimbing dan divisi harus berasal dari data yang valid.
- Setelah disimpan, status peserta berubah menjadi `aktif` dan akun dapat diaktifkan sesuai kebijakan autentikasi yang berlaku.
- Peserta yang belum lengkap tetap berstatus `baru`.

Gunakan desain, komponen, navigasi, dan gaya UI yang konsisten dengan aplikasi presensi saat ini. Jangan merombak keseluruhan tampilan.

## 11. Pengamanan Fitur Presensi

Audit semua jalur akses agar peserta dengan status `baru`:
- Tidak dapat melakukan check-in atau check-out.
- Tidak dapat mengakses dashboard peserta aktif.
- Tidak dapat mengisi aktivitas atau fitur lain yang membutuhkan periode magang.
- Tidak dapat melewati pemeriksaan hanya dengan membuka URL secara langsung.

Gunakan middleware, policy, atau pemeriksaan otorisasi yang sesuai dengan arsitektur proyek.

Jangan hanya menyembunyikan tombol di frontend. Validasi harus tetap dilakukan di backend.

Pastikan fitur lama tetap berjalan untuk peserta aktif yang sudah memiliki periode magang.

## 12. Kriteria Keberhasilan

Implementasi dianggap berhasil apabila:

1. Peserta yang diterima di aplikasi pendaftaran dapat disinkronkan tanpa mendaftar ulang.
2. Hanya peserta yang memenuhi kriteria penerimaan yang diambil.
3. Akun dan profil peserta dibuat dengan status `baru`.
4. Tanggal mulai, tanggal selesai, pembimbing, dan divisi belum harus diisi pada saat sinkronisasi.
5. Admin dapat melengkapi data melalui fitur proses peserta.
6. Peserta belum dapat melakukan presensi sebelum data lengkap dan akun diaktifkan.
7. Sinkronisasi berulang tidak membuat akun ganda.
8. Data penempatan dan riwayat presensi yang sudah ada tidak tertimpa.
9. Fitur lama, termasuk presensi, aktivitas, pengajuan, rekap, penilaian, dan face recognition, tetap berjalan.
10. API terlindungi dan tidak membocorkan kredensial.
11. Migration dapat dijalankan tanpa menghapus data lama.
12. Tersedia penanganan error dan hasil sinkronisasi yang jelas.

## 13. Cara Pengerjaan

Kerjakan secara bertahap:

1. Audit struktur proyek dan seluruh kode yang terdampak.
2. Laporkan risiko perubahan dan file yang perlu diubah.
3. Buat migration tambahan yang aman.
4. Implementasikan API di aplikasi pendaftaran.
5. Implementasikan service dan proses sinkronisasi di aplikasi presensi.
6. Implementasikan referensi unik dan pencegahan duplikasi.
7. Implementasikan status `BARU`, halaman admin, dan proses penempatan peserta.
8. Amankan seluruh akses peserta baru.
9. Jalankan pemeriksaan syntax, migration, dan test yang tersedia.
10. Uji kasus peserta baru, peserta duplikat, koneksi API gagal, konflik data, dan peserta lama.
11. Berikan ringkasan file yang dibuat atau diubah, perintah yang perlu dijalankan, konfigurasi `.env` yang harus diisi, dan langkah pengujian manual.

Jangan menghapus atau menulis ulang fitur yang tidak berkaitan dengan integrasi ini. Jangan mengarang nama kolom atau relasi yang belum diverifikasi. Jika terdapat ketidakcocokan struktur database, periksa kode aktual dan pilih solusi yang paling aman sebelum melanjutkan.

**Prioritas utama: integrasi harus aman, peserta tidak perlu mendaftar ulang, admin tetap memegang kendali atas penempatan magang, dan fitur presensi yang sudah berjalan tidak boleh rusak.**

SAMA NOTES SATU HAL INI TAMBAH MAGANG PADA FITUR DATA PESERTA ADMIN ITU TETAP ADA