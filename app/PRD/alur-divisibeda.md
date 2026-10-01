Saya ingin menambahkan fitur **Riwayat Penempatan Divisi Peserta Magang** pada aplikasi presensi magang yang sedang saya kembangkan.

### 1. Konsep Utama

Jangan mengubah konsep **periode magang utama**.

Contoh:

* Peserta: Andi
* Periode magang: 1 Januari 2027 – 30 Juni 2027
* Durasi: 6 bulan

Selama periode tersebut, peserta dapat berpindah-pindah divisi.

Contoh penempatan:

* Divisi A: 1 Januari – 28 Februari
* Divisi B: 1 Maret – 30 April
* Divisi C: 1 Mei – 30 Juni

Jadi **satu peserta hanya memiliki satu periode magang**, tetapi dapat memiliki **banyak riwayat penempatan divisi** di dalam periode tersebut.

Penempatan divisi berikutnya juga **tidak wajib sudah ditentukan sejak awal**. Admin dapat menambahkannya kemudian ketika sudah ada keputusan penempatan.

---

### 2. Lokasi Fitur

Tambahkan pengelolaan penempatan divisi pada:

**Admin → Data Magang → Detail Peserta → Penempatan Divisi**

Pada halaman detail peserta magang, buat tab/menu:

* Informasi Peserta
* Periode Magang
* Pembimbing
* **Penempatan Divisi**
* Presensi
* Aktivitas
* Penilaian

Fokus implementasi kali ini adalah **Penempatan Divisi**.

---

### 3. Tampilan Penempatan Divisi

Pada halaman Penempatan Divisi, tampilkan informasi:

| Divisi   | Tanggal Mulai | Tanggal Selesai | Status   | Aksi       |
| -------- | ------------- | --------------- | -------- | ---------- |
| Divisi A | 01 Jan 2027   | 28 Feb 2027     | Selesai  | Edit/Hapus |
| Divisi B | 01 Mar 2027   | 30 Apr 2027     | Selesai  | Edit/Hapus |
| Divisi C | 01 Mei 2027   | 30 Jun 2027     | Berjalan | Edit/Hapus |

Status sebaiknya ditentukan otomatis berdasarkan tanggal saat ini:

* **Akan Datang**
* **Berjalan**
* **Selesai**

Jangan meminta admin mengisi status secara manual jika status dapat dihitung dari tanggal.

Tambahkan tombol:

**+ Tambah Penempatan**

---

### 4. Form Tambah Penempatan

Buat form:

**Tambah Penempatan Divisi**

Field:

1. Peserta Magang → otomatis berdasarkan peserta yang sedang dibuka
2. Divisi → dropdown dari data divisi yang sudah tersedia
3. Tanggal Mulai
4. Tanggal Selesai

Validasi:

* Tanggal mulai tidak boleh lebih awal dari tanggal mulai periode magang.
* Tanggal selesai tidak boleh melewati tanggal selesai periode magang.
* Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.
* Jangan mengizinkan dua penempatan untuk peserta yang sama memiliki rentang tanggal yang saling bertabrakan.
* Jika penempatan sebelumnya masih berjalan, admin tetap dapat menambahkan penempatan berikutnya selama tanggalnya tidak bentrok.
* Penempatan berikutnya boleh belum ada. Jangan membuat sistem mewajibkan seluruh periode magang memiliki penempatan sejak awal.

---

### 5. Contoh Skenario

Peserta memiliki periode:

**1 Januari 2027 – 30 Juni 2027**

Awalnya admin hanya mengetahui:

**Divisi A → 1 Januari–28 Februari**

Maka hanya data tersebut yang perlu dimasukkan.

Pada bulan Februari, peserta diputuskan pindah ke Divisi B mulai 1 Maret.

Admin kemudian membuka:

**Data Magang → Andi → Penempatan Divisi → + Tambah Penempatan**

dan memasukkan:

**Divisi B → 1 Maret–30 April**

Kemudian jika pada bulan April ditentukan lagi pindah ke Divisi C:

**Divisi C → 1 Mei–30 Juni**

Sistem harus dapat menangani kondisi tersebut tanpa mengubah periode utama magang.

---

### 6. Relasi dengan Presensi

Penempatan divisi harus berdasarkan **tanggal presensi**, bukan hanya mengambil divisi yang sedang aktif pada profil peserta.

Contoh:

Presensi Andi tanggal 15 Januari:
→ Divisi A

Presensi Andi tanggal 15 Maret:
→ Divisi B

Presensi Andi tanggal 15 Mei:
→ Divisi C

Sistem harus dapat mengetahui divisi peserta berdasarkan tanggal tersebut.

Jika tidak ada penempatan yang cocok dengan tanggal presensi, jangan asal mengambil divisi lain. Tampilkan kondisi sebagai **belum memiliki penempatan** atau null sesuai desain sistem.

---

### 7. Relasi dengan Laporan/Cetak

Fitur ini nantinya akan digunakan untuk laporan presensi dan jurnal/aktivitas.

Ketika sistem mencetak data berdasarkan periode tertentu, sistem harus mengetahui:

**Peserta berada di divisi mana pada tanggal tersebut.**

Contoh:

* Laporan Januari–Februari → Divisi A
* Laporan Maret–April → Divisi B
* Laporan Mei–Juni → Divisi C

Jika laporan membutuhkan tanda tangan kepala divisi, gunakan kepala divisi berdasarkan **penempatan peserta pada periode laporan tersebut**, bukan selalu kepala divisi terakhir.

Jangan menghapus informasi riwayat divisi sebelumnya ketika peserta berpindah divisi.

---

### 8. Database

Periksa struktur database/model yang sudah ada terlebih dahulu sebelum membuat perubahan.

Jangan melakukan `migrate:fresh`.

Jika diperlukan tabel baru, buat migration baru, misalnya konsep:

**penempatan_magang**

dengan data minimal:

* id
* magang_id
* divisi_id
* tanggal_mulai
* tanggal_selesai
* timestamps

Gunakan foreign key yang sesuai dengan struktur database aplikasi yang sudah ada.

Sesuaikan nama model, tabel, primary key, dan relasi dengan struktur project yang sebenarnya. Jangan mengasumsikan nama tabel jika struktur existing berbeda.

Buat relasi:

* Magang → hasMany PenempatanMagang
* PenempatanMagang → belongsTo Magang
* PenempatanMagang → belongsTo Divisi
* Divisi → hasMany PenempatanMagang

Tambahkan juga relasi yang diperlukan untuk mengambil penempatan aktif berdasarkan tanggal.

---

### 9. Logika Penempatan Aktif

Buat mekanisme untuk mendapatkan divisi peserta berdasarkan tanggal tertentu.

Konsep:

`tanggal_mulai <= tanggal`
dan
`tanggal_selesai >= tanggal`

Contoh:

Jika tanggal = 15 Maret 2027:

Cari penempatan:

* mulai ≤ 15 Maret
* selesai ≥ 15 Maret

Maka hasilnya:
**Divisi B**

Gunakan mekanisme ini secara konsisten pada bagian sistem yang membutuhkan divisi peserta berdasarkan tanggal.

---

### 10. Jangan Merusak Fitur Existing

Sebelum melakukan perubahan:

1. Periksa struktur model Magang.
2. Periksa model Divisi.
3. Periksa migration terkait magang/divisi.
4. Periksa controller admin untuk pengelolaan peserta.
5. Periksa Blade detail peserta.
6. Periksa relasi yang sudah ada.
7. Periksa bagaimana divisi saat ini digunakan pada presensi dan laporan.

Jangan mengganti struktur existing secara besar-besaran jika tidak diperlukan.

Implementasikan fitur baru dengan menyesuaikan arsitektur aplikasi yang sudah ada.

---

### 11. UI/UX

Gunakan desain UI yang konsisten dengan aplikasi yang sudah ada.

Pada halaman Penempatan Divisi, tampilkan riwayat dalam bentuk tabel/card yang mudah dipahami.

Berikan informasi visual mengenai:

* Divisi
* Periode penempatan
* Status penempatan
* Aksi edit
* Aksi hapus

Tambahkan konfirmasi sebelum menghapus penempatan.

Jika peserta belum memiliki penempatan:

**"Belum ada penempatan divisi untuk peserta ini."**

Jika penempatan saat ini sedang berjalan, berikan penanda **Sedang Berjalan**.

---

### 12. Hal Penting

Jangan membuat fitur ini sebagai pengganti periode magang.

Struktur konsep yang diinginkan adalah:

**1 Peserta**
→ **1 Periode Magang**
→ **Banyak Penempatan Divisi**

Contoh:

Andi
→ Periode Magang: 1 Januari–30 Juni
→ Divisi A: Januari–Februari
→ Divisi B: Maret–April
→ Divisi C: Mei–Juni

Tujuan utama fitur ini adalah agar sistem dapat menyimpan **riwayat perpindahan divisi peserta secara terstruktur**, menentukan divisi berdasarkan tanggal, dan nantinya menggunakan informasi tersebut untuk presensi, aktivitas, jurnal, serta laporan/cetak.

Sebelum menulis kode, analisis terlebih dahulu struktur project yang sudah ada dan jelaskan file apa saja yang akan dibuat/diubah. Setelah itu implementasikan secara lengkap tanpa menghapus fitur existing.
