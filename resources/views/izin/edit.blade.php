@extends('layouts.user')

@section('title', 'Edit Permohonan Izin / Sakit')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Permohonan Izin / Sakit</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui data tanggal, alasan, atau berkas lampiran bukti.</p>
        </div>
        <a href="{{ route('izin.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form action="{{ route('izin.update', $izin->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Pilihan Jenis Izin -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Jenis Permohonan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-rose-400 cursor-pointer transition has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50">
                        <input type="radio" name="jenis_izin" value="sakit" class="sr-only" {{ old('jenis_izin', $izin->jenis_izin) === 'sakit' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🩺</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Sakit</div>
                                <div class="text-[10px] text-slate-400">Dengan surat dokter</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                        <input type="radio" name="jenis_izin" value="izin" class="sr-only" {{ old('jenis_izin', $izin->jenis_izin) === 'izin' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">📋</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Izin Keperluan</div>
                                <div class="text-[10px] text-slate-400">Ada halangan penting</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-purple-400 cursor-pointer transition has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/50">
                        <input type="radio" name="jenis_izin" value="cuti" class="sr-only" {{ old('jenis_izin', $izin->jenis_izin) === 'cuti' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🌴</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Cuti</div>
                                <div class="text-[10px] text-slate-400">Hak libur resmi</div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('jenis_izin')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        value="{{ old('tanggal_mulai', $izin->tanggal_mulai->format('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_mulai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
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
                        value="{{ old('tanggal_selesai', $izin->tanggal_selesai->format('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('tanggal_selesai') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                    >
                    @error('tanggal_selesai')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Alasan Pengajuan -->
            <div>
                <label for="alasan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alasan / Keterangan Izin <span class="text-rose-500">*</span>
                </label>
                <textarea
                    name="alasan"
                    id="alasan"
                    rows="4"
                    required
                    class="w-full px-4 py-3 rounded-xl border {{ $errors->has('alasan') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} text-sm font-normal text-slate-800 leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                >{{ old('alasan', $izin->alasan) }}</textarea>
                @error('alasan')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload File Bukti -->
            <div>
                <label for="bukti_file" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Lampiran Bukti (Kosongkan jika tidak ingin mengubah)
                </label>
                @if($izin->bukti_file_url)
                    <div class="mb-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">Berkas saat ini terlampir:</span>
                        <a href="{{ $izin->bukti_file_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold flex items-center gap-1">
                            <span>Lihat Berkas Lama</span> &rarr;
                        </a>
                    </div>
                @endif
                <input
                    type="file"
                    name="bukti_file"
                    id="bukti_file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                >
                <p class="text-[11px] text-slate-400 mt-1">Format yang didukung: JPG, PNG, atau PDF. Maksimal 2MB.</p>
                @error('bukti_file')
                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('izin.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
