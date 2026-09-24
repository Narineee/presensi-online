<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk ke Sistem') - Sistem Informasi Manajemen Magang</title>

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
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 40px -15px rgba(30, 58, 138, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(203, 213, 225, 0.7);
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #2563eb;
        }
    </style>
</head>
<body class="min-h-full antialiased text-slate-800 bg-gradient-to-br from-slate-100 via-blue-50/70 to-indigo-100/60 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-x-hidden selection:bg-blue-600 selection:text-white">
    <!-- Ambient Glassmorphism Background Orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
        <div class="absolute -top-28 -left-28 w-96 h-96 rounded-full bg-gradient-to-tr from-blue-500/30 to-cyan-400/25 blur-3xl"></div>
        <div class="absolute -bottom-28 -right-28 w-[30rem] h-[30rem] rounded-full bg-gradient-to-bl from-indigo-500/30 to-blue-400/25 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[34rem] h-[34rem] rounded-full bg-gradient-to-r from-blue-300/20 via-purple-300/15 to-indigo-300/20 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
