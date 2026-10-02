@extends('layouts.auth')

@section('title', 'SEIRAMA - Masuk ke Sistem')

@section('content')
<div class="relative w-full max-w-full lg:max-w-none mx-auto flex-1 flex flex-col justify-between lg:justify-center min-h-[100dvh] lg:min-h-0">
    <!-- Ambient Ellipse Glow Assets (Positioned per PRD specification) -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden lg:overflow-visible -z-10" aria-hidden="true">
        <!-- Ellipse 14: Dibelakang ilustrasi laki-laki (Upper Left) -->
        <img
            src="{{ asset('storage/images/Ellipse 14.png') }}"
            alt=""
            class="absolute top-8 -left-10 w-64 sm:w-72 lg:-left-20 lg:top-8 lg:w-96 xl:w-[28rem] pointer-events-none select-none opacity-85 lg:opacity-75 blur-xs"
        >
        <!-- Ellipse 12: Pojok kanan atas (Top Right) -->
        <img
            src="{{ asset('storage/images/Ellipse 12.png') }}"
            alt=""
            class="absolute top-10 -right-8 w-60 sm:w-72 lg:right-10 lg:-top-10 lg:w-80 xl:w-96 pointer-events-none select-none opacity-80 lg:opacity-70 blur-xs"
        >
        <!-- Ellipse 13: Pojok kiri bawah (Bottom Left) -->
        <img
            src="{{ asset('storage/images/Ellipse 13.png') }}"
            alt=""
            class="absolute -bottom-10 -left-12 w-72 sm:w-84 lg:-left-24 lg:bottom-0 lg:w-[28rem] xl:w-[32rem] pointer-events-none select-none opacity-85 lg:opacity-85 blur-xs"
        >
        <!-- Ellipse 11: Pojok kanan bawah (Bottom Right) -->
        <img
            src="{{ asset('storage/images/Ellipse 11.png') }}"
            alt=""
            class="absolute -bottom-8 -right-8 w-68 sm:w-76 lg:-right-20 lg:bottom-0 lg:w-80 xl:w-96 pointer-events-none select-none opacity-85 lg:opacity-80 blur-xs"
        >
    </div>

    <!-- Main Responsive Container: 2 Columns on Desktop (lg+), Bottom Sheet Full-Width Card on Mobile (Dashboard-v2) -->
    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-0 lg:gap-12 xl:gap-16 lg:items-center w-full min-h-[100dvh] lg:min-h-0 justify-between lg:justify-center flex-1">

        <!-- ============================================== -->
        <!-- DESKTOP LEFT COLUMN: Hero Branding & 3D Artwork -->
        <!-- ============================================== -->
        <div class="hidden lg:flex lg:col-span-7 flex-col items-start justify-center space-y-6 xl:space-y-8 pr-0 lg:pr-4">
            <!-- Portal Chip Badge -->
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white/[0.08] border border-white/15 backdrop-blur-md text-xs font-semibold text-white/90 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-[#DD00FF] animate-pulse"></span>
                <span>SEIRAMA (Sistem Informasi Kehadiran dan Aktivitas Magang)</span>
            </div>

            <!-- Hero Headline -->
            <div class="space-y-3">
                <h1 class="text-4xl xl:text-5xl font-black text-white tracking-tight leading-[1.18]">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DD00FF] via-fuchsia-400 to-pink-300">S</span>ist<span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DD00FF] via-fuchsia-400 to-pink-300">e</span>m Informasi Kehad<span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DD00FF] via-fuchsia-400 to-pink-300">ir</span>an
                    dan <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DD00FF] via-fuchsia-400 to-pink-300">A</span>ktivitas 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#DD00FF] via-fuchsia-400 to-pink-300">Ma</span>gang.
                </h1>
                <p class="text-white/75 text-sm xl:text-base max-w-xl leading-relaxed font-normal">
                    Presensi, Aktivitas & Penilaian Magang dalam Satu Sistem.
                </p>
            </div>

            <!-- Feature Pills -->
            <div class="flex flex-wrap gap-3 pt-1">
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white/[0.06] border border-white/10 text-xs font-medium text-white/85 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-[#DD00FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <span>Geolokasi</span>
                </div>
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white/[0.06] border border-white/10 text-xs font-medium text-white/85 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-[#DD00FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span>Verifikasi Wajah</span>
                </div>
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white/[0.06] border border-white/10 text-xs font-medium text-white/85 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-[#DD00FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Validasi Proyek & Tugas</span>
                </div>
            </div>

            <!-- Big Desktop 3D Illustration -->
            <div class="relative w-full max-w-[500px] xl:max-w-[560px] pt-3">
                <img
                    src="{{ asset('storage/images/ilustrasi.png') }}"
                    alt="Ilustrasi Presensi Digital SIKAP"
                    class="w-full h-auto object-contain drop-shadow-[0_20px_45px_rgba(0,0,0,0.55)] select-none pointer-events-none hover:scale-[1.02] transition-transform duration-300"
                >
            </div>
        </div>

        <!-- ============================================== -->
        <!-- RIGHT COLUMN / MOBILE BOTTOM SHEET: SIKAP Glassmorphic Card -->
        <!-- ============================================== -->
        <div class="lg:col-span-5 w-full max-w-full lg:max-w-[420px] mx-auto flex-1 lg:flex-initial flex flex-col justify-between lg:justify-center min-h-[100dvh] lg:min-h-0">

            <!-- Mobile Top 3D Illustration (Hidden on Desktop) -->
            <!-- Positioned BEHIND the card layer (z-10) with card overlapping it -->
            <div class="w-full flex-shrink-0 flex items-end justify-center pt-5 sm:pt-7 pb-0 -mb-8 sm:-mb-10 px-2 relative z-10 lg:hidden">
                <img
                    src="{{ asset('storage/images/ilustrasi.png') }}"
                    alt="Ilustrasi Presensi Digital SIKAP"
                    class="w-full max-w-[395px] sm:max-w-[430px] h-auto object-contain select-none pointer-events-none drop-shadow-2xl"
                >
            </div>

            <!-- Glassmorphic Login Card (Positioned IN FRONT of illustration layer z-20) -->
            <div class="relative z-20 glass-sikap rounded-t-[36px] sm:rounded-t-[40px] rounded-b-none lg:rounded-[36px] border-t border-x lg:border border-white/20 border-b-0 lg:border-b px-6 sm:px-8 pt-10 sm:pt-11 pb-8 sm:pb-10 lg:pt-9 lg:pb-9 shadow-2xl w-full flex-1 lg:flex-initial flex flex-col justify-between lg:block">

                <!-- Top Section of Card: Branding, Welcome, Flash, Form -->
                <div class="w-full">
                    <!-- SIKAP Branding Title -->
                    <div class="text-center">
                    <span class="sr-only">Sistem Informasi Kehadiran dan Aktivitas Magang</span>
                    <h1 class="text-3xl sm:text-[34px] font-black tracking-wider text-white uppercase leading-none">
                        SEIRAMA
                    </h1>
                    <p class="text-[11px] sm:text-xs text-white/80 font-normal mt-2 leading-tight">
                        (Sistem Informasi Kehadiran dan Aktivitas Magang)
                    </p>
                </div>

                <!-- Welcome Subheading -->
                <div class="text-center mt-7">
                    <h2 class="text-2xl sm:text-[26px] font-extrabold tracking-wide text-white uppercase leading-tight">
                        WELCOME BACK
                    </h2>
                    <p class="text-xs sm:text-[13px] text-white/70 font-normal mt-1">
                        Manage your daily task
                    </p>
                </div>

                <!-- Flash Status Message (e.g. Logout) -->
                @if (session('status'))
                    <div class="mt-4 p-3 rounded-2xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 text-xs font-medium flex items-center gap-2 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Validation Error Message -->
                @if (isset($errors) && $errors->any())
                    <div class="mt-4 p-3 rounded-2xl bg-rose-500/20 border border-rose-400/40 text-rose-200 text-xs font-medium flex items-center gap-2 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login.process') }}" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-[11px] sm:text-xs font-bold tracking-wider text-white uppercase mb-1.5 pl-1">
                            USERNAME
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
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
                                placeholder="Masukkan Username"
                                class="input-sikap w-full pl-11 pr-4 py-3.5 rounded-2xl text-xs sm:text-sm font-medium placeholder-white/40 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-[11px] sm:text-xs font-bold tracking-wider text-white uppercase mb-1.5 pl-1">
                            PASSWORD
                        </label>
                        <div class="relative flex items-center password-field-container">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan Password"
                                class="input-sikap password-input w-full pl-11 pr-11 py-3.5 rounded-2xl text-xs sm:text-sm font-medium placeholder-white/40 focus:outline-none"
                            >
                            <button
                                type="button"
                                id="toggle-password"
                                tabindex="-1"
                                class="toggle-password-btn absolute inset-y-0 right-0 pr-3.5 flex items-center text-white/50 hover:text-white transition focus:outline-none cursor-pointer"
                                title="Tampilkan / Sembunyikan Password"
                            >
                                <svg id="eye-icon" class="eye-icon w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg id="eye-off-icon" class="eye-off-icon w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="btn-sikap w-full py-3.5 px-6 rounded-2xl text-white font-bold text-sm sm:text-base cursor-pointer flex items-center justify-center tracking-wide"
                        >
                            Masuk ke sistem
                        </button>
                    </div>
                </form>
                </div>

                <!-- Card Footer (Stays pinned inside bottom of card) -->
                <div class="pt-6 pb-2 lg:pb-0 text-center mt-auto lg:mt-0">
                    <p class="text-[11px] sm:text-xs text-white/70 leading-relaxed font-normal">
                        Kendala akun atau lupa password? Hubungi<br>
                        <span class="font-bold text-white">Administrator Instansi.</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const container = btn.closest('.password-field-container') || btn.parentElement;
                const passwordInput = container.querySelector('.password-input') || document.getElementById('password');
                const eyeIcon = container.querySelector('.eye-icon') || document.getElementById('eye-icon');
                const eyeOffIcon = container.querySelector('.eye-off-icon') || document.getElementById('eye-off-icon');

                if (passwordInput) {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    if (eyeIcon) eyeIcon.classList.toggle('hidden', isPassword);
                    if (eyeOffIcon) eyeOffIcon.classList.toggle('hidden', !isPassword);
                }
            });
        });
    });
</script>
@endpush
