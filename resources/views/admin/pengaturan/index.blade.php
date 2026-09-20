@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Aplikasi</h1>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    Konfigurasi Sistem
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Kelola data identitas instansi dan data Ketua / Kepala Dinas (pimpinan tertinggi) untuk penandatanganan dokumen dan rekap laporan.
            </p>
        </div>
    </div>

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-xs font-bold text-emerald-900">Berhasil Disimpan!</p>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Alert Notifikasi Error Form -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 8.25h.008v.008H12v-.008z" />
            </svg>
            <div>
                <p class="text-xs font-bold text-rose-900">Terdapat Kesalahan Pengisian Formulir:</p>
                <ul class="text-xs text-rose-700 list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.pengaturan.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri & Tengah: Form Input (2 Kolom) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- KARTU 1: Data Ketua Dinas / Pimpinan Tertinggi -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-6">
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Pimpinan Tertinggi</span>
                            <h2 class="text-base font-bold text-slate-900 mt-0.5">Data Ketua / Kepala Dinas</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pejabat pimpinan utama instansi yang berwenang menandatangani dan mengesahkan berkas akhir.
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Nama Kepala Dinas -->
                        <div class="sm:col-span-2">
                            <label for="nama_kepala_dinas" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap Ketua / Kepala Dinas <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="nama_kepala_dinas"
                                id="nama_kepala_dinas"
                                value="{{ old('nama_kepala_dinas', $pengaturan->nama_kepala_dinas) }}"
                                placeholder="Contoh: Dr. H. Asep Saepudin, M.Si"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_kepala_dinas') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">Lengkap dengan gelar akademik maupun kebangsawanan.</p>
                            @error('nama_kepala_dinas')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIP Kepala Dinas -->
                        <div>
                            <label for="nip_kepala_dinas" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                NIP (Nomor Induk Pegawai)
                            </label>
                            <input
                                type="text"
                                name="nip_kepala_dinas"
                                id="nip_kepala_dinas"
                                value="{{ old('nip_kepala_dinas', $pengaturan->nip_kepala_dinas) }}"
                                placeholder="Contoh: 19720315 199803 1 004"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nip_kepala_dinas') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('nip_kepala_dinas')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jabatan Resmi -->
                        <div>
                            <label for="jabatan_kepala_dinas" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jabatan Resmi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="jabatan_kepala_dinas"
                                id="jabatan_kepala_dinas"
                                value="{{ old('jabatan_kepala_dinas', $pengaturan->jabatan_kepala_dinas) }}"
                                placeholder="Contoh: Kepala Dinas Komunikasi dan Informatika"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('jabatan_kepala_dinas') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('jabatan_kepala_dinas')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pangkat / Golongan -->
                        <div>
                            <label for="pangkat_golongan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Pangkat / Golongan Ruang
                            </label>
                            <input
                                type="text"
                                name="pangkat_golongan"
                                id="pangkat_golongan"
                                value="{{ old('pangkat_golongan', $pengaturan->pangkat_golongan) }}"
                                placeholder="Contoh: Pembina Utama Muda (IV/c)"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('pangkat_golongan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('pangkat_golongan')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kota Penandatanganan Dokumen -->
                        <div>
                            <label for="kota_surat" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kota Penandatanganan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="kota_surat"
                                id="kota_surat"
                                value="{{ old('kota_surat', $pengaturan->kota_surat) }}"
                                placeholder="Contoh: Banjarbaru"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('kota_surat') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('kota_surat')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: Identitas Instansi & Sistem -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-6">
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Organisasi & Instansi</span>
                            <h2 class="text-base font-bold text-slate-900 mt-0.5">Identitas Instansi & Aplikasi</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Nama instansi, nama sistem, serta alamat dan kontak resmi yang tertera pada dokumen.
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A48.243 48.243 0 0012 9.75c-2.551 0-5.056.2-7.5.583V21h15z" />
                            </svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Nama Instansi -->
                        <div>
                            <label for="nama_instansi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Instansi / Dinas <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="nama_instansi"
                                id="nama_instansi"
                                value="{{ old('nama_instansi', $pengaturan->nama_instansi) }}"
                                placeholder="Contoh: Dinas Komunikasi dan Informatika"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_instansi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('nama_instansi')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Aplikasi -->
                        <div>
                            <label for="nama_aplikasi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Aplikasi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="nama_aplikasi"
                                id="nama_aplikasi"
                                value="{{ old('nama_aplikasi', $pengaturan->nama_aplikasi) }}"
                                placeholder="Contoh: Sistem Presensi & Aktivitas Digital"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_aplikasi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('nama_aplikasi')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="sm:col-span-2">
                            <label for="alamat_instansi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Kantor Instansi
                            </label>
                            <textarea
                                name="alamat_instansi"
                                id="alamat_instansi"
                                rows="2"
                                placeholder="Contoh: Jl. Panglima Batur No. 1, Kota Banjarbaru, Kalimantan Selatan"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('alamat_instansi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >{{ old('alamat_instansi', $pengaturan->alamat_instansi) }}</textarea>
                            @error('alamat_instansi')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label for="telepon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor Telepon / Fax
                            </label>
                            <input
                                type="text"
                                name="telepon"
                                id="telepon"
                                value="{{ old('telepon', $pengaturan->telepon) }}"
                                placeholder="Contoh: (0511) 4772555"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('telepon') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Email Resmi
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $pengaturan->email) }}"
                                placeholder="Contoh: diskominfo@banjarbarukota.go.id"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Website -->
                        <div class="sm:col-span-2">
                            <label for="website" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Situs Web Resmi (Website)
                            </label>
                            <input
                                type="text"
                                name="website"
                                id="website"
                                value="{{ old('website', $pengaturan->website) }}"
                                placeholder="Contoh: https://diskominfo.banjarbarukota.go.id"
                                class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('website') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium transition"
                            >
                            @error('website')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Form -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Perubahan Pengaturan</span>
                    </button>
                </div>

            </div>

            <!-- Kolom Kanan: Live Preview Format Tanda Tangan & Kop (1 Kolom) -->
            <div class="space-y-6">

                <!-- Pratinjau Tanda Tangan Pimpinan -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pratinjau Tanda Tangan</span>
                    </div>

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Tampilan tanda tangan resmi Kepala Dinas saat dicetak pada lembar pengesahan atau dokumen dinas:
                    </p>

                    <!-- Mock Kartu Tanda Tangan Dokumen -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/70 text-center space-y-12">
                        <div>
                            <p class="text-xs text-slate-500">
                                <span id="preview-kota">{{ $pengaturan->kota_surat }}</span>, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                            </p>
                            <p id="preview-jabatan" class="text-xs font-bold text-slate-800 mt-0.5">
                                {{ $pengaturan->jabatan_kepala_dinas }}
                            </p>
                            <p id="preview-instansi" class="text-[10px] text-slate-500">
                                {{ $pengaturan->nama_instansi }}
                            </p>
                        </div>

                        <div>
                            <p id="preview-nama" class="text-xs font-extrabold text-slate-900 underline">
                                {{ $pengaturan->nama_kepala_dinas }}
                            </p>
                            <p id="preview-pangkat" class="text-[10px] text-slate-600 mt-0.5">
                                {{ $pengaturan->pangkat_golongan ?? '-' }}
                            </p>
                            <p id="preview-nip" class="text-[10px] text-slate-500">
                                NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400 bg-blue-50/50 p-3 rounded-xl border border-blue-100 flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Pratinjau di atas diperbarui secara langsung (real-time) saat Anda mengetik di formulir samping.</span>
                    </div>
                </div>

                <!-- Informasi Bantuan & Panduan -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Panduan Pengaturan</h3>
                    <ul class="text-xs text-slate-600 space-y-2 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0 mt-1.5"></span>
                            <span><strong>Ketua / Kepala Dinas</strong> adalah jabatan pimpinan tertinggi instansi yang menaungi seluruh divisi dan unit magang.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0 mt-1.5"></span>
                            <span>Apabila terjadi mutasi atau pergantian pimpinan, cukup perbarui nama dan NIP di halaman ini tanpa perlu mengubah kode program.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0 mt-1.5"></span>
                            <span>Data pimpinan sub-bagian / divisi teknis tetap dapat dikelola secara terpisah melalui menu <strong>Divisi / Sub-Bagian</strong>.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputNama = document.getElementById('nama_kepala_dinas');
        const inputNip = document.getElementById('nip_kepala_dinas');
        const inputJabatan = document.getElementById('jabatan_kepala_dinas');
        const inputPangkat = document.getElementById('pangkat_golongan');
        const inputKota = document.getElementById('kota_surat');
        const inputInstansi = document.getElementById('nama_instansi');

        const previewNama = document.getElementById('preview-nama');
        const previewNip = document.getElementById('preview-nip');
        const previewJabatan = document.getElementById('preview-jabatan');
        const previewPangkat = document.getElementById('preview-pangkat');
        const previewKota = document.getElementById('preview-kota');
        const previewInstansi = document.getElementById('preview-instansi');

        if (inputNama && previewNama) {
            inputNama.addEventListener('input', function () {
                previewNama.textContent = this.value.trim() || '(Nama Kepala Dinas)';
            });
        }

        if (inputNip && previewNip) {
            inputNip.addEventListener('input', function () {
                previewNip.textContent = this.value.trim() ? 'NIP. ' + this.value.trim() : 'NIP. -';
            });
        }

        if (inputJabatan && previewJabatan) {
            inputJabatan.addEventListener('input', function () {
                previewJabatan.textContent = this.value.trim() || 'Kepala Dinas';
            });
        }

        if (inputPangkat && previewPangkat) {
            inputPangkat.addEventListener('input', function () {
                previewPangkat.textContent = this.value.trim() || '-';
            });
        }

        if (inputKota && previewKota) {
            inputKota.addEventListener('input', function () {
                previewKota.textContent = this.value.trim() || 'Kota';
            });
        }

        if (inputInstansi && previewInstansi) {
            inputInstansi.addEventListener('input', function () {
                previewInstansi.textContent = this.value.trim() || 'Nama Instansi';
            });
        }
    });
</script>
@endpush
