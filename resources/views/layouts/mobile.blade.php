<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F3F7F5">
    <title>@yield('title', 'Presensi') - Sistem Informasi Manajemen Magang</title>

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
<div class="relative mx-auto min-h-dvh max-w-md bg-[#F3F7F5] shadow-xl lg:max-w-none lg:shadow-none @if (! View::hasSection('hideNav')) lg:pl-64 @endif">
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

    @if (! View::hasSection('hideNav'))
        @php
            $nav = [
                ['Presensi',  route('presensi.index'),  'presensi',  'M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Aktivitas', route('aktivitas.create'), 'aktivitas', 'M9 12.75L11.25 15 15 9.75M10.5 3.75h3a1.5 1.5 0 011.5 1.5v.75h1.5a2.25 2.25 0 012.25 2.25v11.25a2.25 2.25 0 01-2.25 2.25H7.5a2.25 2.25 0 01-2.25-2.25V8.25A2.25 2.25 0 017.5 6H9v-.75a1.5 1.5 0 011.5-1.5z'],
                ['Pengajuan',      route('izin.create'),      'izin',      'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6-9.75-6'],
                ['Riwayat',     route('magang.rekap'),     'rekap',     'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z'],
                ['Profil',    route('profil.edit'),      'profil',    'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
            ];

            $isRekapActive = request()->routeIs('magang.rekap', 'presensi.riwayat', 'aktivitas.riwayat', 'aktivitas.index', 'aktivitas.show', 'izin.riwayat', 'izin.index', 'magang.penilaian.*')
                || (request()->is('rekap*', 'presensi/riwayat', 'aktivitas/riwayat', 'izin/riwayat', 'magang/penilaian*'));
        @endphp
        {{-- SIDEBAR (desktop) --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex">
            <div class="px-6 py-6">
                <p class="text-lg font-extrabold text-brand">Presensi Magang</p>
                <p class="text-xs text-slate-500">Sistem Informasi Manajemen Magang</p>
            </div>
            <ul class="flex-1 space-y-1 px-3">
                @foreach ($nav as [$label, $url, $key, $icon])
                    @php
                        $on = ($key === 'rekap' && $isRekapActive)
                            || ($key === 'aktivitas' && (request()->routeIs('aktivitas.create', 'aktivitas.edit') || request()->is('aktivitas/create', 'aktivitas/*/edit')))
                            || ($key === 'izin' && (request()->routeIs('izin.create', 'izin.edit') || request()->is('izin/create', 'izin/*/edit')))
                            || ($key === 'presensi' && (request()->routeIs('presensi.*') && ! request()->routeIs('presensi.riwayat')))
                            || ($key === 'profil' && request()->routeIs('profil.*'));
                    @endphp
                    <li>
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $on ? 'bg-brand-soft text-brand' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-slate-200 p-4">
                <p class="truncate text-sm font-bold">{{ auth()->user()->magang->nama_lengkap ?? auth()->user()->username }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="text-xs font-bold text-red-600 hover:underline">Keluar</button>
                </form>
            </div>
        </aside>

        <nav class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md lg:hidden border-t border-slate-200 bg-white/95 px-2 pt-2 backdrop-blur" style="padding-bottom:max(.5rem, env(safe-area-inset-bottom))">
            <ul class="grid grid-cols-5">
                @foreach ($nav as [$label, $url, $key, $icon])
                    @php
                        $on = ($key === 'rekap' && $isRekapActive)
                            || ($key === 'aktivitas' && (request()->routeIs('aktivitas.create', 'aktivitas.edit') || request()->is('aktivitas/create', 'aktivitas/*/edit')))
                            || ($key === 'izin' && (request()->routeIs('izin.create', 'izin.edit') || request()->is('izin/create', 'izin/*/edit')))
                            || ($key === 'presensi' && (request()->routeIs('presensi.*') && ! request()->routeIs('presensi.riwayat')))
                            || ($key === 'profil' && request()->routeIs('profil.*'));
                    @endphp
                    <li>
                        <a href="{{ $url }}" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold {{ $on ? 'bg-brand-soft text-brand' : 'text-slate-500' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                            {{ $label }}
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