<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Presensi Digital</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS Play CDN -->
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
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col shrink-0 hidden md:flex min-h-screen">
        <!-- Logo / Brand -->
        <div class="h-16 px-6 flex items-center gap-3 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-blue-500/20">
                P
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-tight">Presensi Digital</h2>
                <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Super Admin</span>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
            <!-- Section: Menu Utama -->
            <div>
                <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Menu Utama
                </div>
                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.dashboard') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </nav>
            </div>

            <!-- Section: Operasional -->
            <div>
                <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Operasional
                </div>
                <nav class="space-y-1">
                    <a href="{{ route('admin.presensi.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.presensi.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.presensi.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Monitoring Presensi</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.presensi.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.aktivitas.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.aktivitas.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.aktivitas.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Monitoring Aktivitas</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.aktivitas.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.izin.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.izin.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.izin.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <span>Monitoring Izin & Sakit</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.izin.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.penilaian.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.penilaian.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.penilaian.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            <span>Rekap Penilaian Magang</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.penilaian.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>
                </nav>
            </div>

            <!-- Section: Master Data -->
            <div>
                <div class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                    <span>Master Data</span>
                </div>
                <nav class="space-y-1">
                    <a href="{{ route('admin.divisi.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.divisi.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.divisi.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span>Divisi / Sub-Bagian</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.divisi.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.pembimbing.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pembimbing.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.pembimbing.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <span>Data Pembimbing</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.pembimbing.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.magang.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.magang.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.magang.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                            </svg>
                            <span>Data Magang</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.magang.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.cs.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cs.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.cs.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <span>Data CS</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.cs.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.shift.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.shift.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.shift.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Master Shift CS</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.shift.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.jadwal-shift.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.jadwal-shift.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.jadwal-shift.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                            </svg>
                            <span>Jadwal Shift CS</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.jadwal-shift.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>

                    <a href="{{ route('admin.kriteria.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.kriteria.*') ? 'font-semibold bg-blue-50 text-blue-700' : 'font-medium text-slate-600 hover:bg-slate-50' }} transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.kriteria.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <span>Kriteria Penilaian</span>
                        </span>
                        <span class="text-[10px] {{ request()->routeIs('admin.kriteria.*') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-semibold">Aktif</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar Bottom (Logged User Info) -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm">
                    {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->username }}</p>
                    <p class="text-[11px] text-slate-500 truncate capitalize">{{ Auth::user()->role }} (Super Admin)</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden min-h-screen">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Berjalan
                </span>
                <span class="text-xs text-slate-400 hidden sm:inline">&bull; Database Connected</span>
            </div>

            <!-- Header Right Action -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">{{ Auth::user()->username }}</div>
                    <div class="text-[11px] text-slate-400 font-medium">Akun Utama Sistem</div>
                </div>

                <!-- Logout Form -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        title="Keluar dari sistem"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-rose-200 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-semibold transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
