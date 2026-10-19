<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F8FAFC">
    <title>@yield('title', 'Beranda') - Pembimbing</title>

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
                            DEFAULT: '#0F1850', // Deep Navy
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

@php
    $nav = [
        ['Beranda', route('pembimbing.dashboard'),    'pembimbing.dashboard',  'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['Binaan',  route('pembimbing.binaan.index'), 'pembimbing.binaan*',    'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['Presensi', route('pembimbing.presensi.index'), 'pembimbing.presensi*', 'M9 12.75L11.25 15 15 9.75M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
    ];
    $iconLainnya = 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5';
@endphp

<div x-data="{ more: false }" class="relative mx-auto min-h-dvh w-full max-w-md bg-[#F8FAFC]/90 shadow-xl lg:max-w-none lg:shadow-none lg:pl-64">
    <main class="mx-auto w-full max-w-md px-4 pt-0 pb-28 lg:max-w-2xl lg:pt-8 lg:pb-12">
        @yield('content')
    </main>

    {{-- SIDEBAR (desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200/80 bg-white/90 backdrop-blur-xl lg:flex">
        <div class="px-6 py-6 border-b border-slate-100">
            <p class="text-lg font-black tracking-tight text-brand">SEIRAMA</p>
            <p class="text-xs text-slate-500 font-medium">Panel Pembimbing Lapangan</p>
        </div>
        <ul class="flex-1 space-y-1.5 px-3 py-4 overflow-y-auto">
            @foreach ($nav as [$label, $url, $pattern, $icon])
                @php $on = request()->routeIs($pattern); @endphp
                <li>
                    <a href="{{ $url }}" class="flex items-center gap-3 rounded-2xl px-3.5 py-3 text-sm font-bold transition {{ $on ? 'bg-brand text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        <span class="truncate">{{ $label }}</span>
                    </a>
                </li>
            @endforeach

            <li class="pt-4 pb-1 px-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menu Lainnya</p>
            </li>
            @include('pembimbing.menu-lainnya', ['variant' => 'side'])
        </ul>
        <div class="border-t border-slate-200/80 p-4 bg-white/50 backdrop-blur-sm">
            <div class="flex items-center gap-2.5">
                <span class="relative grid h-8 w-8 shrink-0 place-items-center overflow-hidden rounded-full border border-brand bg-white text-xs font-bold text-brand">
                    {{ strtoupper(substr(auth()->user()->pembimbing->nama_lengkap ?? auth()->user()->username, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-slate-900">{{ auth()->user()->pembimbing->nama_lengkap ?? auth()->user()->username }}</p>
                    <p class="text-[10px] text-slate-500 font-medium">Pembimbing Lapangan</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end items-center">
                @csrf
                <button class="text-xs font-bold text-red-600 hover:underline cursor-pointer">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- NAVIGASI BAWAH (HP) --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md lg:hidden border-t border-slate-200/80 bg-white/90 px-2 pt-2 backdrop-blur-xl shadow-lg" style="padding-bottom:max(.5rem, env(safe-area-inset-bottom))">
        <ul class="grid grid-cols-4">
            @foreach ($nav as [$label, $url, $pattern, $icon])
                @php $on = request()->routeIs($pattern); @endphp
                <li class="min-w-0">
                    <a href="{{ $url }}" class="group flex flex-col items-center gap-1 py-1 px-0.5 transition {{ $on ? 'text-brand' : 'text-slate-500 hover:text-slate-700' }}">
                        <span class="flex h-8 w-12 items-center justify-center rounded-lg transition-all duration-200 {{ $on ? 'bg-brand text-white shadow-sm' : 'text-slate-500 group-hover:text-slate-700 group-hover:bg-slate-100/60' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        </span>
                        <span class="truncate w-full text-center text-[10px] sm:text-[11px] leading-tight {{ $on ? 'font-bold text-brand' : 'font-medium text-slate-500' }}">{{ $label }}</span>
                    </a>
                </li>
            @endforeach
            @php
                $onLainnya = request()->routeIs('pembimbing.rencana.*', 'pembimbing.aktivitas.*', 'pembimbing.izin.*', 'pembimbing.tugas-luar.*', 'pembimbing.penilaian.*');
            @endphp
            <li class="min-w-0">
                <button type="button" @click="more = true" :aria-expanded="more" class="group flex flex-col items-center gap-1 py-1 px-0.5 w-full transition {{ $onLainnya ? 'text-brand' : 'text-slate-500 hover:text-slate-700' }}">
                    <span class="flex h-8 w-12 items-center justify-center rounded-lg transition-all duration-200 {{ $onLainnya ? 'bg-brand text-white shadow-sm' : 'text-slate-500 group-hover:text-slate-700 group-hover:bg-slate-100/60' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconLainnya }}"/></svg>
                    </span>
                    <span class="truncate w-full text-center text-[10px] sm:text-[11px] leading-tight {{ $onLainnya ? 'font-bold text-brand' : 'font-medium text-slate-500' }}">Lainnya</span>
                </button>
            </li>
        </ul>
    </nav>

    {{-- Sheet "Lainnya" (HP) --}}
    <div x-show="more" x-cloak class="fixed inset-0 z-50 flex items-end bg-black/50 lg:hidden" @keydown.escape.window="more = false">
        <div @click.outside="more = false" class="mx-auto max-h-[85dvh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl" style="padding-bottom:max(1.25rem, env(safe-area-inset-bottom))">
            <div class="mb-4 flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Menu Lainnya</h2>
                    <p class="text-xs text-slate-500">Fitur bimbingan & evaluasi</p>
                </div>
                <button type="button" @click="more = false" class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200" aria-label="Tutup">×</button>
            </div>
            <ul class="space-y-1.5">@include('pembimbing.menu-lainnya', ['variant' => 'side'])</ul>
            <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-slate-200 pt-4">@csrf
                <button class="w-full rounded-2xl border border-rose-300 py-3 text-sm font-extrabold text-rose-700 hover:bg-rose-50 transition">Keluar</button>
            </form>
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>