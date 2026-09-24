// =====================================================================
// DATABASE
// SISTEM INFORMASI MANAJEMEN MAGANG (SIM MAGANG)
// =====================================================================
//
// Role:
// 1. admin       -> mengelola akun, data magang, pembimbing, master data, dll.
// 2. magang      -> presensi + aktivitas harian + lembar penilaian
// 3. pembimbing  -> memantau/validasi aktivitas + menilai magang
//
// Catatan desain:
// - pengguna = akun untuk login
// - Detail profil dipisahkan berdasarkan role
// - Penilaian KHUSUS untuk anak magang
// - Penilaian dilakukan di akhir masa magang
// - Tidak ada self-register, akun dibuat oleh admin
// - WFH disimpan sebagai mode kerja pada presensi
// =====================================================================


// =====================================================================
// 1. PENGGUNA
// =====================================================================

Table pengguna {
  id integer [pk, increment]

  username varchar(50) [not null, unique]
  password varchar(255) [not null, note: 'Simpan dalam bentuk hash, jangan plain text']

  role varchar(30) [
    not null,
    note: 'admin, magang, pembimbing'
  ]

  is_active boolean [not null, default: true]

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Akun login. Dibuat oleh admin, tidak ada self-register.'
}


// =====================================================================
// 2. PEMBIMBING
// =====================================================================

Table pembimbing {
  id integer [pk, increment]

  pengguna_id integer [not null, unique]

  nip varchar(30) [unique]
  nama_lengkap varchar(100) [not null]
  jabatan varchar(50)
  no_hp varchar(20)

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Pembimbing magang yang melakukan validasi aktivitas dan penilaian akhir magang.'
}

// =====================================================================
// 3. DIVISI / SUB-BAGIAN
// =====================================================================

Table divisi {
  id integer [pk, increment]
  
  nama_divisi varchar(100) [not null]
  nama_pimpinan varchar(100) [not null]
  nip_pimpinan varchar(30)
  jabatan_pimpinan varchar(100)

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Master data divisi untuk ttd dinamis pimpinan sub-bagian di PDF.'
}

// =====================================================================
// 4. MAGANG
// =====================================================================

Table magang {
  id integer [pk, increment]

  pengguna_id integer [not null, unique]
  pembimbing_id integer [not null]
  divisi_id integer [note: 'FK ke divisi untuk ttd pimpinan sub-bagian saat cetak PDF']

  no_induk varchar(30)
  nama_lengkap varchar(100) [not null]
  jenis_kelamin enum('L', 'P') [note: 'L = Laki-laki, P = Perempuan']
  jurusan varchar(100)
  instansi_pendidikan varchar(150)
  no_hp varchar(20)
  foto varchar(255)

  tanggal_mulai date
  tanggal_selesai date

  status enum(
    'aktif',
    'selesai',
    'cuti'
  ) [not null, default: 'aktif']

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Profil anak magang.'
}


// =====================================================================
// 5. PRESENSI
// =====================================================================

Table presensi {
  id integer [pk, increment]

  pengguna_id integer [not null]

  tanggal date [not null]

  jam_masuk time
  jam_keluar time

  status enum(
    'hadir',
    'terlambat',
    'izin',
    'sakit',
    'alpha',
    'cuti'
  ) [not null, default: 'hadir']

  mode_kerja enum(
    'onsite',
    'wfh'
  ) [not null, default: 'onsite']

  foto_masuk varchar(255)
  foto_keluar varchar(255)

  lokasi_masuk varchar(255) [
    note: 'Latitude, longitude, atau alamat hasil geolokasi'
  ]

  lokasi_keluar varchar(255)

  keterangan varchar(255)

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  indexes {
    (pengguna_id, tanggal) [
      unique,
      note: 'Satu pengguna hanya boleh memiliki satu presensi per hari'
    ]
  }

  Note: 'Presensi harian untuk magang. Mode kerja dapat berupa onsite atau WFH.'
}


// =====================================================================
// 6. PENGAJUAN IZIN & SAKIT
// =====================================================================

Table pengajuan_izin {
  id integer [pk, increment]

  pengguna_id integer [not null]
  
  jenis_izin enum('sakit', 'izin', 'cuti') [not null]
  tanggal_mulai date [not null]
  tanggal_selesai date [not null]
  
  alasan text [not null]
  bukti_file varchar(255) [note: 'Path file foto atau surat dokter']
  
  status_approval enum('pending', 'disetujui', 'ditolak') [not null, default: 'pending']
  
  validated_by integer [note: 'FK ke pembimbing.id yang menyetujui izin']
  validated_at timestamp

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Tabel khusus untuk merekam pengajuan izin/sakit peserta magang beserta lampiran buktinya.'
}


// =====================================================================
// 7. AKTIVITAS
// =====================================================================

Table aktivitas {
  id integer [pk, increment]

  pengguna_id integer [not null]

  tanggal date [not null]

  isi text [not null]

  progress integer [
    not null,
    default: 0,
    note: 'Persentase progress 0-100'
  ]

  status enum(
    'pending',
    'approve',
    'revisi'
  ) [not null, default: 'pending']

  validated_by integer [
    note: 'FK ke pembimbing.id'
  ]

  validated_at timestamp

  catatan_validasi text

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Aktivitas harian magang yang dapat divalidasi pembimbing.'
}


// =====================================================================
// 8. KRITERIA PENILAIAN
// =====================================================================

Table kriteria_penilaian {
  id integer [pk, increment]

  nama varchar(100) [not null]

  bobot integer [
    not null,
    default: 1,
    note: 'Bobot kriteria untuk perhitungan nilai akhir'
  ]

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Daftar kriteria penilaian akhir anak magang.'
}


// =====================================================================
// 9. PENILAIAN
// =====================================================================

Table penilaian {
  id integer [pk, increment]

  magang_id integer [
    not null,
    unique,
    note: 'Satu magang hanya memiliki satu penilaian akhir'
  ]

  pembimbing_id integer [
    not null,
    note: 'Pembimbing yang memberikan penilaian'
  ]

  total_nilai integer [
    note: 'Nilai akhir hasil perhitungan detail penilaian'
  ]

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  Note: 'Penilaian akhir anak magang setelah masa magang selesai.'
}


// =====================================================================
// 10. DETAIL PENILAIAN
// =====================================================================

Table detail_penilaian {
  id integer [pk, increment]

  penilaian_id integer [not null]
  kriteria_id integer [not null]

  nilai integer [
    not null,
    note: 'Nilai untuk kriteria tersebut'
  ]

  created_at timestamp [not null, default: `now()`]
  updated_at timestamp

  indexes {
    (penilaian_id, kriteria_id) [
      unique,
      note: 'Satu kriteria hanya boleh muncul sekali dalam satu penilaian'
    ]
  }

  Note: 'Detail nilai setiap kriteria penilaian magang.'
}


// =====================================================================
// RELASI AKUN / PROFIL
// =====================================================================

Ref: pembimbing.pengguna_id - pengguna.id
Ref: magang.pengguna_id - pengguna.id

// =====================================================================
// RELASI MAGANG KE DIVISI (TTD PIMPINAN)
// =====================================================================

Ref: magang.divisi_id > divisi.id

// =====================================================================
// RELASI PEMBIMBING
// =====================================================================

Ref: magang.pembimbing_id > pembimbing.id

// =====================================================================
// RELASI PRESENSI & PENGAJUAN IZIN
// =====================================================================

Ref: presensi.pengguna_id > pengguna.id
Ref: pengajuan_izin.pengguna_id > pengguna.id
Ref: pengajuan_izin.validated_by > pembimbing.id

// =====================================================================
// RELASI AKTIVITAS
// =====================================================================

Ref: aktivitas.pengguna_id > pengguna.id
Ref: aktivitas.validated_by > pembimbing.id

// =====================================================================
// RELASI PENILAIAN
// =====================================================================

Ref: penilaian.magang_id > magang.id
Ref: penilaian.pembimbing_id > pembimbing.id
Ref: detail_penilaian.penilaian_id > penilaian.id
Ref: detail_penilaian.kriteria_id > kriteria_penilaian.id