@extends('layouts.admin')

@section('title', 'Tambah Shift')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Master Shift Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Buat aturan jam kerja dan toleransi keterlambatan shift CS.</p>
        </div>
        <a href="{{ route('admin.shift.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Tambah Shift -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.shift.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Shift <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama') }}"
                    placeholder="Contoh: Shift Pagi / Shift Siang"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                >
                @error('nama')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jam_masuk" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jam Masuk <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="jam_masuk"
                        id="jam_masuk"
                        value="{{ old('jam_masuk', '08:00') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('jam_masuk') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-mono font-medium"
                    >
                    @error('jam_masuk')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_keluar" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jam Keluar <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="jam_keluar"
                        id="jam_keluar"
                        value="{{ old('jam_keluar', '17:00') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('jam_keluar') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-mono font-medium"
                    >
                    @error('jam_keluar')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="toleransi_masuk" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Toleransi Keterlambatan (Menit)
                </label>
                <input
                    type="number"
                    name="toleransi_masuk"
                    id="toleransi_masuk"
                    value="{{ old('toleransi_masuk', 15) }}"
                    min="0"
                    max="120"
                    placeholder="0"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                >
                <p class="text-[11px] text-slate-400 mt-1">CS tidak dianggap terlambat jika hadir dalam rentang waktu toleransi ini.</p>
                @error('toleransi_masuk')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500"
                    >
                    <span class="text-sm font-semibold text-slate-700">Shift Aktif (Dapat Digunakan untuk Penjadwalan)</span>
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.shift.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Simpan Data Shift
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
