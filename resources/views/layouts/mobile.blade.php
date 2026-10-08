<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F8FAFC">
    <title>@yield('title', 'Presensi') - Sistem Informasi Manajemen Magang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#0F1850',
                            dark: '#0A1038',
                            blue: '#1E3A8A',
                            light: '#2563EB',
                            soft: '#EEF2FF',
                            ink: '#0F172A',
                            amber: '#F59E0B',
                            yellow: '#FBBF24',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak]{display:none!important}
        html, body {
            overflow-x: clip;
            min-height: 100%;
        }

        /* Fixed / Sticky Top Navigation Bar (Stays pinned when scrolling) */
        .sticky-top-nav {
            position: -webkit-sticky !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 30 !important;
            padding-top: max(0.875rem, env(safe-area-inset-top));
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 0 4px 16px -2px rgba(15, 24, 80, 0.05);
        }

        /* Light Glassmorphism Utilities */
        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 25px -5px rgba(15, 24, 80, 0.04), 0 8px 10px -6px rgba(15, 24, 80, 0.02);
        }
        .glass-subcard {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 12px -2px rgba(15, 24, 80, 0.03);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-color: rgba(226, 232, 240, 0.85);
        }
        .btn-brand-primary {
            background-color: #0F1850;
            color: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px -2px rgba(15, 24, 80, 0.25);
        }
        .btn-brand-primary:hover {
            background-color: #1A2875;
            box-shadow: 0 6px 18px -2px rgba(15, 24, 80, 0.35);
        }
        .btn-brand-primary:active {
            transform: scale(0.99);
        }
    </style>
    @stack('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100/80 font-sans text-brand-ink antialiased min-h-dvh selection:bg-[#0F1850] selection:text-white">

{{-- Ambient Pastel Ellipses for Glassmorphic Depth (Simple, Light & Clean) --}}
<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10" aria-hidden="true">
    <div class="absolute -top-16 -left-16 w-80 h-80 rounded-full bg-blue-300/20 blur-3xl"></div>
    <div class="absolute top-1/3 -right-20 w-80 h-80 rounded-full bg-indigo-200/20 blur-3xl"></div>
    <div class="absolute bottom-28 -left-20 w-72 h-72 rounded-full bg-amber-200/20 blur-3xl"></div>
    <div class="absolute -bottom-16 -right-16 w-80 h-80 rounded-full bg-sky-200/20 blur-3xl"></div>
</div>

<div class="relative mx-auto min-h-dvh w-full max-w-md bg-[#F8FAFC]/90 shadow-xl lg:max-w-none lg:shadow-none @if (! View::hasSection('hideNav')) lg:pl-64 @endif">
    <main class="mx-auto w-full max-w-md px-4 pt-0 pb-28 lg:max-w-2xl lg:pt-8 lg:pb-12">
        @yield('content')
    </main>

    @if (! View::hasSection('hideNav'))
        @php
            $nav = [
                ['Presensi',  route('presensi.index'),  'presensi',  'M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Aktivitas', route('aktivitas.create'), 'aktivitas', 'M9 12.75L11.25 15 15 9.75M10.5 3.75h3a1.5 1.5 0 011.5 1.5v.75h1.5a2.25 2.25 0 012.25 2.25v11.25a2.25 2.25 0 01-2.25 2.25H7.5a2.25 2.25 0 01-2.25-2.25V8.25A2.25 2.25 0 017.5 6H9v-.75a1.5 1.5 0 011.5-1.5z'],
                ['Pengajuan', route('izin.create'),      'izin',      'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6-9.75-6'],
                ['Riwayat',   route('magang.rekap'),     'rekap',     'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z'],
                ['Profil',    route('profil.edit'),      'profil',    'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
            ];

            $isRekapActive = request()->routeIs('magang.rekap', 'presensi.riwayat', 'aktivitas.riwayat', 'aktivitas.index', 'aktivitas.show', 'izin.riwayat', 'izin.index', 'magang.penilaian.*')
                || (request()->is('rekap*', 'presensi/riwayat', 'aktivitas/riwayat', 'izin/riwayat', 'magang/penilaian*'));
        @endphp
        {{-- SIDEBAR (desktop) --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200/80 bg-white/90 backdrop-blur-xl lg:flex">
            <div class="px-6 py-6 border-b border-slate-100">
                <p class="text-lg font-black tracking-tight text-brand">SEIRAMA</p>
                <p class="text-xs text-slate-500 font-medium">Sistem Kehadiran & Aktivitas</p>
            </div>
            <ul class="flex-1 space-y-1.5 px-3 py-4">
                @foreach ($nav as [$label, $url, $key, $icon])
                    @php
                        $on = ($key === 'rekap' && $isRekapActive)
                            || ($key === 'aktivitas' && (request()->routeIs('aktivitas.create', 'aktivitas.edit') || request()->is('aktivitas/create', 'aktivitas/*/edit')))
                            || ($key === 'izin' && (request()->routeIs('izin.create', 'izin.edit') || request()->is('izin/create', 'izin/*/edit')))
                            || ($key === 'presensi' && (request()->routeIs('presensi.*') && ! request()->routeIs('presensi.riwayat')))
                            || ($key === 'profil' && request()->routeIs('profil.*'));
                    @endphp
                    <li>
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-bold transition {{ $on ? 'bg-brand text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                            <span class="truncate">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-slate-200/80 p-4 bg-white/50 backdrop-blur-sm">
                <p class="truncate text-sm font-bold text-slate-900">{{ auth()->user()->magang->nama_lengkap ?? auth()->user()->username }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="text-xs font-bold text-red-600 hover:underline cursor-pointer">Keluar</button>
                </form>
            </div>
        </aside>

        {{-- BOTTOM NAV (mobile) --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md lg:hidden border-t border-slate-200/80 bg-white/90 px-2 pt-2 backdrop-blur-xl shadow-lg" style="padding-bottom:max(.5rem, env(safe-area-inset-bottom))">
            <ul class="grid grid-cols-5">
                @foreach ($nav as [$label, $url, $key, $icon])
                    @php
                        $on = ($key === 'rekap' && $isRekapActive)
                            || ($key === 'aktivitas' && (request()->routeIs('aktivitas.create', 'aktivitas.edit') || request()->is('aktivitas/create', 'aktivitas/*/edit')))
                            || ($key === 'izin' && (request()->routeIs('izin.create', 'izin.edit') || request()->is('izin/create', 'izin/*/edit')))
                            || ($key === 'presensi' && (request()->routeIs('presensi.*') && ! request()->routeIs('presensi.riwayat')))
                            || ($key === 'profil' && request()->routeIs('profil.*'));
                    @endphp
                    <li class="min-w-0">
                        <a href="{{ $url }}" class="group flex flex-col items-center gap-1 py-1 px-0.5 transition {{ $on ? 'text-brand' : 'text-slate-500 hover:text-slate-700' }}">
                            <span class="flex h-8 w-12 items-center justify-center rounded-lg transition-all duration-200 {{ $on ? 'bg-brand text-white shadow-sm' : 'text-slate-500 group-hover:text-slate-700 group-hover:bg-slate-100/60' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                            </span>
                            <span class="truncate w-full text-center text-[10px] sm:text-[11px] leading-tight {{ $on ? 'font-bold text-brand' : 'font-medium text-slate-500' }}">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</div>
@stack('scripts')
</body>
</html>