<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensi Digital') - Sistem Presensi &amp; Aktivitas</title>

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
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50 flex">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div
        id="user-sidebar-backdrop"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 hidden md:hidden"
        onclick="toggleUserSidebar()"
        aria-hidden="true"
    ></div>

    <!-- Responsive Sidebar (Sesuai PRD tampilan.md) -->
    <aside
        id="user-sidebar"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col shrink-0 transform -translate-x-full md:translate-x-0 md:static md:w-64 transition-transform duration-300 ease-in-out shadow-xl md:shadow-none"
    >
        <!-- Logo / Brand Header -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-slate-100 bg-white">
            <a href="{{ route('presensi.index') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    PD
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 leading-tight">Presensi Digital</h2>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full mt-0.5 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Peserta Magang
                    </span>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button
                type="button"
                onclick="toggleUserSidebar()"
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

            <!-- 1. Presensi & Monitoring Harian -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Presensi &amp; Aktivitas
                </div>
                <nav class="space-y-1">
                    <!-- Dashboard & Presensi -->
                    <a
                        href="{{ route('presensi.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ (request()->routeIs('presensi.index') || request()->is('magang/dashboard')) ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ (request()->routeIs('presensi.index') || request()->is('magang/dashboard')) ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Presensi Harian</span>
                        </span>
                        @if(request()->routeIs('presensi.index'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Jurnal / Aktivitas Harian -->
                    <a
                        href="{{ route('aktivitas.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ (request()->routeIs('aktivitas.*') && !request()->routeIs('aktivitas.cetak')) ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ (request()->routeIs('aktivitas.*') && !request()->routeIs('aktivitas.cetak')) ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Jurnal Aktivitas</span>
                        </span>
                        @if(request()->routeIs('aktivitas.*') && !request()->routeIs('aktivitas.cetak'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>

                    <!-- Pengajuan Izin & Sakit -->
                    <a
                        href="{{ route('izin.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('izin.*') ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('izin.*') ? 'text-white' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <span>Permohonan Ketidakhadiran</span>
                        </span>
                        @if(request()->routeIs('izin.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>
                </nav>
            </div>

            <!-- 2. Dokumen & Cetak Rekap (Sesuai PRD tampilan.md) -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Dokumen &amp; Rekapitulasi
                </div>
                <nav class="space-y-1">
                    <a
                        href="{{ route('presensi.cetak') }}"
                        target="_blank"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('presensi.cetak') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('presensi.cetak') ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                            </svg>
                            <span>Cetak Rekap Presensi</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('presensi.cetak') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">PDF</span>
                    </a>

                    <a
                        href="{{ route('aktivitas.cetak') }}"
                        target="_blank"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('aktivitas.cetak') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('aktivitas.cetak') ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                            </svg>
                            <span>Cetak Rekap Aktivitas</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('aktivitas.cetak') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">PDF</span>
                    </a>
                </nav>
            </div>

            <!-- 3. Nilai Akhir (Khusus Magang Sesuai PRD tampilan.md) -->
            @if(Auth::user()->role === 'magang')
                <div>
                    <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                        Evaluasi Kelulusan
                    </div>
                    <nav class="space-y-1">
                        <a
                            href="{{ route('magang.penilaian.index') }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('magang.penilaian.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                        >
                            <span class="flex items-center gap-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('magang.penilaian.*') ? 'text-white' : 'text-amber-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                                <span>Lembar Nilai Akhir</span>
                            </span>
                            @if(request()->routeIs('magang.penilaian.*'))
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </a>
                    </nav>
                </div>
            @endif

            <!-- 4. Pengaturan Akun & Profil (Sesuai PRD tampilan.md "edit profile") -->
            <div>
                <div class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Akun Pengguna
                </div>
                <nav class="space-y-1">
                    <a
                        href="{{ route('profil.edit') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('profil.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }} transition"
                    >
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('profil.*') ? 'text-white' : 'text-blue-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span>Edit Profil Saya</span>
                        </span>
                        @if(request()->routeIs('profil.*'))
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        @endif
                    </a>
                </nav>
            </div>

        </div>

        <!-- Sidebar Bottom: Info Profil Peserta -->
        <div class="p-3.5 border-t border-slate-200 bg-slate-50/80">
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 p-1 rounded-xl hover:bg-white transition group" title="Klik untuk edit profil">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs shrink-0 overflow-hidden group-hover:ring-2 group-hover:ring-blue-400">
                    @if(Auth::user()->magang && Auth::user()->magang->foto)
                        <img src="{{ asset('storage/' . Auth::user()->magang->foto) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate group-hover:text-blue-600 transition">
                        {{ Auth::user()->magang->nama_lengkap ?? Auth::user()->username }}
                    </p>
                    <p class="text-[10px] text-slate-500 font-medium truncate flex items-center gap-1">
                        <span>Edit Profil</span>
                        <span class="text-[9px] text-blue-500">&rarr;</span>
                    </p>
                </div>
            </a>
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
                    onclick="toggleUserSidebar()"
                    class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition cursor-pointer"
                    aria-label="Buka Menu Sidebar"
                    title="Menu Navigasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Role Badge & Portal Info -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-800 hidden sm:inline">Portal Presensi &amp; Aktivitas</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Peserta Magang
                    </span>
                </div>
            </div>

            <!-- Header Right: User Info & Logout -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">
                        {{ Auth::user()->magang->nama_lengkap ?? Auth::user()->username }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium">
                        {{ Auth::user()->magang->divisi->nama_divisi ?? 'Peserta Magang' }}
                    </div>
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

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        function toggleUserSidebar() {
            const sidebar = document.getElementById('user-sidebar');
            const backdrop = document.getElementById('user-sidebar-backdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
