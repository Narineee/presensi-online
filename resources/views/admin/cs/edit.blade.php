@extends('layouts.admin')

@section('title', 'Edit CS')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Customer Service</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui profil CS, validator pembimbing, dan pengaturan akun login.</p>
        </div>
        <a href="{{ route('admin.cs.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Edit CS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.cs.update', $cs->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Profil CS -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Profil Customer Service</span>
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="nama_lengkap" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_lengkap"
                            id="nama_lengkap"
                            value="{{ old('nama_lengkap', $cs->nama_lengkap) }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nama_lengkap') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('nama_lengkap')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            NIK (Nomor Induk Karyawan) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik', $cs->nik) }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('nik') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('nik')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jabatan" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jabatan (Opsional)
                        </label>
                        <input
                            type="text"
                            name="jabatan"
                            id="jabatan"
                            value="{{ old('jabatan', $cs->jabatan) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            No. WhatsApp / HP (Opsional)
                        </label>
                        <input
                            type="text"
                            name="no_hp"
                            id="no_hp"
                            value="{{ old('no_hp', $cs->no_hp) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>

                    <div>
                        <label for="tanggal_bergabung" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Bergabung (Opsional)
                        </label>
                        <input
                            type="date"
                            name="tanggal_bergabung"
                            id="tanggal_bergabung"
                            value="{{ old('tanggal_bergabung', $cs->tanggal_bergabung ? $cs->tanggal_bergabung->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Status CS <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="status"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                        >
                            <option value="aktif" {{ old('status', $cs->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="non_aktif" {{ old('status', $cs->status) === 'non_aktif' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Validator Pembimbing -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">2</span>
                    <span>Pembimbing / Validator CS</span>
                </h3>
                <div class="mt-4">
                    <label for="pembimbing_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Pilih Pembimbing / Validator <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="pembimbing_id"
                        id="pembimbing_id"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('pembimbing_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium bg-white"
                    >
                        <option value="">-- Pilih Pembimbing / Validator --</option>
                        @foreach ($pembimbing as $p)
                            <option value="{{ $p->id }}" {{ old('pembimbing_id', $cs->pembimbing_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_lengkap }} (NIP: {{ $p->nip }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Pembimbing yang dipilih akan memvalidasi presensi dan aktivitas kerja harian CS ini.</p>
                    @error('pembimbing_id')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 3: Akun Login CS -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Akun Login Customer Service</span>
                </h3>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Username Login <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username', $cs->pengguna->username ?? '') }}"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('username') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        @error('username')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Password Baru (Opsional)
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Biarkan kosong jika tidak ganti"
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-800 font-medium"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengganti password akun.</p>
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
                                {{ old('is_active', $cs->pengguna->is_active ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500"
                            >
                            <span class="text-sm font-semibold text-slate-700">Akun Aktif (Dapat Login ke Aplikasi)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.cs.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-semibold transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Perbarui Data CS
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
