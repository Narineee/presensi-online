### Tambahan UI/UX Form Pengajuan Tugas Luar (TL)

Atur urutan interaksi pemilihan tanggal agar peserta memahami bahwa tanggal selesai harus ditentukan terlebih dahulu.

**1. Kondisi awal form**
- Kolom **Tanggal Selesai** aktif dan dapat diklik.
- Kolom **Tanggal Mulai** dalam kondisi nonaktif (disabled), tampak buram atau berwarna abu-abu, dan tidak dapat diklik.
- Tambahkan petunjuk singkat di bawah kolom tanggal mulai: "Pilih tanggal selesai terlebih dahulu untuk mengaktifkan tanggal mulai."
- Jangan menampilkan kolom tanggal mulai seolah-olah sudah siap digunakan.

**2. Setelah tanggal selesai dipilih**
- Aktifkan kolom Tanggal Mulai secara otomatis.
- Ubah tampilannya menjadi normal agar terlihat bahwa kolom sudah dapat digunakan.
- Tampilkan kalender tanggal mulai yang memungkinkan peserta memilih tanggal pelaksanaan tugas yang sebenarnya, termasuk tanggal lampau yang lebih lama dari batas 3 hari kerja.
- Tanggal mulai tidak boleh melewati tanggal selesai yang telah dipilih.
- Jika tanggal selesai diubah, periksa kembali tanggal mulai yang sudah dipilih. Jika tanggal mulai menjadi lebih besar daripada tanggal selesai, kosongkan tanggal mulai dan minta peserta memilih ulang.

**3. Validasi dan batas waktu**
- Terapkan batas maksimal pengajuan 3 hari kerja pada kolom Tanggal Selesai, bukan Tanggal Mulai.
- Nonaktifkan tanggal selesai yang sudah melewati batas 3 hari kerja, dengan memperhitungkan akhir pekan dan hari libur yang berlaku dalam sistem.
- Pastikan aturan yang sama diterapkan pada validasi backend, bukan hanya pada tampilan kalender.

**4. Tampilan responsif**
- Pertahankan desain yang konsisten dengan tampilan SEIRAMA.
- Pada kondisi nonaktif, gunakan warna latar abu-abu muda, teks redup, dan kursor atau interaksi yang menunjukkan bahwa kolom belum tersedia.
- Setelah aktif, gunakan tampilan normal seperti input tanggal lainnya.
- Pastikan interaksi berjalan baik di HP maupun laptop.

Jangan mengubah alur pengajuan TL, mekanisme persetujuan, atau fitur presensi lainnya di luar kebutuhan ini. Implementasikan pada form pengajuan TL yang sudah ada, bukan membuat form baru.