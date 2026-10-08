<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F3F7F5">
    <title>@yield('title', 'Beranda') - Pembimbing</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
            colors: { brand: { DEFAULT: '#14664B', dark: '#0E4D39', soft: '#E3F0EA', ink: '#12261F' } }
        } } }
    </script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-200 font-sans text-brand-ink antialiased lg:bg-[#F3F7F5]">
@php
    $nav = [
        ['Beranda', route('pembimbing.dashboard'),    'pembimbing.dashboard',  'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['Binaan',  route('pembimbing.binaan.index'), 'pembimbing.binaan*',    'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['Presensi', route('pembimbing.presensi.index'), 'pembimbing.presensi*', 'M9 12.75L11.25 15 15 9.75M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
    ];
    $iconLainnya = 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5';
@endphp

<div x-data="{ more: false }" class="relative mx-auto min-h-dvh max-w-md bg-[#F3F7F5] shadow-xl lg:max-w-none lg:pl-64 lg:shadow-none">
    <main class="mx-auto w-full max-w-md px-4 pt-5 pb-28 lg:max-w-2xl lg:pt-8 lg:pb-12">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        @yield('content')
    </main>

    {{-- SIDEBAR (desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col overflow-y-auto border-r border-slate-200 bg-white lg:flex">
        <div class="px-6 py-6">
            <p class="text-lg font-extrabold text-brand">Panel Pembimbing</p>
            <p class="text-xs text-slate-500">Sistem Informasi Manajemen Magang</p>
        </div>
        <ul class="space-y-1 px-3">
            @foreach ($nav as [$label, $url, $pattern, $icon])
                @php $on = request()->routeIs($pattern); @endphp
                <li>
                    <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $on ? 'bg-brand-soft text-brand' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
        <p class="mt-5 mb-1 px-6 text-[11px] font-bold uppercase tracking-wider text-slate-400">Menu lainnya</p>
        <ul class="flex-1 space-y-1 px-3">
            @include('pembimbing.menu-lainnya', ['variant' => 'side'])
        </ul>
        <div class="border-t border-slate-200 p-4">
            <p class="truncate text-sm font-bold">{{ auth()->user()->pembimbing->nama_lengkap ?? auth()->user()->username }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf
                <button class="text-xs font-bold text-red-600 hover:underline">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- NAVIGASI BAWAH (HP) --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md border-t border-slate-200 bg-white/95 px-2 pt-2 backdrop-blur lg:hidden" style="padding-bottom:max(.5rem, env(safe-area-inset-bottom))">
        <ul class="grid grid-cols-4">
            @foreach ($nav as [$label, $url, $pattern, $icon])
                @php $on = request()->routeIs($pattern); @endphp
                <li>
                    <a href="{{ $url }}" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold {{ $on ? 'bg-brand-soft text-brand' : 'text-slate-500' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                </li>
            @endforeach
            <li>
                <button type="button" @click="more = true" :aria-expanded="more" class="flex w-full flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold text-slate-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconLainnya }}"/></svg>
                    Lainnya
                </button>
            </li>
        </ul>
    </nav>

    {{-- Sheet "Lainnya" (HP) --}}
    <div x-show="more" x-cloak class="fixed inset-0 z-50 flex items-end bg-black/50 lg:hidden" @keydown.escape.window="more = false">
        <div @click.outside="more = false" class="mx-auto max-h-[85dvh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white p-5" style="padding-bottom:max(1.25rem, env(safe-area-inset-bottom))">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-extrabold">Menu lainnya</h2>
                <button type="button" @click="more = false" class="text-2xl leading-none text-slate-400" aria-label="Tutup">×</button>
            </div>
            <ul class="space-y-1">@include('pembimbing.menu-lainnya', ['variant' => 'side'])</ul>
            <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-slate-200 pt-4">@csrf
                <button class="w-full rounded-2xl border border-rose-300 py-3 text-sm font-extrabold text-rose-700">Keluar</button>
            </form>
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>