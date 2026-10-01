@extends('layouts.admin')

@section('title', 'Tambah Magang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Peserta Magang Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Daftarkan profil magang, tentukan pembimbing & divisi, serta buatkan akun login.</p>
        </div>
        <a href="{{ route('admin.magang.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Tambah Magang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.magang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Section 1: Data Pokok Peserta Magang -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Data Pokok Peserta Magang</span>
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_lengkap" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_lengkap"
                            id="nama_lengkap"
                            value="{{ old('nama_lengkap') }}"
                            placeholder="Contoh: Rian Pratama"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_lengkap') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('nama_lengkap')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_induk" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor Induk (NIM / NIS) <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            type="text"
                            name="no_induk"
                            id="no_induk"
                            value="{{ old('no_induk') }}"
                            placeholder="Contoh: 21010045"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>

                    <div class="sm:col-span-2 p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <div>
                            <span class="font-bold">Ketentuan Pengisian:</span>
                            Admin hanya perlu menginput Nama Lengkap dan Nomor Induk. Biodata lanjutan (seperti asal instansi/kampus, jurusan, jenis kelamin, nomor kontak WhatsApp, dan pasfoto) akan dilengkapi secara mandiri oleh peserta magang melalui menu <strong>Profil Akun</strong> setelah login.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Penempatan & Pembimbing -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                    <span>Penempatan Divisi & Pembimbing</span>
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="pembimbing_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pembimbing Lapangan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="pembimbing_id"
                            id="pembimbing_id"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('pembimbing_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                        >
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach ($pembimbing as $p)
                                <option value="{{ $p->id }}" {{ old('pembimbing_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }} (NIP: {{ $p->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('pembimbing_id')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="divisi_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Divisi / Sub-Bagian (Untuk TTD PDF Pimpinan)
                        </label>
                        <select
                            name="divisi_id"
                            id="divisi_id"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                        >
                            <option value="">-- Tanpa Divisi Khusus --</option>
                            @foreach ($divisi as $d)
                                <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama_divisi }} (Pimpinan: {{ $d->nama_pimpinan ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Periode & Status Magang -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Periode Pelaksanaan & Status</span>
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            value="{{ old('tanggal_mulai') }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_mulai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('tanggal_mulai')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            value="{{ old('tanggal_selesai') }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_selesai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('tanggal_selesai')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Status Magang <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                        >
                            <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ old('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="cuti" {{ old('status') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 4: Akun Login Magang -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Akun Login Peserta Magang</span>
                </h3>
                <p class="text-xs text-slate-500 mt-2">
                    Kredensial ini digunakan anak magang untuk presensi harian dan mengisi laporan aktivitas.
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Data wajah tidak diisi oleh admin. Peserta akan diminta mendaftarkan wajahnya sendiri saat pertama kali login.
                </p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Username Login <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username') }}"
                            placeholder="Contoh: magang_rian"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('username') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('username')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Password Login <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Minimal 6 karakter"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500"
                            >
                            <span class="text-sm font-semibold text-slate-700">Akun Aktif (Dapat Login ke Aplikasi)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.magang.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Simpan Data Magang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
