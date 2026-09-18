@extends('layouts.admin')

@section('title', 'Edit Divisi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Data Divisi</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi divisi dan pimpinan sub-bagian.</p>
        </div>
        <a href="{{ route('admin.divisi.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Edit Divisi -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.divisi.update', $divisi->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Data Divisi -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100">
                    1. Informasi Divisi
                </h3>
                <div class="mt-4">
                    <label for="nama_divisi" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Divisi / Sub-Bagian <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_divisi"
                        id="nama_divisi"
                        value="{{ old('nama_divisi', $divisi->nama_divisi) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_divisi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                    >
                    @error('nama_divisi')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Data Pimpinan Sub-Bagian -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100">
                    2. Data Pimpinan (Untuk Tanda Tangan Laporan PDF)
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="nama_pimpinan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Pimpinan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_pimpinan"
                            id="nama_pimpinan"
                            value="{{ old('nama_pimpinan', $divisi->nama_pimpinan) }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_pimpinan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('nama_pimpinan')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nip_pimpinan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            NIP Pimpinan (Opsional)
                        </label>
                        <input
                            type="text"
                            name="nip_pimpinan"
                            id="nip_pimpinan"
                            value="{{ old('nip_pimpinan', $divisi->nip_pimpinan) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>

                    <div>
                        <label for="jabatan_pimpinan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jabatan Pimpinan (Opsional)
                        </label>
                        <input
                            type="text"
                            name="jabatan_pimpinan"
                            id="jabatan_pimpinan"
                            value="{{ old('jabatan_pimpinan', $divisi->jabatan_pimpinan) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>
                </div>
            </div>

            <!-- Section 3: Titik Lokasi Kantor & Radius Presensi (Opsional) -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100">
                    3. Pengaturan Lokasi Presensi (Opsional)
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Latitude
                        </label>
                        <input
                            type="text"
                            name="latitude"
                            id="latitude"
                            value="{{ old('latitude', $divisi->latitude) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-mono"
                        >
                    </div>

                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Longitude
                        </label>
                        <input
                            type="text"
                            name="longitude"
                            id="longitude"
                            value="{{ old('longitude', $divisi->longitude) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-mono"
                        >
                    </div>

                    <div>
                        <label for="radius_meter" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Radius (Meter)
                        </label>
                        <input
                            type="number"
                            name="radius_meter"
                            id="radius_meter"
                            value="{{ old('radius_meter', $divisi->radius_meter) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.divisi.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Perbarui Data Divisi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
