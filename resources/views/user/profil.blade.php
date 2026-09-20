@extends('layouts.user')

@section('title', 'Profil Akun')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Flash Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <div>
                <span class="font-bold">Gagal memperbarui profil:</span>
                <ul class="list-disc list-inside mt-1 font-normal">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Alert Profil Belum Lengkap (Sesuai PRD tampilan.md: kalau masih kosong diwajibkan isi terlebih dahulu) -->
    @if($isProfileIncomplete)
        <div class="p-5 rounded-3xl bg-amber-50 border-2 border-amber-200 text-amber-900 shadow-xs flex items-start gap-4">
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-lg">
                ⚠️
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-950">Lengkapi Profil Anda Terlebih Dahulu</h3>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    Sesuai ketentuan, peserta magang wajib melengkapi asal sekolah/kampus, jurusan, nomor kontak WhatsApp, dan pasfoto resmi sebelum masa praktik kerja selesai. Data ini akan otomatis tercantum pada lembar penilaian akhir dan rekapan presensi resmi.
                </p>
            </div>
        </div>
    @endif

    <!-- Main Profile Card Form -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-blue-50/40 via-white to-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm">
                    👤
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight">Kelola Profil Akun</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui biodata diri, kontak aktif, dan pasfoto peserta</p>
                </div>
            </div>
        </div>

        <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Foto Profil Section -->
            @if($user->role === 'magang')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Pasfoto Resmi Peserta
                    </label>
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <div class="relative shrink-0">
                            @if($magang && $magang->foto)
                                <img id="preview-foto" src="{{ asset('storage/' . $magang->foto) }}" alt="{{ $magang->nama_lengkap }}" class="w-24 h-24 rounded-2xl object-cover border-2 border-blue-200 shadow-sm">
                            @else
                                <div id="preview-foto-placeholder" class="w-24 h-24 rounded-2xl bg-blue-100 text-blue-700 border-2 border-blue-200 flex items-center justify-center font-extrabold text-2xl shadow-sm">
                                    {{ strtoupper(substr($magang->nama_lengkap ?? $user->username, 0, 2)) }}
                                </div>
                                <img id="preview-foto" src="" alt="Preview" class="w-24 h-24 rounded-2xl object-cover border-2 border-blue-200 shadow-sm hidden">
                            @endif
                        </div>
                        <div class="flex-1 text-center sm:text-left space-y-2">
                            <input
                                type="file"
                                name="foto"
                                id="input-foto"
                                accept="image/jpeg,image/png,image/jpg"
                                class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                                onchange="previewImage(this)"
                            >
                            <p class="text-[11px] text-slate-400">
                                Format JPG atau PNG, ukuran maksimal 2MB. Disarankan pasfoto formal setengah badan dengan latar rapi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        value="{{ old('nama_lengkap', $magang->nama_lengkap ?? $cs->nama_lengkap ?? '') }}"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    >
                </div>

                <!-- Nomor Induk (Readonly, dibuatkan Admin) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        {{ $user->role === 'magang' ? 'Nomor Induk (NIM / NISN)' : 'Nomor Induk Kependudukan (NIK)' }}
                        <span class="text-[10px] font-normal text-slate-400">(Admin Only)</span>
                    </label>
                    <input
                        type="text"
                        value="{{ $magang->no_induk ?? $cs->nik ?? '-' }}"
                        disabled
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-semibold text-slate-600 cursor-not-allowed"
                    >
                </div>

                <!-- Username Akun (Readonly) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username Login
                        <span class="text-[10px] font-normal text-slate-400">(Admin Only)</span>
                    </label>
                    <input
                        type="text"
                        value="{{ $user->username }}"
                        disabled
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-semibold text-slate-600 cursor-not-allowed"
                    >
                </div>

                @if($user->role === 'magang')
                    <!-- Instansi Pendidikan (Asal Kampus / Sekolah) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Asal Kampus / Sekolah <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="instansi_pendidikan"
                            value="{{ old('instansi_pendidikan', $magang->instansi_pendidikan ?? '') }}"
                            required
                            placeholder="Contoh: Universitas Lambung Mangkurat"
                            class="w-full px-3.5 py-2.5 rounded-xl border {{ empty($magang->instansi_pendidikan) ? 'border-amber-300 bg-amber-50/20' : 'border-slate-300' }} text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                        >
                    </div>

                    <!-- Jurusan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Program Studi / Jurusan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="jurusan"
                            value="{{ old('jurusan', $magang->jurusan ?? '') }}"
                            required
                            placeholder="Contoh: S1 Teknologi Informasi"
                            class="w-full px-3.5 py-2.5 rounded-xl border {{ empty($magang->jurusan) ? 'border-amber-300 bg-amber-50/20' : 'border-slate-300' }} text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                        >
                    </div>

                    <!-- Divisi Penempatan (Readonly) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Divisi Penempatan <span class="text-[10px] font-normal text-slate-400">(Plotting Admin)</span>
                        </label>
                        <input
                            type="text"
                            value="{{ $magang->divisi->nama_divisi ?? 'Belum ditentukan admin' }}"
                            disabled
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-600 cursor-not-allowed"
                        >
                    </div>

                    <!-- Pembimbing Lapangan (Readonly) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pembimbing Lapangan <span class="text-[10px] font-normal text-slate-400">(Plotting Admin)</span>
                        </label>
                        <input
                            type="text"
                            value="{{ $magang->pembimbing->nama_lengkap ?? 'Belum ditentukan admin' }}"
                            disabled
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-600 cursor-not-allowed"
                        >
                    </div>
                @else
                    <!-- Jabatan CS (Readonly) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jabatan / Posisi <span class="text-[10px] font-normal text-slate-400">(Admin Only)</span>
                        </label>
                        <input
                            type="text"
                            value="{{ $cs->jabatan ?? 'Customer Service' }}"
                            disabled
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-600 cursor-not-allowed"
                        >
                    </div>
                @endif

                <!-- Nomor HP / WhatsApp Aktif -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor HP / WhatsApp Aktif <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="no_hp"
                        value="{{ old('no_hp', $magang->no_hp ?? $cs->no_hp ?? '') }}"
                        required
                        placeholder="Contoh: 08123456789"
                        class="w-full px-3.5 py-2.5 rounded-xl border {{ (empty($magang->no_hp) && empty($cs->no_hp)) ? 'border-amber-300 bg-amber-50/20' : 'border-slate-300' }} text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    >
                </div>

                <!-- Ganti Password (Opsional) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ganti Kata Sandi
                        <span class="text-[10px] font-normal text-slate-400">(Kosongkan jika tidak diganti)</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 6 karakter baru"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    >
                </div>
            </div>

            <!-- Tombol Simpan Profil -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <a href="{{ route('presensi.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    &larr; Kembali ke Presensi
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer"
                >
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-foto');
            const placeholder = document.getElementById('preview-foto-placeholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
