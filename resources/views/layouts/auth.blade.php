<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0F1850]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F1850">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0F1850">
    <title>@yield('title', 'Masuk ke Sistem') - Sistem Informasi Manajemen Magang</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                            neon: '#DD00FF',
                            neonHover: '#E51AFF',
                            dark: '#0F1850',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        html, body {
            background-color: #0F1850 !important;
            background: #0F1850 !important;
            color: #ffffff;
            min-height: 100%;
            min-height: 100dvh;
            width: 100%;
            max-width: 100vw;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0F1850 !important;
        }
        .glass-sikap {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.25);
        }
        .input-sikap {
            background: rgba(115, 132, 166, 0.38);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.2s ease-in-out;
        }
        .input-sikap:focus {
            background: rgba(115, 132, 166, 0.55);
            border-color: rgba(221, 0, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(221, 0, 255, 0.25);
        }
        .btn-sikap {
            background-color: #DD00FF;
            box-shadow: 0 10px 25px -4px rgba(221, 0, 255, 0.45);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-sikap:hover {
            background-color: #E51AFF;
            box-shadow: 0 14px 30px -4px rgba(221, 0, 255, 0.6);
            transform: translateY(-1px);
        }
        .btn-sikap:active {
            transform: translateY(0) scale(0.99);
        }
    </style>
</head>
<body class="min-h-screen min-h-[100dvh] w-full max-w-[100vw] antialiased text-white bg-[#0F1850] flex flex-col justify-between lg:justify-center lg:items-center p-0 lg:p-8 xl:p-10 relative overflow-x-hidden selection:bg-[#DD00FF] selection:text-white">

    <div class="w-full max-w-6xl mx-auto relative z-10 flex-1 flex flex-col justify-between lg:justify-center my-0 lg:my-auto min-h-[100dvh] lg:min-h-0">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
