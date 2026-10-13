Saya ingin melakukan perubahan pada sistem presensi magang Laravel yang sudah ada. Sebelum mengubah kode, pelajari terlebih dahulu struktur project, database, model, controller, route, middleware, Blade, dan relasi antartabel yang digunakan.

Jangan membuat ulang sistem dari awal. Lakukan penyesuaian terhadap implementasi yang sudah tersedia dengan tetap mempertahankan fitur yang tidak berkaitan dengan perubahan ini.

1. Perubahan konsep utama

Sebelumnya, sistem memiliki fitur Pekerjaan yang dikelola pembimbing, kemudian peserta magang memilih pekerjaan tersebut saat mengisi Aktivitas.

Sekarang, konsep tersebut diubah menjadi:

Rencana Kerja: dibuat dan dikelola oleh peserta magang sendiri.

Aktivitas: berisi laporan progres dari rencana kerja yang benar-benar dikerjakan peserta pada tanggal tertentu.

Pembimbing: hanya melihat daftar rencana kerja peserta sebagai informasi, sedangkan validasi tetap dilakukan pada Aktivitas.

Hapus fitur Pekerjaan lama sebagai fitur terpisah, termasuk input pekerjaan yang diberikan pembimbing, jika memang sudah tidak digunakan. Jangan sampai ada dua fitur dengan fungsi yang sama.

2. Fitur Rencana Kerja peserta magang

Buat atau sesuaikan menu Rencana Kerja agar peserta dapat:

Menambahkan rencana kerja secara mandiri.

Melihat daftar rencana kerja yang pernah dibuat.

Mengedit rencana kerja sesuai ketentuan sistem.

Memilih rencana kerja yang relevan ketika mengisi aktivitas.

Mempertahankan rencana kerja yang masih digunakan pada hari-hari berikutnya.

Contoh rencana kerja:

Melayani pegawai.

Membantu administrasi.

Membuat desain UI.

Membantu dokumentasi kegiatan.

Rencana kerja tidak harus dibuat ulang setiap hari. Satu rencana kerja boleh memiliki banyak catatan aktivitas pada tanggal yang berbeda.

Jika peserta belum mendapatkan briefing dari pembimbing, peserta boleh membuat rencana sementara dan memperbaruinya setelah memperoleh arahan.

Jika peserta mendapatkan tugas baru yang belum tercantum dalam daftar rencana kerja, peserta dapat menambahkan rencana kerja baru terlebih dahulu.

Penting: rencana kerja bukan syarat untuk melakukan presensi masuk atau pulang. Peserta harus tetap bisa melakukan presensi sesuai aturan yang sudah berlaku meskipun belum memiliki rencana kerja.

3. Perubahan formulir Aktivitas peserta magang

Ubah formulir Aktivitas dengan ketentuan berikut.

Input yang dipertahankan

Tanggal aktivitas.

Progres pekerjaan untuk setiap rencana kerja yang dipilih.

Status validasi dan catatan revisi dari pembimbing sesuai mekanisme yang sudah ada.

Input yang dihapus

Waktu aktivitas sebagai kolom input tersendiri.

Pekerjaan yang diberikan pembimbing.

Ringkasan aktivitas umum yang sebelumnya berdiri sendiri.

Input yang menggantikan pekerjaan lama

Gunakan pilihan Rencana Kerja yang mengambil data dari rencana kerja milik peserta yang sedang login.

Ketika peserta memilih rencana kerja, tampilkan kolom Progres khusus untuk rencana kerja tersebut.

Contoh tampilan formulir:

Tanggal: 13 Oktober 2026

Rencana kerja 1: Melayani pegawai
Progres: Membantu pelayanan administrasi kepada lima pegawai.

Rencana kerja 2: Membuat desain UI
Progres: Menyelesaikan rancangan halaman dashboard sebesar 60%.

Peserta dapat menambahkan beberapa rencana kerja dalam satu pengisian aktivitas. Setiap rencana kerja yang dipilih harus memiliki input progres masing-masing.

Jangan menyediakan satu kolom ringkasan aktivitas umum yang menggabungkan semua pekerjaan.

Pastikan sistem mencegah pemilihan rencana kerja yang bukan milik peserta yang sedang login.

4. Aturan pengisian aktivitas

Terapkan aturan berikut:

Peserta hanya melaporkan rencana kerja yang benar-benar dikerjakan pada tanggal aktivitas tersebut.

Jika rencana kerja sudah tersedia dan relevan, peserta cukup memilihnya tanpa membuat rencana baru.

Jika mendapat pekerjaan baru yang belum ada dalam daftar, peserta dapat menambahkan rencana kerja baru melalui menu Rencana, kemudian memilihnya pada formulir Aktivitas.

Jika suatu rencana kerja tidak dikerjakan pada hari itu, peserta tidak perlu membuat catatan progres untuk rencana tersebut.

Jangan mengisi progres secara otomatis atau menganggap rencana kerja sebagai pekerjaan yang sudah selesai.

Aktivitas harus memiliki tanggal yang jelas dan dapat dibedakan meskipun menggunakan rencana kerja yang sama pada beberapa hari berbeda.

Jika satu rencana kerja dipilih beberapa kali dalam satu hari, periksa struktur sistem yang sudah ada dan tentukan validasi yang sesuai agar tidak terjadi duplikasi laporan yang tidak disengaja.

Pertahankan mekanisme pengiriman, penyuntingan, revisi, dan validasi aktivitas yang sudah ada selama masih relevan.

Jika peserta belum mengisi aktivitas, jangan membuat data progres palsu. Penanganan aktivitas kosong harus mengikuti aturan sistem dan tidak boleh menghambat presensi.

5. Penyesuaian sisi pembimbing

A. Menu Pekerjaan

Hapus menu Pekerjaan lama sebagai fitur terpisah jika menu tersebut sebelumnya digunakan untuk membuat pekerjaan yang harus dipilih peserta.

Ganti fungsi informatifnya dengan tampilan daftar Rencana Kerja peserta binaan.

Pada halaman Rencana Kerja, pembimbing dapat:

Melihat nama peserta.

Melihat daftar rencana kerja yang dibuat peserta.

Melihat detail dan tanggal pembuatan atau perubahan jika datanya tersedia.

Mengetahui rencana kerja yang sedang dimiliki peserta.

Untuk tahap ini, pembimbing tidak perlu membuat, mengelola, atau memvalidasi rencana kerja. Peserta tetap menjadi pihak yang menginput rencananya sendiri.

B. Validasi Aktivitas

Pertahankan fitur validasi Aktivitas pembimbing.

Pembimbing tetap dapat:

Melihat aktivitas yang dikirim peserta binaan.

Melihat tanggal aktivitas.

Melihat rencana kerja yang terkait dengan setiap progres.

Memeriksa progres masing-masing rencana kerja.

Menyetujui aktivitas atau meminta revisi.

Memberikan catatan revisi jika mekanisme tersebut sudah tersedia.

Jangan menghapus validasi aktivitas dan jangan memindahkan proses validasi ke menu Rencana Kerja.

Pastikan pembimbing hanya dapat mengakses peserta yang berada dalam kewenangannya sesuai relasi dan periode penugasan yang berlaku.

6. Perubahan navigasi peserta magang

Ubah bottom navigation menjadi:

Presensi

Rencana

Aktivitas

Rekap

Pengajuan

Menu Edit Profil dihapus dari bottom navigation. Foto profil pada header menjadi akses menuju halaman profil atau pengaturan akun.

Pastikan tampilan mobile tetap nyaman digunakan dan navigasi desktop menyesuaikan layout yang sudah tersedia.

Pada sisi pembimbing, lakukan perubahan navigasi hanya jika diperlukan untuk mengganti menu Pekerjaan menjadi Rencana Kerja. Jangan merombak seluruh dashboard pembimbing.

7. Relasi database dan keamanan data

Periksa struktur database sebelum menentukan perubahan tabel.

Pastikan:

Rencana kerja terhubung dengan peserta yang membuatnya.

Aktivitas terhubung dengan peserta dan rencana kerja yang relevan.

Satu rencana kerja dapat memiliki beberapa catatan progres pada tanggal berbeda.

Progres setiap rencana kerja disimpan secara terpisah.

Riwayat aktivitas yang sudah divalidasi tidak berubah secara tidak sengaja ketika rencana kerja diedit.

Rencana kerja yang sudah memiliki aktivitas tidak boleh dihapus secara permanen jika penghapusan tersebut merusak relasi atau riwayat laporan.

Validasi backend diterapkan untuk kepemilikan rencana kerja, akses pembimbing, tanggal, dan isi progres.

Jika perlu mengubah database, gunakan migration baru yang aman dan kompatibel dengan data yang sudah ada.

Jangan menggunakan migrate:fresh karena database mungkin berisi data penting.

Jangan langsung menghapus tabel atau kolom lama sebelum memeriksa foreign key, relasi model, controller, laporan, dan penggunaan data di seluruh project.

8. Fitur yang harus tetap berfungsi

Jangan merusak fitur yang tidak berkaitan dengan perubahan ini, termasuk:

Presensi masuk dan pulang.

Validasi lokasi, foto, dan mode kerja yang sudah ada.

Pengajuan izin, sakit, cuti, dan tugas luar.

Riwayat serta rekap presensi.

Penilaian magang.

Relasi pembimbing dengan peserta dan periode penugasan.

Autentikasi dan hak akses setiap role.

Fitur cetak laporan yang masih digunakan.

Sesuaikan laporan yang sebelumnya mengambil data dari Pekerjaan atau Ringkasan Aktivitas agar dapat menampilkan rencana kerja dan progres yang baru.

9. UI/UX

Gunakan desain yang konsisten dengan sistem saat ini.

Buat formulir yang sederhana, mudah dipahami pemula, responsif di HP maupun laptop, dan menampilkan status validasi dengan jelas.

Pada halaman Aktivitas, tampilkan setiap rencana kerja sebagai bagian tersendiri yang memiliki input progres sendiri. Sediakan tombol untuk menambahkan pilihan rencana kerja lainnya.

Hindari duplikasi data dan jangan membuat fitur baru yang tidak diperlukan.

10. Pengujian wajib

Setelah perubahan selesai, pastikan skenario berikut berhasil:

Peserta dapat presensi meskipun belum memiliki rencana kerja.

Peserta dapat membuat dan mengedit rencana kerja.

Peserta dapat memilih satu atau beberapa rencana kerja saat mengisi aktivitas.

Setiap rencana kerja memiliki progres masing-masing.

Peserta dapat menggunakan rencana yang sama untuk aktivitas pada tanggal berbeda.

Peserta dapat menambahkan rencana baru jika mendapat tugas yang belum tercantum.

Rencana yang tidak dikerjakan tidak otomatis dianggap selesai.

Pembimbing dapat melihat rencana kerja tanpa perlu memvalidasinya.

Pembimbing tetap dapat menyetujui atau meminta revisi aktivitas.

Peserta tidak dapat memilih rencana kerja milik peserta lain.

Riwayat aktivitas dan penilaian magang tetap konsisten.

Fitur presensi, pengajuan, rekap, dan laporan lainnya tetap berjalan.

11. Hasil pengerjaan yang harus dilaporkan

Setelah implementasi selesai, jelaskan:

File yang diubah, ditambah, atau dihapus.

Perubahan tabel dan relasi database.

Migration yang perlu dijalankan.

Perubahan alur peserta dan pembimbing.

Hasil pengujian setiap skenario.

Kendala yang belum terselesaikan.

Kerjakan berdasarkan struktur project yang benar-benar ada. Jangan hanya memberikan contoh kode atau rekomendasi. Lakukan perubahan pada implementasi yang relevan dan pastikan fitur yang tidak berkaitan tetap berfungsi.

Urutan Bottom Navigation Peserta Magang

Urutan menu dari kiri ke kanan:

Presensi — melakukan presensi masuk dan pulang serta melihat status kehadiran.

Rencana — menambah, melihat, dan mengedit rencana kerja.

Aktivitas — mengisi progres pekerjaan berdasarkan rencana kerja dan memantau validasi pembimbing.

Pengajuan — mengajukan dan memantau izin, sakit, cuti, dan tugas luar.

Rekap — melihat riwayat dan rekap kehadiran serta laporan yang tersedia.

Terapkan urutan ini secara konsisten pada bottom navigation mobile. Pada desktop, sesuaikan urutan menu navigasi yang tersedia tanpa mengubah fungsi dan hak akses setiap menu. Edit Profil tetap diakses melalui foto profil pada header, bukan melalui bottom navigation.