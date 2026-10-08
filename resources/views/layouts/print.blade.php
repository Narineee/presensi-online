<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Dokumen') - Presensi Digital</title>

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
        /* Pengaturan Dasar & Standar Font */
        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Hapus total scrollbar visual di semua browser (Chrome, Edge, Firefox, Safari) baik di layar maupun cetak */
        *, *::before, *::after {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        *::-webkit-scrollbar,
        ::-webkit-scrollbar,
        *::-webkit-scrollbar-thumb,
        ::-webkit-scrollbar-thumb,
        *::-webkit-scrollbar-track,
        ::-webkit-scrollbar-track {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }

        @media screen {
            html, body {
                overflow-x: hidden !important;
                background-color: #f1f5f9;
            }
            .print-sheet {
                width: 210mm;
                max-width: 100%;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                border: 1px solid #cbd5e1;
                padding: 14mm 12mm;
                box-sizing: border-box;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            *, *::before, *::after {
                box-shadow: none !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
            }

            html, body {
                background-color: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                overflow: visible !important;
                overflow-x: clip !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            main, .max-w-5xl, .max-w-4xl, .print-sheet {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                min-width: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                overflow: visible !important;
            }

            .overflow-x-auto, .overflow-visible {
                overflow: visible !important;
                overflow-x: clip !important;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 10mm 12mm 10mm;
            }

            table {
                page-break-inside: auto;
                border-collapse: collapse !important;
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
            }

            th, td {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen">

    <!-- Top Action Bar Khusus Pratinjau Lembar Cetak (Tidak ikut tercetak) -->
    <div class="no-print sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-3 shadow-xs">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-200/60">
                    📄 Pratinjau Lembar Cetak Dokumen (Kertas A4)
                </span>
                <span class="hidden sm:inline text-slate-300">|</span>
                <span class="hidden sm:inline text-slate-500 font-medium">
                    Halaman pratinjau lembar kertas A4. Klik tombol di kanan untuk langsung mencetak atau simpan sebagai file PDF.
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer"
                    title="Cetak langsung ke printer atau simpan sebagai file PDF"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                    </svg>
                    <span>Cetak / Simpan PDF</span>
                </button>
                <button
                    type="button"
                    onclick="window.close()"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                >
                    Tutup Tab
                </button>
            </div>
        </div>
    </div>

    <!-- Area Lembar Kertas Cetak -->
    <main class="py-6 sm:py-8 px-2 sm:px-4">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
