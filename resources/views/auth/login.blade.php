@extends('layouts.auth')

@section('title', 'Masuk ke Akun')

@section('content')
<div class="glass-panel rounded-3xl overflow-hidden">
    <!-- Header Card -->
    <div class="px-8 pt-8 pb-6 text-center border-b border-white/60 bg-gradient-to-b from-white/40 via-white/20 to-transparent">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25 mb-4 ring-4 ring-white/80">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5Zm0 0v-3.675A55.378 55.378 0 0112 8.443m-5.25 6.557c0 1.035.84 1.875 1.875 1.875h6.75c1.035 0 1.875-.84 1.875-1.875v-3.675a55.38 55.38 0 00-5.25-2.882" />
            </svg>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-700 text-[11px] font-bold mb-2 backdrop-blur-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
            Portal SIM Magang Terpadu
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sistem Informasi Manajemen Magang</h1>
    </div>

    <div class="p-7 sm:p-8 space-y-5">
        <!-- Flash Status Message (e.g. Logout) -->
        @if (session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-800 text-xs font-semibold flex items-start gap-2.5 shadow-xs backdrop-blur-sm">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Validation Error Message -->
        @if ($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-800 text-xs font-semibold flex items-start gap-2.5 shadow-xs backdrop-blur-sm">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Username Field -->
            <div>
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Username Akun
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-800">
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
                        class="glass-input w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('username') ? 'border-rose-400/90 bg-rose-50/40 text-rose-900' : 'border-slate-200/80 text-slate-900' }} focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-600 transition text-xs font-medium placeholder-slate-400 shadow-xs"
                    >
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi (Password)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-800 text">
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
                        class="glass-input w-full pl-10 pr-11 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400/90 bg-rose-50/40 text-rose-900' : 'border-slate-200/80 text-slate-900' }} focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-600 transition text-xs font-medium placeholder-slate-400 shadow-xs"
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
                    class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-bold text-xs bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:via-indigo-700 hover:to-blue-800 active:scale-[0.99] shadow-lg shadow-blue-500/25 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-500/20 cursor-pointer"
                >
                    <span>Masuk ke Sistem</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Card Footer -->
    <div class="px-8 py-4 bg-white/40 border-t border-white/60 backdrop-blur-md text-center">
        <p class="text-xs text-slate-500">
            Kendala akun atau lupa password? Hubungi <span class="font-bold text-slate-700">Administrator Instansi</span>.
        </p>
    </div>
</div>

<div class="mt-6 text-center text-xs text-slate-500 font-medium">
    &copy; {{ date('Y') }} Sistem Informasi Manajemen Magang.
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
