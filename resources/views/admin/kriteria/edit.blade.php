@extends('layouts.admin')

@section('title', 'Edit Kriteria Penilaian')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Kriteria Penilaian</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui nama indikator atau bobot kriteria evaluasi magang.</p>
        </div>
        <a href="{{ route('admin.kriteria.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.kriteria.update', $kriteria->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Kriteria -->
            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Kriteria / Indikator Penilaian <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama', $kriteria->nama) }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                >
                <p class="text-[11px] text-slate-400 mt-1">ID Kriteria: #{{ $kriteria->id }}</p>
                @error('nama')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bobot Nilai -->
            <div>
                <label for="bobot" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Bobot Penilaian <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="number"
                        name="bobot"
                        id="bobot"
                        value="{{ old('bobot', $kriteria->bobot) }}"
                        min="1"
                        max="100"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('bobot') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium pr-12"
                    >
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">
                        Poin
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Masukkan angka bulat (contoh: 20, 25, 30).</p>
                @error('bobot')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Opsi Kriteria Presensi Otomatis (Objektif) -->
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-1">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="is_presensi" value="1" {{ old('is_presensi', $kriteria->is_presensi) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                    <div>
                        <span class="text-xs font-bold text-slate-800">Gunakan sebagai Kriteria Penilaian Presensi Otomatis (Objektif)</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Jika dicentang, nilai kriteria ini akan dihitung secara otomatis oleh sistem berdasarkan total menit kerja presensi digital (8 jam/hari, potong 50% jika lupa checkout, izin resmi hadir penuh, tanggal merah tidak memotong).</p>
                    </div>
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.kriteria.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
