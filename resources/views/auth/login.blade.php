@extends('layouts.auth')

@section('title', 'Masuk ke Akun')

@section('content')
<div class="bg-white rounded-3xl shadow-xl shadow-slate-200/70 border border-slate-200/90 overflow-hidden">
    <!-- Header Card -->
    <div class="px-8 pt-8 pb-6 text-center border-b border-slate-100 bg-gradient-to-b from-blue-50/50 via-white to-white">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/25 mb-4 ring-4 ring-blue-100">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-blue-100/80 text-blue-700 text-[11px] font-bold mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
            Portal Presensi &amp; Aktivitas Digital
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Presensi Digital</h1>
        <p class="text-xs text-slate-500 mt-1">Sistem Terpadu Magang, Customer Service, Pembimbing &amp; Admin</p>
    </div>

    <div class="p-7 sm:p-8 space-y-5">
        <!-- Flash Status Message (e.g. Logout) -->
        @if (session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Validation Error Message -->
        @if ($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Informasi Akun Resmi (Sesuai PRD: Seluruh Tampilan Sama & Akun Dibuatkan oleh Admin) -->
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 flex items-start gap-3">
            <div class="w-7 h-7 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">
                ℹ️
            </div>
            <div class="text-[11px] leading-relaxed text-slate-600">
                <span class="font-bold text-slate-900">Ketentuan Akses Pengguna:</span><br>
                Seluruh akun login (Magang, CS, Pembimbing, dan Admin) dibuatkan dan diterbitkan langsung oleh <strong class="text-slate-800">Administrator Sistem</strong>. Peserta tidak dapat mendaftar mandiri.
            </div>
        </div>

        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Username Field -->
            <div>
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Username Akun
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Ketik username akun Anda"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('username') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-xs font-medium text-slate-900 placeholder-slate-400"
                    >
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi (Password)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-11 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-xs font-medium text-slate-900 placeholder-slate-400"
                    >
                    <button
                        type="button"
                        id="toggle-password"
                        tabindex="-1"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition focus:outline-none cursor-pointer"
                        title="Tampilkan / Sembunyikan Password"
                    >
                        <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg id="eye-off-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-bold text-xs bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-md shadow-blue-500/20 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer"
                >
                    <span>Masuk ke Sistem</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Default Admin Credentials Dropdown / Accordion -->
        <div class="pt-2 border-t border-slate-100">
            <details class="text-xs group">
                <summary class="flex items-center justify-between text-[11px] font-semibold text-slate-500 hover:text-slate-800 cursor-pointer select-none">
                    <span>Bantuan Akun Default Super Admin</span>
                    <span class="group-open:rotate-180 transition-transform">▾</span>
                </summary>
                <div class="mt-2.5 p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-[11px] text-blue-900 leading-relaxed font-mono">
                    Username: <strong class="text-blue-800">admin</strong> &bull; Password: <strong class="text-blue-800">admin123</strong>
                </div>
            </details>
        </div>
    </div>

    <!-- Card Footer -->
    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-500">
            Kendala akun atau lupa password? Hubungi <span class="font-bold text-slate-700">Administrator Instansi</span>.
        </p>
    </div>
</div>

<div class="mt-6 text-center text-xs text-slate-400">
    &copy; {{ date('Y') }} Presensi Digital. Sistem Presensi Terpadu.
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.classList.toggle('hidden', isPassword);
                eyeOffIcon.classList.toggle('hidden', !isPassword);
            });
        }
    });
</script>
@endpush
