@extends('layouts.admin')

@section('title', 'Tambah Jadwal Shift')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Jadwal Shift CS</h1>
            <p class="text-sm text-slate-500 mt-1">Tetapkan jadwal shift kerja harian untuk staf Customer Service.</p>
        </div>
        <a href="{{ route('admin.jadwal-shift.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Tambah Jadwal Shift -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.jadwal-shift.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="cs_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Staf CS <span class="text-rose-500">*</span>
                </label>
                <select
                    name="cs_id"
                    id="cs_id"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('cs_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                >
                    <option value="">-- Pilih Customer Service --</option>
                    @foreach ($csList as $c)
                        <option value="{{ $c->id }}" {{ old('cs_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_lengkap }} (NIK: {{ $c->nik }})
                        </option>
                    @endforeach
                </select>
                @error('cs_id')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="shift_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Master Shift <span class="text-rose-500">*</span>
                </label>
                <select
                    name="shift_id"
                    id="shift_id"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('shift_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                >
                    <option value="">-- Pilih Shift Kerja --</option>
                    @foreach ($shiftList as $s)
                        <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama }} ({{ substr($s->jam_masuk, 0, 5) }} - {{ substr($s->jam_keluar, 0, 5) }})
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Shift <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal"
                        id="tanggal"
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                    >
                    @error('tanggal')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Shift <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                    >
                        <option value="terjadwal" {{ old('status', 'terjadwal') === 'terjadwal' ? 'selected' : '' }}>Terjadwal (Bekerja)</option>
                        <option value="libur" {{ old('status') === 'libur' ? 'selected' : '' }}>Libur</option>
                        <option value="izin" {{ old('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="cuti" {{ old('status') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Keterangan (Opsional)
                </label>
                <input
                    type="text"
                    name="keterangan"
                    id="keterangan"
                    value="{{ old('keterangan') }}"
                    placeholder="Contoh: Menggantikan shift pagi / piket khusus"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                >
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.jadwal-shift.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Simpan Jadwal Shift
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
