@extends('layouts.mobile')

@section('title', 'Kelola Profil Akun')

@section('content')
@php
    $magang = $user->magang;
    $namaLengkap = $magang->nama_lengkap ?? $user->username;
    $inisial = collect(explode(' ', trim($namaLengkap)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
@endphp

{{-- Header Sesuai Wireframe edit-profil.png --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <div class="flex items-center gap-3 min-w-0 flex-1 mr-2">
        <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-xs hover:bg-slate-50 transition" title="Kembali ke Presensi">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight truncate">Edit Profil</h1>
            <p class="text-xs text-slate-500 font-medium truncate">Perbarui informasi pribadi</p>
        </div>
    </div>

    {{-- Tombol Logout Cepat di Header --}}
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
        @csrf
        <button type="submit" title="Keluar dari Akun (Logout)" class="grid h-11 w-11 place-items-center rounded-full border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 shadow-xs transition cursor-pointer">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
            </svg>
        </button>
    </form>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    {{-- Alert Jika Profil Belum Lengkap --}}
    @if($isProfileIncomplete)
        <div class="rounded-3xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-4 text-xs text-amber-900 shadow-2xs flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-sm shadow-2xs">
                ⚠️
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-amber-950 truncate">Lengkapi Profil Anda Terlebih Dahulu</p>
                <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed break-words">
                    Sesuai ketentuan, peserta magang wajib melengkapi pasfoto resmi, asal kampus/sekolah, jurusan, dan kontak aktif untuk keperluan lembar penilaian dan berkas rekapan presensi resmi.
                </p>
            </div>
        </div>
    @endif

    {{-- Form Edit Profil --}}
    <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Kartu Utama --}}
        <div class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
            {{-- Label Judul Bagian --}}
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-900">
                PASFOTO RESMI PESERTA
            </h2>

            {{-- Avatar & Unggah Foto --}}
            <div class="flex flex-col items-center text-center">
                {{-- Kotak Preview Pasfoto --}}
                <div class="relative h-28 w-28 overflow-hidden rounded-2xl border-2 border-slate-800 bg-slate-100 grid place-items-center text-2xl font-black text-brand shadow-sm">
                    @if ($magang?->foto_url)
                        <img id="preview-image" src="{{ $magang->foto_url }}" alt="{{ $namaLengkap }}" class="h-full w-full object-cover">
                        <span id="preview-placeholder" class="hidden">{{ $inisial }}</span>
                    @else
                        <img id="preview-image" src="" alt="Preview" class="hidden h-full w-full object-cover">
                        <span id="preview-placeholder">{{ $inisial }}</span>
                    @endif
                </div>

                {{-- File Input --}}
                <div class="mt-3.5 w-full flex justify-center">
                    <label for="foto-input" class="cursor-pointer inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-4 py-2 text-xs font-bold text-slate-800 shadow-2xs transition">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <span>Choose File</span>
                    </label>
                    <input type="file" name="foto" id="foto-input" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="handleFotoPreview(this)">
                </div>
                <p id="foto-filename" class="mt-1 text-[11px] text-slate-500 font-medium truncate max-w-xs text-center">
                    No file chosen
                </p>

                <p class="mt-1.5 text-center text-[11px] leading-relaxed text-slate-500 max-w-xs break-words">
                    Format JPG atau PNG, ukuran maksimal 2MB. Disarankan pasfoto format setengah badan dengan latar rapi.
                </p>
            </div>

            {{-- Garis Pemisah --}}
            <hr class="border-slate-200/80">

            {{-- 5 Input Fields --}}
            <div class="space-y-3.5">
                {{-- Field 1: Nama Lengkap --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        NAMA LENGKAP <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        value="{{ old('nama_lengkap', $magang->nama_lengkap ?? '') }}"
                        required
                        placeholder="Masukkan nama lengkap"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                </div>

                {{-- Field 2: Asal Kampus / Sekolah --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        ASAL KAMPUS / SEKOLAH <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="instansi_pendidikan"
                        value="{{ old('instansi_pendidikan', $magang->instansi_pendidikan ?? '') }}"
                        required
                        placeholder="Contoh: Universitas Lambung Mangkurat"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                </div>

                {{-- Field 3: Program Studi / Jurusan --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        PROGRAM STUDI / JURUSAN <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="jurusan"
                        value="{{ old('jurusan', $magang->jurusan ?? '') }}"
                        required
                        placeholder="Contoh: S1 Teknologi Informasi"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                </div>

                {{-- Field 4: Nomor HP / WhatsApp --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        NOMOR HP / WHATSAPP <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="tel"
                        name="no_hp"
                        value="{{ old('no_hp', $magang->no_hp ?? '') }}"
                        required
                        placeholder="Contoh: 08123456789"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                </div>

                {{-- Field 5: Jenis Kelamin --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        JENIS KELAMIN
                    </label>
                    <select
                        name="jenis_kelamin"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin', $magang->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin', $magang->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                </div>

                {{-- Field Tambahan: Ganti Password Opsional --}}
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                        GANTI KATA SANDI <span class="text-[10px] font-normal text-slate-400 normal-case">(Kosongkan bila tidak diganti)</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 6 karakter"
                        class="w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition"
                    >
                </div>
            </div>

            {{-- Kartu Info Akun dari Admin --}}
            <div class="mt-4 rounded-2xl glass-subcard p-4 border border-white text-xs space-y-2">
                <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium shrink-0">Nomor Induk (NIM/NISN)</span>
                    <span class="font-mono font-bold text-slate-800 text-right min-w-0 break-words ml-2">{{ $magang->no_induk ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium shrink-0">Username Akun</span>
                    <span class="font-mono font-bold text-slate-800 text-right min-w-0 break-words ml-2">{{ $user->username }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium shrink-0">Divisi Penempatan</span>
                    <span class="font-semibold text-slate-800 text-right min-w-0 break-words ml-2">{{ $magang->divisi->nama_divisi ?? 'Belum ditentukan' }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-slate-500 font-medium shrink-0">Pembimbing Lapangan</span>
                    <span class="font-semibold text-slate-800 text-right min-w-0 break-words ml-2">{{ $magang->pembimbing->nama_lengkap ?? 'Belum ditentukan' }}</span>
                </div>
            </div>
        </div>

        {{-- Tombol Simpan Perubahan Sesuai Wireframe --}}
        <button
            type="submit"
            class="btn-brand-primary w-full rounded-2xl py-4 text-center text-sm font-extrabold text-white shadow-sm cursor-pointer"
        >
            Simpan Perubahan
        </button>
    </form>

    {{-- Tombol Logout Khusus di Bawah Form --}}
    <form action="{{ route('logout') }}" method="POST" class="pt-1">
        @csrf
        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50/80 hover:bg-rose-100 text-rose-700 py-3.5 text-sm font-bold shadow-2xs transition active:scale-[0.99] cursor-pointer"
        >
            <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
            </svg>
            <span>Keluar dari Akun (Logout)</span>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function handleFotoPreview(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const filenameEl = document.getElementById('foto-filename');
            if (filenameEl) {
                filenameEl.textContent = file.name;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('preview-image');
                const placeholder = document.getElementById('preview-placeholder');

                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
