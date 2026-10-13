### Perubahan Desain Ringkasan Rekapitulasi Presensi Magang

Tolong modifikasi halaman Rekapitulasi & Cetak Dokumen peserta magang berdasarkan desain Blade yang sudah ada.

**Pertahankan desain utama yang sekarang.** Jangan merombak keseluruhan halaman, header, warna, typography, efek glass-card, border, radius, shadow, navigasi, maupun menu riwayat yang sudah ada. Fokus hanya pada bagian ringkasan metrik periode magang.

### 1. Hapus Informasi Aktivitas

Hapus kartu atau kolom Aktivitas pada ringkasan metrik yang sekarang. Informasi total aktivitas dan status pengisian aktivitas tidak perlu lagi ditampilkan pada bagian ringkasan tersebut.

Fitur Riwayat Aktivitas pada menu di bawahnya harus tetap ada dan berfungsi seperti sebelumnya.

### 2. Tambahkan Ringkasan Progres Periode

Buat bagian ringkasan di bagian atas dengan informasi:
- Tanggal hari ini.
- Persentase progres periode magang.
- Jumlah hari kerja yang sudah berlalu dibandingkan total hari kerja selama periode magang.
- Progress bar yang menggambarkan persentase progres periode.

Contoh:
- Periode magang: 14 September–14 November 2026.
- Hari ini: 13 Oktober 2026.
- Hari kerja yang sudah berlalu: 22 dari 45 hari kerja.
- Progres periode: sekitar 48–49%, mengikuti metode pembulatan yang konsisten.

Hitung hari kerja berdasarkan Senin–Jumat dan periode magang yang sebenarnya. Sabtu dan Minggu tidak dihitung sebagai hari kerja. Sesuaikan juga dengan tanggal mulai dan selesai peserta yang tersimpan di database.

### 3. Tambahkan Ringkasan Kehadiran dan Jam Magang

Di bagian bawah ringkasan progres periode, buat bagian khusus Kehadiran yang berisi dua metrik:

**A. Kehadiran berdasarkan hari**
- Jumlah hari hadir aktual.
- Target total hari kerja selama periode magang.
- Persentase kehadiran berdasarkan jumlah hari hadir dibandingkan target hari kerja.

Contoh: 20 dari 45 hari kerja, sekitar 44%.

**B. Jam Magang**
- Jumlah jam magang aktual yang telah diperoleh.
- Target total jam magang selama periode.
- Persentase pencapaian jam magang.
- Progress bar untuk memperlihatkan pencapaian jam kerja.

Contoh: 158 dari 360 jam, sekitar 44% jika dibulatkan ke bilangan terdekat, atau 43% jika menggunakan pembulatan ke bawah.

Target jam magang dihitung dari target hari kerja dikalikan 8 jam, kecuali sistem memiliki ketentuan jam kerja resmi yang berbeda.

### 4. Aturan Perhitungan

Pisahkan tiga jenis progres berikut agar tidak tertukar:

1. **Progres periode:** hari kerja yang sudah berlalu dibandingkan total hari kerja dalam periode magang.
2. **Progres kehadiran:** jumlah hari hadir yang valid dibandingkan total target hari kerja.
3. **Progres jam magang:** durasi kerja aktual yang valid dibandingkan total target jam magang.

Jangan menghitung progres periode menggunakan jumlah hari hadir. Jangan menghitung jam magang hanya dengan mengalikan jumlah kehadiran dengan 8 jam jika sistem sudah memiliki jam masuk dan jam pulang aktual.

Gunakan sumber data dan service penilaian yang sudah tersedia apabila relevan. Pastikan status izin, sakit, Alfa, cuti, dan Tugas Luar mengikuti aturan perhitungan yang berlaku pada sistem.

### 5. Tampilan Responsif

Pertahankan gaya desain saat ini:
- Glass-card dan rounded-3xl.
- Warna brand yang sudah digunakan.
- Angka persentase yang menonjol.
- Progress bar yang sederhana.
- Layout yang nyaman dilihat di HP maupun laptop.

Pada layar HP, susun informasi secara vertikal atau dua kolom kecil yang tidak menyebabkan teks terpotong. Pada layar laptop, manfaatkan ruang yang tersedia tanpa mengubah karakter desain utama.

### 6. Ketentuan Implementasi

Periksa Blade yang ada, controller yang menyediakan `$stats`, serta service atau query yang menghitung statistik periode magang.

Jangan hanya mengganti tampilan angka yang sudah ada. Pastikan nilai progres periode, jumlah hari hadir, jam aktual, target hari kerja, dan target jam kerja benar-benar berasal dari perhitungan sistem.

Jika struktur `$stats` perlu diubah, sesuaikan controller atau service terkait dan pertahankan kompatibilitas dengan halaman lain yang menggunakannya. Jangan menghapus fitur Riwayat Presensi, Riwayat Aktivitas, Riwayat Permohonan Ketidakhadiran, dan Lembar Nilai Magang.

Setelah selesai, jelaskan file yang diubah dan berikan kode lengkap setiap file yang dimodifikasi.