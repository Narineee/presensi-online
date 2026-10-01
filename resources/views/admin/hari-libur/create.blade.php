@extends('layouts.admin')

@section('title', 'Tambah Hari Libur Manual')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Hari Libur Manual</h1>
            <p class="text-sm text-slate-500 mt-1">Input data tanggal merah atau cuti bersama khusus instansi/daerah.</p>
        </div>
        <a href="{{ route('admin.hari-libur.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.hari-libur.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Tanggal Libur -->
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tanggal Libur <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ old('tanggal') }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
                <p class="text-[11px] text-slate-400 mt-1">Pilih tanggal spesifik yang ditetapkan sebagai tanggal merah.</p>
                @error('tanggal')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Hari Libur -->
            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Hari Libur / Peringatan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama') }}"
                    placeholder="Contoh: Hari Kemerdekaan RI ke-81 / Cuti Bersama Idul Fitri"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
                @error('nama')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Hari Libur -->
            <div>
                <label for="jenis" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Jenis Libur <span class="text-rose-500">*</span>
                </label>
                <select
                    name="jenis"
                    id="jenis"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('jenis') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
                    <option value="Hari Libur Nasional" {{ old('jenis') === 'Hari Libur Nasional' ? 'selected' : '' }}>
                        Hari Libur Nasional (Tanggal Merah Resmi)
                    </option>
                    <option value="Cuti Bersama" {{ old('jenis') === 'Cuti Bersama' ? 'selected' : '' }}>
                        Cuti Bersama (Dispensasi Hari Kerja)
                    </option>
                </select>
                @error('jenis')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keterangan Tambahan -->
            <div>
                <label for="keterangan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Keterangan Tambahan (Opsional)
                </label>
                <input
                    type="text"
                    name="keterangan"
                    id="keterangan"
                    value="{{ old('keterangan') }}"
                    placeholder="Contoh: Sesuai SKB 3 Menteri / Libur Khusus Instansi"
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('keterangan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
                <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika sama dengan nama hari libur.</p>
                @error('keterangan')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Perlindungan Data Manual -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-slate-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div class="text-xs text-slate-700 leading-relaxed">
                    <span class="font-bold">Keamanan Data Manual:</span>
                    Data yang diinput melalui formulir ini akan tersimpan dengan sumber <code>manual</code>. Proses sinkronisasi API berikutnya tidak akan menimpa maupun menghapus data manual ini.
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.hari-libur.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-500/20 transition cursor-pointer">
                    Simpan Hari Libur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
