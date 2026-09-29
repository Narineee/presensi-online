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

    <!-- Tailwind CSS CDN -->
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
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50 flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div
        id="sidebar-backdrop"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 hidden md:hidden"
        onclick="toggleAdminSidebar()"
        aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <aside
        id="admin-sidebar"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col shrink-0 transform -translate-x-full md:translate-x-0 md:static md:w-64 transition-transform duration-300 ease-in-out shadow-xl md:shadow-none"
    >
        <!-- Logo / Brand Header -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-slate-100 bg-white">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    PD
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 leading-tight">Presensi Digital</h2>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        Administrator
                    </span>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button
                type="button"
                onclick="toggleAdminSidebar()"
                class="md:hidden p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition"
                aria-label="Tutup Sidebar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation (Urutan Sesuai PRD tampilan.md) -->
        <div class="flex-1 overflow-y-auto px-3.5 py-5 space-y-6 sidebar-scroll">

            <!-- 1. [MENU UTAMA] -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Menu Utama
                </div>
                <nav class="space-y-1">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </nav>
            </div>

            <!-- 2. [DATA MASTER] Sesuai PRD tampilan.md -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Data Master
                </div>
                <nav class="space-y-1">
                    <!-- Kelola Data Pembimbing -->
                    <a
                        href="{{ route('admin.pembimbing.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.pembimbing.*') ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.pembimbing.*') ? 'text-white' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <span>Data Pembimbing</span>
                        </span>
                        @if(request()->routeIs('admin.pembimbing.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Kelola Data Peserta & Plotting (Magang) -->
                    <a
                        href="{{ route('admin.magang.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.magang.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.magang.*') ? 'text-white' : 'text-amber-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                            </svg>
                            <span> Data Peserta &amp; Plotting</span>
                        </span>
                        @if(request()->routeIs('admin.magang.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Kelola Data Divisi -->
                    <a
                        href="{{ route('admin.divisi.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.divisi.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.divisi.*') ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span>Data Divisi</span>
                        </span>
                        @if(request()->routeIs('admin.divisi.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Kelola Data Kriteria Penilaian -->
                    <a
                        href="{{ route('admin.kriteria.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.kriteria.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.kriteria.*') ? 'text-white' : 'text-indigo-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <span>Data Kriteria</span>
                        </span>
                        @if(request()->routeIs('admin.kriteria.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>
                </nav>
            </div>

            <!-- 3. [OPERASIONAL] Sesuai PRD tampilan.md -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Operasional
                </div>
                <nav class="space-y-1">
                    <!-- Monitoring Presensi -->
                    <a
                        href="{{ route('admin.presensi.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.presensi.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.presensi.*') ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Monitoring Presensi</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('admin.presensi.*') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700' }}">
                            Cetak
                        </span>
                    </a>

                    <!-- Monitoring Aktivitas -->
                    <a
                        href="{{ route('admin.aktivitas.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.aktivitas.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.aktivitas.*') ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Monitoring Aktivitas</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('admin.aktivitas.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                            Cetak
                        </span>
                    </a>

                    <!-- Monitoring Izin & Sakit -->
                    <a
                        href="{{ route('admin.izin.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.izin.*') ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.izin.*') ? 'text-white' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <span>Monitoring Izin &amp; Sakit</span>
                        </span>
                        @if(request()->routeIs('admin.izin.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Rekap Penilaian Magang -->
                    <a
                        href="{{ route('admin.penilaian.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.penilaian.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.penilaian.*') ? 'text-white' : 'text-amber-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            <span>Rekap Penilaian Magang</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('admin.penilaian.*') ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700' }}">
                            Cetak
                        </span>
                    </a>
                </nav>
            </div>

            <!-- 4. [SISTEM] Sesuai PRD tampilan.md -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Sistem &amp; Instansi
                </div>
                <nav class="space-y-1">
                    <a
                        href="{{ route('admin.pengaturan.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.pengaturan.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.pengaturan.*') ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan Aplikasi</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('admin.pengaturan.*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                            TTD
                        </span>
                    </a>
                </nav>
            </div>

        </div>

        <!-- Sidebar Bottom: Akun Sedang Login -->
        <div class="p-3.5 border-t border-slate-200 bg-slate-50/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                    {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->username }}</p>
                    <p class="text-[10px] text-slate-500 font-medium truncate capitalize">Super Admin &bull; Aktif</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden min-h-screen">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button (Mobile Sidebar Toggle - Sesuai PRD "ada tombol sidebar") -->
                <button
                    type="button"
                    onclick="toggleAdminSidebar()"
                    class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition cursor-pointer"
                    aria-label="Buka Menu Sidebar"
                    title="Menu Navigasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- Header Right: User Info & Logout -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">{{ Auth::user()->username }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">Administrator</div>
                </div>

                <!-- Logout Form -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        title="Keluar dari sesi"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
            <div class="max-w-6xl mx-auto space-y-6">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts: Mobile Sidebar Toggle -->
    <script>
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
