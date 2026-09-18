<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pembimbing') - Presensi Digital</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Role Badge -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('pembimbing.dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-purple-500/20">
                            P
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 tracking-tight text-base block leading-none">Presensi Digital</span>
                            <span class="text-[10px] text-slate-400 font-medium">Portal Pembimbing & Validator</span>
                        </div>
                    </a>

                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        Pembimbing
                    </span>
                </div>

                <!-- Nav Menu Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('pembimbing.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pembimbing.dashboard') ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                        Dashboard
                    </a>
                    <a href="{{ route('pembimbing.aktivitas.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pembimbing.aktivitas.*') ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                        Validasi Aktivitas
                    </a>
                    <a href="{{ route('pembimbing.izin.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pembimbing.izin.*') ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                        Verifikasi Izin & Sakit
                    </a>
                    <a href="{{ route('pembimbing.penilaian.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pembimbing.penilaian.*') ? 'bg-purple-50 text-purple-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                        Penilaian Magang
                    </a>
                </nav>

                <!-- User Info & Logout Button -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-800">
                            {{ Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->username }}
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium">Pembimbing Lapangan</div>
                    </div>

                    <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs border border-purple-200">
                        {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition cursor-pointer" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/80 bg-white py-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Sistem Presensi & Aktivitas Harian Digital. Seluruh hak cipta dilindungi.
    </footer>

    @yield('scripts')
</body>
</html>
