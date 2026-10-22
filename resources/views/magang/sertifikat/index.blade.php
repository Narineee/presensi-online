@extends('layouts.mobile')
@section('title', 'Sertifikat Magang')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">

<style>
    /* Styling Dasar Sertifikat */
    .font-script-cert {
        font-family: 'Great Vibes', 'Brush Script MT', 'Segoe Script', cursive;
    }
    .font-sans-cert {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Versi Screen (Preview Interaktif) */
    .cert-screen-card {
        position: relative;
        width: 100%;
        max-width: 980px;
        aspect-ratio: 1.4142 / 1;
        background: #ebf1ff;
        box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.12), 0 5px 15px -3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin: 0 auto;
        color: #0c3773;
        box-sizing: border-box;
    }

    .cert-corner-tl-screen {
        position: absolute;
        top: 0;
        left: 0;
        width: 18.5%;
        max-width: 190px;
        z-index: 2;
        pointer-events: none;
        user-select: none;
    }

    .cert-corner-br-screen {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 25.5%;
        max-width: 260px;
        z-index: 2;
        pointer-events: none;
        user-select: none;
    }

    /* Kotak Putih Sertifikat (Menimpa bahan dekorasi sudut sehingga teks & TTD tidak tertutup) */
    .cert-inner-box-screen {
        position: absolute;
        inset: 5.2% 4.2%;
        background: #ffffff;
        box-shadow: 0 4px 20px -2px rgba(12, 55, 115, 0.08);
        z-index: 5;
        box-sizing: border-box;
    }

    .cert-frame-screen {
        position: absolute;
        inset: 10px;
        border: 2px solid #bae6fd;
        box-shadow: 0 0 0 1px #e0f2fe;
        z-index: 6;
        pointer-events: none;
        background: transparent;
    }

    .cert-content-screen {
        position: relative;
        z-index: 10;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 4.5% 5.5% 4%;
        box-sizing: border-box;
        text-align: center;
    }

    /* Versi Cetak / Print (A4 Landscape Pixel-Perfect) */
    @media print {
        header, footer, nav, aside, .no-print, #modal-presensi-flow {
            display: none !important;
        }
        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        main {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        @page {
            size: A4 landscape;
            margin: 0;
        }
        .cert-screen-wrapper {
            display: none !important;
        }
        .cert-print-sheet {
            display: block !important;
            width: 297mm !important;
            height: 210mm !important;
            max-width: 297mm !important;
            max-height: 210mm !important;
            position: relative !important;
            background: #ebf1ff !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .corner-tl-print {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 60mm !important;
            z-index: 1 !important;
        }
        .corner-br-print {
            position: absolute !important;
            top: 138mm !important;
            left: 212mm !important;
            width: 85mm !important;
            z-index: 1 !important;
        }
        .cert-card-print {
            position: absolute !important;
            top: 9mm !important;
            left: 11mm !important;
            width: 275mm !important;
            height: 192mm !important;
            background: #ffffff !important;
            border: 3.5px solid #bae6fd !important;
            box-sizing: border-box !important;
            z-index: 5 !important;
        }
        .section-table-print {
            width: 100% !important;
            height: 100% !important;
            border-collapse: collapse !important;
        }
    }
</style>
@endpush

@section('content')
<header class="no-print sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('magang.rekap') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali ke Rekap">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Rekapitulasi & Cetak Dokumen</h1>
        <p class="text-xs text-slate-500 font-medium truncate">Sertifikat Kelulusan Magang</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

<div class="space-y-4">

    @if(!$penilaian)
        {{-- State: Penilaian Belum Diisi / Diterbitkan --}}
        <section class="rounded-3xl glass-card p-8 shadow-sm text-center min-h-[340px] flex flex-col items-center justify-center space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center shadow-2xs border border-amber-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Sertifikat Belum Diterbitkan</h2>
                <p class="text-xs text-slate-500 max-w-xs mx-auto mt-1 leading-relaxed">
                    Pembimbing lapangan belum menginput evaluasi nilai akhir. Sertifikat kelulusan magang hanya dapat dicetak setelah penilaian resmi diterbitkan.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('magang.rekap') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold shadow-xs hover:bg-slate-50 transition">
                    Kembali ke Rekap
                </a>
            </div>
        </section>
    @else

        {{-- 1. SCREEN VIEW (Interactive Landscape Preview Card) --}}
        <div class="cert-screen-wrapper no-print space-y-4">

            {{-- Action & Info Bar --}}
            <div class="flex items-center justify-between gap-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-800">
                        ✓ Siap Dicetak
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('magang.sertifikat.cetak') }}?v={{ time() }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand/90 text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-sm transition active:scale-[.98]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>📄 Cetak Sertifikat (PDF)</span>
                    </a>
                    <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[.98] cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.641-2.135-1.196-3.14m0 0A12.001 12.001 0 0112 3c3.059 0 5.842 1.154 7.961 3.039m-1.196 3.14a12.016 12.016 0 01-1.196 3.14m0 0A11.96 11.96 0 0112 15a11.96 11.96 0 01-5.569-1.171M6 16.5v2.25A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V16.5" />
                        </svg>
                        <span>Cetak Cepat</span>
                    </button>
                </div>
            </div>

            {{-- Responsive Scrollable Wrapper (Memastikan landscape tampak utuh di mobile) --}}
            <div class="overflow-x-auto pb-4 rounded-3xl -mx-2 px-2 sm:mx-0 sm:px-0">
                <div class="min-w-[680px] lg:min-w-0">
                    <div class="cert-screen-card font-sans-cert rounded-2xl sm:rounded-3xl border border-slate-200/80">

                        {{-- Dekorasi Sudut Kiri Atas (bahan-1.png) --}}
                        <img src="{{ asset('images/sertifikat/bahan-1.png') }}"
                             alt="Dekorasi Sudut Kiri Atas"
                             class="cert-corner-tl-screen">

                        {{-- Dekorasi Sudut Kanan Bawah (bahan-2.png) --}}
                        <img src="{{ asset('images/sertifikat/bahan-2.png') }}"
                             alt="Dekorasi Sudut Kanan Bawah"
                             class="cert-corner-br-screen">

                        {{-- Kotak Putih Sertifikat (Menimpa bahan dekorasi sudut sehingga TTD & teks tidak tertutup) --}}
                        <div class="cert-inner-box-screen rounded-xl sm:rounded-2xl">
                            {{-- Bingkai Garis Biru Muda --}}
                            <div class="cert-frame-screen rounded-lg sm:rounded-xl"></div>

                            {{-- Isi Konten Sertifikat --}}
                            <div class="cert-content-screen">
                                {{-- 1. Header Judul --}}
                                <div>
                                    <h1 style="font-size: clamp(1.8rem, 4vw, 2.85rem); font-weight: 900; letter-spacing: 0.18em; color: #0c3773; line-height: 1; text-transform: uppercase;">
                                        SERTIFIKAT
                                    </h1>
                                    <p style="font-size: clamp(0.85rem, 1.8vw, 1.15rem); font-weight: 700; color: #0c3773; margin-top: 6px; letter-spacing: 0.05em;">
                                        Diberikan kepada:
                                    </p>
                                </div>

                                {{-- 2. Nama Peserta (Font Cursive Emas) & Nomor Induk --}}
                                <div style="margin: 4px 0;">
                                    <p class="font-script-cert" style="font-size: clamp(2.2rem, 5vw, 3.85rem); color: #c48a28; line-height: 1.1;">
                                        ({{ $penilaian->magang->nama_lengkap ?? ($magang->nama_lengkap ?? 'Peserta') }})
                                    </p>
                                    <p style="font-size: clamp(0.75rem, 1.3vw, 0.95rem); font-weight: 700; color: #0c3773; margin-top: 4px; letter-spacing: 0.05em;">
                                        Nomor Induk: {{ !empty($penilaian->magang->no_induk ?? $magang->no_induk) ? ($penilaian->magang->no_induk ?? $magang->no_induk) : '-' }}
                                    </p>
                                </div>

                                {{-- 3. Paragraf Kegiatan PKL / Magang --}}
                                <div style="max-width: 86%; margin: 0 auto;">
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 1.05rem); font-weight: 600; color: #0c3773; line-height: 1.6; margin: 0;">
                                        Telah melaksanakan Kegiatan Praktik Kerja Lapangan (PKL) di {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }} Provinsi Kalimantan Selatan
                                    </p>
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 1.05rem); font-weight: 600; color: #0c3773; line-height: 1.6; margin: 2px 0 0 0;">
                                        Pada Tanggal {{ $tanggalMulai }} sampai dengan {{ $tanggalSelesai }}
                                    </p>
                                </div>

                                {{-- 4. Paragraf Evaluasi Nilai Akhir --}}
                                <div style="margin: 4px 0;">
                                    <p style="font-size: clamp(0.75rem, 1.4vw, 1rem); font-weight: 500; color: #0c3773; margin: 0;">
                                        Berdasarkan hasil evaluasi, yang bersangkutan memperoleh nilai akhir dengan kualifikasi
                                    </p>
                                    <p style="font-size: clamp(1.15rem, 2.5vw, 1.75rem); font-weight: 900; color: #0c3773; margin: 3px 0 0 0; letter-spacing: 0.05em;">
                                        {{ $penilaian->keterangan_predikat }}
                                    </p>
                                </div>

                                {{-- 5. Bagian Tanda Tangan (Sebelah Kanan Bawah Sesuai Desain) --}}
                                <div class="w-full flex justify-end">
                                    <div class="relative z-10 w-[44%] max-w-[340px] text-center text-[#0c3773]" style="line-height: 1.4;">
                                        <p style="font-size: clamp(0.7rem, 1.3vw, 0.95rem); font-weight: 500; margin: 0;">
                                            {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalCetak }}
                                        </p>
                                        <p style="font-size: clamp(0.7rem, 1.3vw, 0.95rem); font-weight: 700; font-style: italic; margin: 2px 0 0 0;">
                                            Kepala {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                                        </p>

                                        {{-- Ruang Tanda Tangan (Luas 75px untuk TTD & Stempel) --}}
                                        <div style="height: clamp(55px, 8vw, 80px); line-height: 1;">&nbsp;</div>

                                        <p style="font-size: clamp(0.8rem, 1.5vw, 1.1rem); font-weight: 700; margin: 0; text-decoration: underline;">
                                            {{ $pengaturan->nama_kepala_dinas ?? 'Nama Kepala Dinas' }}
                                        </p>
                                        <p style="font-size: clamp(0.68rem, 1.2vw, 0.9rem); font-weight: 500; margin: 2px 0 0 0;">
                                            {{ $pengaturan->pangkat_golongan ?? 'Pembina Utama Muda' }}
                                        </p>
                                        <p style="font-size: clamp(0.65rem, 1.1vw, 0.85rem); font-weight: 500; color: #475569; margin: 2px 0 0 0;">
                                            NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Helper note & bottom action di bawah preview --}}
            <div class="pt-1 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5">
                <a href="{{ route('magang.sertifikat.cetak') }}?v={{ time() }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand hover:bg-brand/90 text-white rounded-xl text-xs font-extrabold shadow-sm transition active:scale-[.98] w-full sm:w-auto">
                    <span>📄 Unduh Sertifikat (PDF)</span>
                </a>
            </div>
        </div>

        {{-- 2. PRINT SHEET (Pixel-Perfect A4 Landscape Print Version) --}}
        <div class="cert-print-sheet hidden font-sans-cert">

            {{-- Dekorasi Sudut Kiri Atas (bahan-1.png) --}}
            <div class="corner-tl-print">
                <img src="{{ asset('images/sertifikat/bahan-1.png') }}"
                     alt="Dekorasi Sudut Kiri Atas"
                     style="width: 60mm;">
            </div>

            {{-- Dekorasi Sudut Kanan Bawah (bahan-2.png) --}}
            <div class="corner-br-print">
                <img src="{{ asset('images/sertifikat/bahan-2.png') }}"
                     alt="Dekorasi Sudut Kanan Bawah"
                     style="width: 85mm;">
            </div>

            {{-- Kotak Putih Sertifikat Utama --}}
            <div class="cert-card-print">
                <div style="padding: 2.5mm; height: 189mm;">
                    <div style="border: 1.5px solid #e0f2fe; height: 184mm; padding: 6mm 10mm 4mm 10mm; box-sizing: border-box; text-align: center;">

                        <table class="section-table-print" style="height: 100%;">
                            {{-- 1. Header Judul --}}
                            <tr>
                                <td style="text-align: center; vertical-align: top; height: 26mm;">
                                    <h1 style="font-size: 42pt; font-weight: 900; letter-spacing: 0.2em; color: #0c3773; margin: 0; line-height: 1; text-transform: uppercase;">
                                        SERTIFIKAT
                                    </h1>
                                    <p style="font-size: 14pt; font-weight: bold; color: #0c3773; margin: 3.5mm 0 0 0; letter-spacing: 0.05em;">
                                        Diberikan kepada:
                                    </p>
                                </td>
                            </tr>

                            {{-- 2. Nama Peserta (Font Cursive Emas Great Vibes) & Nomor Induk --}}
                            <tr>
                                <td style="text-align: center; vertical-align: middle; height: 32mm;">
                                    <p style="font-family: 'Great Vibes', 'Brush Script MT', cursive; font-size: 52pt; color: #c48a28; margin: 0; line-height: 1;">
                                        ({{ $penilaian->magang->nama_lengkap ?? ($magang->nama_lengkap ?? 'Peserta') }})
                                    </p>
                                    <p style="font-size: 11pt; font-weight: 700; color: #0c3773; margin: 2.5mm 0 0 0; letter-spacing: 0.05em;">
                                        Nomor Induk: {{ !empty($penilaian->magang->no_induk ?? $magang->no_induk) ? ($penilaian->magang->no_induk ?? $magang->no_induk) : '-' }}
                                    </p>
                                </td>
                            </tr>

                            {{-- 3. Paragraf Kegiatan PKL / Magang --}}
                            <tr>
                                <td style="text-align: center; vertical-align: middle; height: 26mm;">
                                    <div style="font-size: 13.5pt; font-weight: 600; color: #0c3773; line-height: 1.6; width: 88%; margin: 0 auto;">
                                        Telah melaksanakan Kegiatan Praktik Kerja Lapangan (PKL) di {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }} Provinsi Kalimantan Selatan<br>
                                        Pada Tanggal {{ $tanggalMulai }} sampai dengan {{ $tanggalSelesai }}
                                    </div>
                                </td>
                            </tr>

                            {{-- 4. Paragraf Evaluasi Nilai Akhir --}}
                            <tr>
                                <td style="text-align: center; vertical-align: middle; height: 24mm;">
                                    <p style="font-size: 13pt; font-weight: 500; color: #0c3773; margin: 0;">
                                        Berdasarkan hasil evaluasi, yang bersangkutan memperoleh nilai akhir dengan kualifikasi
                                    </p>
                                    <p style="font-size: 22pt; font-weight: 900; color: #0c3773; margin: 1.5mm 0 0 0; letter-spacing: 0.05em;">
                                        {{ $penilaian->keterangan_predikat }}
                                    </p>
                                </td>
                            </tr>

                            {{-- 5. Bagian Tanda Tangan (Sebelah Kanan Bawah Sesuai Desain) --}}
                            <tr>
                                <td style="vertical-align: bottom; height: 60mm; padding-bottom: 2mm;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 55%;"></td>
                                            <td style="width: 45%; text-align: center; color: #0c3773; padding-right: 5mm;">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <tr>
                                                        <td style="text-align: center; color: #0c3773; line-height: 1.35;">
                                                            <div style="font-size: 11.5pt; font-weight: 500;">
                                                                {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalCetak }}
                                                            </div>
                                                            <div style="font-size: 11.5pt; font-weight: bold; font-style: italic; margin-top: 1mm;">
                                                                Kepala {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        {{-- Ruang Kosong Tanda Tangan & Stempel (24mm uncollapsible) --}}
                                                        <td style="height: 24mm; line-height: 24mm; text-align: center;">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align: center; color: #0c3773; line-height: 1.35;">
                                                            <div style="font-size: 12.5pt; font-weight: bold; text-decoration: underline;">
                                                                {{ $pengaturan->nama_kepala_dinas ?? 'Nama Kepala Dinas' }}
                                                            </div>
                                                            <div style="font-size: 10.5pt; font-weight: 500; margin-top: 1mm;">
                                                                {{ $pengaturan->pangkat_golongan ?? 'Pembina Utama Muda' }}
                                                            </div>
                                                            <div style="font-size: 9.5pt; font-weight: 500; color: #475569; margin-top: 0.8mm;">
                                                                NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>

        </div>

    @endif

</div>
@endsection
