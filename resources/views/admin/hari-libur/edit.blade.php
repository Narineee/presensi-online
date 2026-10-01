@extends('layouts.admin')

@section('title', 'Edit Hari Libur')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Hari Libur</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui detail informasi hari libur atau cuti bersama.</p>
        </div>
        <a href="{{ route('admin.hari-libur.index', ['tahun' => $hariLibur->tanggal ? $hariLibur->tanggal->format('Y') : null]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.hari-libur.update', $hariLibur->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Info Sumber Data Saat Ini -->
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">Sumber Data Saat Ini:</span>
                    @if($hariLibur->sumber === 'api')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            API Indonesia
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-800">
                            Manual Admin
                        </span>
                    @endif
                </div>
                @if($hariLibur->external_id)
                    <span class="text-[11px] font-mono text-slate-400">ID: {{ $hariLibur->external_id }}</span>
                @endif
            </div>

            <!-- Tanggal Libur -->
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tanggal Libur <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ old('tanggal', $hariLibur->tanggal ? $hariLibur->tanggal->format('Y-m-d') : '') }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
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
                    value="{{ old('nama', $hariLibur->nama ?: $hariLibur->keterangan) }}"
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
                    <option value="Hari Libur Nasional" {{ old('jenis', $hariLibur->jenis) === 'Hari Libur Nasional' ? 'selected' : '' }}>
                        Hari Libur Nasional (Tanggal Merah Resmi)
                    </option>
                    <option value="Cuti Bersama" {{ old('jenis', $hariLibur->jenis) === 'Cuti Bersama' ? 'selected' : '' }}>
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
                    value="{{ old('keterangan', $hariLibur->keterangan) }}"
                    class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('keterangan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 text-sm text-slate-800 font-medium"
                >
                @error('keterangan')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Proteksi Perubahan -->
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-xs text-amber-800 leading-relaxed">
                    <span class="font-bold">Kustomisasi Manual:</span>
                    Menyimpan perubahan form ini akan menetapkan sumber data menjadi <code>manual</code> agar perubahan Anda terlindungi dan tidak tertimpa saat sinkronisasi API berikutnya.
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.hari-libur.index', ['tahun' => $hariLibur->tanggal ? $hariLibur->tanggal->format('Y') : null]) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-500/20 transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
