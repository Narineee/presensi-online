Gunakan **API Indonesia** sebagai sumber API resmi untuk sinkronisasi hari libur.

Dokumentasi resmi:
`https://docs.apiindonesia.id/docs/#hari-libur`

Base URL:
`https://use.apiindonesia.id`

Endpoint daftar hari libur:
`GET /api/v1/libur`

Sehingga endpoint lengkap:
`https://use.apiindonesia.id/api/v1/libur?tahun={tahun}`

### Autentikasi

Gunakan API key melalui HTTP header:

`x-api-key: {API_KEY}`

Simpan API key hanya di `.env`, jangan hardcode di source code dan jangan expose ke frontend.

Contoh:

`HARI_LIBUR_API_ENABLED=true`
`HARI_LIBUR_API_URL=https://use.apiindonesia.id/api/v1/libur`
`HARI_LIBUR_API_KEY=`

API key akan diisi sendiri oleh pengguna.

### Parameter

Gunakan parameter:

`tahun`

Contoh:

`?tahun=2026`

Sistem harus dapat melakukan sinkronisasi berdasarkan tahun yang dipilih admin.

Contoh:

* Sinkronisasi tahun 2026 → mengambil data 2026.
* Sinkronisasi tahun 2027 → mengambil data 2027.
* Tahun berikutnya dapat digunakan tanpa perlu mengubah kode.

### Mapping response API

Response API memiliki struktur data:

* `id`
* `date`
* `name`
* `type`
* `is_joint_leave`
* `description`
* `source`
* `year`
* `is_active`

Petakan ke tabel `hari_libur` sebagai berikut:

* `id` API → `external_id`
* `date` → `tanggal`
* `name` → `nama`
* `description` → `keterangan`
* `sumber` → `api`

Untuk `jenis`:

* jika `is_joint_leave = 1`, simpan sebagai `Cuti Bersama`
* jika `is_joint_leave = 0`, simpan sebagai `Hari Libur Nasional`

Tetap perhatikan kemungkinan nilai response yang berbeda dan lakukan validasi sebelum menyimpan.

### Sinkronisasi

Buat service khusus, misalnya:

`app/Services/HariLiburService.php`

Service bertanggung jawab untuk:

1. Mengirim request ke API Indonesia.
2. Mengirim API key melalui header `x-api-key`.
3. Mengirim parameter tahun.
4. Memvalidasi HTTP response.
5. Mengambil data dari field `data`.
6. Memetakan response API ke struktur database.
7. Menyimpan atau memperbarui data.
8. Menandai data sebagai `sumber = api`.
9. Menggunakan `external_id` sebagai salah satu identitas data API.
10. Mencegah duplicate berdasarkan tanggal.
11. Tidak menghapus data yang dibuat secara manual.
12. Mencatat error API ke log Laravel.
13. Menangani timeout, HTTP 4xx, HTTP 5xx, API key tidak valid, dan response yang tidak sesuai.

### Aturan penting data manual

Data dengan:

`sumber = manual`

tidak boleh dihapus atau ditimpa oleh proses sinkronisasi API.

Jika terjadi konflik tanggal antara data API dan data manual, jangan langsung menghapus data manual.

Gunakan mekanisme yang aman untuk menentukan data yang dipertahankan dan tampilkan konflik tersebut jika diperlukan pada UI admin.

### Admin UI

Tambahkan menu:

**Kelola Hari Libur**

Pada halaman tersebut sediakan:

* daftar hari libur
* filter tahun
* filter jenis
* indikator sumber data
* tombol Tambah Manual
* tombol Edit
* tombol Hapus
* tombol **Sinkronisasi API**
* pilihan tahun untuk sinkronisasi

Contoh:

**Sinkronisasi Hari Libur**

* Tahun: `[2026 ▼]`
* Tombol: `[Sinkronkan dari API]`

Setelah sinkronisasi berhasil, tampilkan informasi jumlah data:

* berhasil ditambahkan
* diperbarui
* dilewati
* konflik dengan data manual
* gagal

### Pergantian tahun

Jangan hardcode tahun 2026.

Admin harus dapat memilih tahun yang ingin disinkronkan.

Contoh:

2026 → API mengambil `?tahun=2026`

2027 → API mengambil `?tahun=2027`

Dengan demikian ketika tahun berganti, admin cukup melakukan sinkronisasi tahun baru.

### Fallback

Tetap pertahankan mekanisme `getDaftarLiburNasionalBawaan()` yang sudah ada sebagai fallback apabila memang masih dibutuhkan oleh sistem.

Namun jangan mencampurkan data fallback dengan data API secara tidak jelas.

Gunakan sumber:

* `api`
* `manual`

dan pastikan mekanisme fallback tidak menyebabkan duplicate tanggal.

### Migration

Jangan menggunakan:

`php artisan migrate:fresh`

dan jangan menggunakan:

`php artisan migrate:refresh`

Karena project sudah memiliki data existing.

Buat migration ALTER baru untuk menambahkan:

* `nama`
* `jenis`
* `sumber`
* `external_id`

pada tabel `hari_libur`.

Kemudian jalankan:

`php artisan migrate`

### Testing

Tambahkan test untuk:

1. Request API berhasil.
2. API key dikirim melalui header.
3. Parameter tahun dikirim dengan benar.
4. Response API berhasil dipetakan.
5. `is_joint_leave = 0` menjadi Hari Libur Nasional.
6. `is_joint_leave = 1` menjadi Cuti Bersama.
7. Data API tidak duplicate.
8. Data manual tidak dihapus oleh sinkronisasi.
9. API error ditangani dengan benar.
10. `isTanggalMerah()` tetap berfungsi.
11. PresensiScoreServiceTest tetap lulus.
12. Hari libur/cuti bersama tidak dihitung sebagai kewajiban presensi sesuai aturan sistem.

### Setelah implementasi

Tampilkan:

1. File yang dibuat.
2. File yang diubah.
3. Migration yang dibuat.
4. Struktur tabel terbaru.
5. Service API.
6. Controller.
7. Route.
8. Perubahan sidebar.
9. Perubahan Blade.
10. Konfigurasi `.env`.
11. Cara mendapatkan dan memasukkan API key.
12. Cara menjalankan migration.
13. Cara melakukan sinkronisasi.
14. Hasil testing.

Jangan membuat endpoint API lain dan jangan mengganti API Indonesia dengan API lain tanpa instruksi.

Gunakan dokumentasi API Indonesia sebagai referensi kontrak API:
`https://docs.apiindonesia.id/docs/#hari-libur`
