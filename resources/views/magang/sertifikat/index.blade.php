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
        .cert-corner-tl-print {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 55mm !important;
            z-index: 2 !important;
        }
        .cert-corner-br-print {
            position: absolute !important;
            bottom: 0 !important;
            right: 0 !important;
            width: 76mm !important;
            z-index: 2 !important;
        }
        .cert-inner-box-print {
            position: absolute !important;
            inset: 11mm 12mm !important;
            background: #ffffff !important;
            z-index: 5 !important;
            box-sizing: border-box !important;
        }
        .cert-frame-print {
            position: absolute !important;
            inset: 3.5mm !important;
            border: 1.2mm solid #bae6fd !important;
            box-shadow: 0 0 0 0.5mm #e0f2fe !important;
            z-index: 6 !important;
            pointer-events: none !important;
            background: transparent !important;
        }
        .cert-content-print {
            position: relative !important;
            z-index: 10 !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            padding: 10mm 16mm 9mm 16mm !important;
            box-sizing: border-box !important;
            text-align: center !important;
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
                    <span class="hidden sm:inline-block text-xs font-semibold text-slate-500">
                        Format resmi: Biru Emas Modern
                    </span>
                </div>

                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand/90 text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-sm transition active:scale-[.98] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.641-2.135-1.196-3.14m0 0A12.001 12.001 0 0112 3c3.059 0 5.842 1.154 7.961 3.039m-1.196 3.14a12.016 12.016 0 01-1.196 3.14m0 0A11.96 11.96 0 0112 15a11.96 11.96 0 01-5.569-1.171M6 16.5v2.25A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V16.5" />
                    </svg>
                    <span>Cetak Sertifikat (A4)</span>
                </button>
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
                                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-[0.18em] text-[#0c3773] drop-shadow-2xs">
                                        SERTIFIKAT
                                    </h1>
                                    <p class="text-xs sm:text-sm md:text-base font-semibold text-[#0c3773] mt-1 sm:mt-1.5 tracking-wide">
                                        Diberikan kepada:
                                    </p>
                                </div>

                                {{-- 2. Nama Peserta (Font Cursive Emas) --}}
                                <div class="my-1 sm:my-2">
                                    <p class="font-script-cert text-3xl sm:text-4xl md:text-5xl lg:text-[56px] text-[#c48a28] leading-tight drop-shadow-2xs">
                                        ({{ $penilaian->magang->nama_lengkap }})
                                    </p>
                                </div>

                                {{-- 3. Paragraf Kegiatan PKL / Magang --}}
                                <div class="max-w-[82%] mx-auto space-y-1">
                                    <p class="text-[11px] sm:text-[13px] md:text-[15px] font-semibold text-[#0c3773] leading-relaxed">
                                        Telah melaksanakan Kegiatan Praktik Kerja Lapangan (PKL) di {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }} Provinsi Kalimantan Selatan
                                    </p>
                                    <p class="text-[11px] sm:text-[13px] md:text-[15px] font-semibold text-[#0c3773] leading-relaxed">
                                        Pada Tanggal {{ $tanggalMulai }} sampai dengan {{ $tanggalSelesai }}
                                    </p>
                                </div>

                                {{-- 4. Paragraf Evaluasi Nilai Akhir --}}
                                <div class="my-1 sm:my-1.5 space-y-0.5">
                                    <p class="text-[11px] sm:text-[13px] md:text-[14px] font-medium text-[#0c3773]">
                                        Berdasarkan hasil evaluasi, yang bersangkutan memperoleh nilai akhir sebesar
                                    </p>
                                    <p class="text-base sm:text-xl md:text-2xl font-black text-[#0c3773] tracking-wide">
                                        [{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})]
                                    </p>
                                </div>

                                {{-- 5. Bagian Tanda Tangan (Sebelah Kanan Bawah Sesuai Desain) --}}
                                <div class="w-full flex justify-end">
                                    <div class="relative z-10 w-[42%] max-w-[320px] text-center space-y-0.5 text-[#0c3773]">
                                        <p class="text-[10px] sm:text-[12px] md:text-[13px] font-medium leading-tight">
                                            {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalCetak }}
                                        </p>
                                        <p class="text-[10px] sm:text-[12px] md:text-[13px] font-bold italic leading-tight">
                                            Kepala {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                                        </p>

                                        {{-- Ruang Tanda Tangan --}}
                                        <div class="h-10 sm:h-12 md:h-14"></div>

                                        <p class="text-[11px] sm:text-[13px] md:text-[14px] font-bold leading-tight">
                                            [{{ $pengaturan->nama_kepala_dinas ?? 'Nama Kepala Dinas' }}]
                                        </p>
                                        <p class="text-[10px] sm:text-[11px] md:text-[12px] font-medium leading-tight">
                                            {{ $pengaturan->pangkat_golongan ?? 'Pembina Utama Muda' }}
                                        </p>
                                        <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium leading-tight text-slate-600">
                                            NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Helper note di bawah preview --}}
            <p class="text-[11px] text-slate-500 text-center">
                💡 Klik tombol <strong>Cetak Sertifikat</strong> untuk mencetak langsung atau menyimpan dalam format PDF dengan ukuran standar A4 Landscape.
            </p>
        </div>

        {{-- 2. PRINT SHEET (Pixel-Perfect A4 Landscape Print Version) --}}
        <div class="cert-print-sheet hidden font-sans-cert">

            {{-- Dekorasi Sudut Kiri Atas (bahan-1.png) --}}
            <img src="{{ asset('images/sertifikat/bahan-1.png') }}"
                 alt="Dekorasi Sudut Kiri Atas"
                 class="cert-corner-tl-print">

            {{-- Dekorasi Sudut Kanan Bawah (bahan-2.png) --}}
            <img src="{{ asset('images/sertifikat/bahan-2.png') }}"
                 alt="Dekorasi Sudut Kanan Bawah"
                 class="cert-corner-br-print">

            {{-- Kotak Putih Sertifikat (Menimpa bahan dekorasi sudut sehingga TTD & teks tidak tertutup) --}}
            <div class="cert-inner-box-print">
                {{-- Bingkai Garis Biru Muda --}}
                <div class="cert-frame-print"></div>

                {{-- Isi Konten Sertifikat --}}
                <div class="cert-content-print">
                    {{-- 1. Header Judul --}}
                    <div style="margin-top: 4mm;">
                        <h1 style="font-size: 38pt; font-weight: 900; letter-spacing: 0.18em; color: #0c3773; margin: 0; line-height: 1;">
                            SERTIFIKAT
                        </h1>
                        <p style="font-size: 13pt; font-weight: 600; color: #0c3773; margin-top: 3mm; letter-spacing: 0.05em;">
                            Diberikan kepada:
                        </p>
                    </div>

                    {{-- 2. Nama Peserta (Font Cursive Emas) --}}
                    <div style="margin: 3mm 0;">
                        <p class="font-script-cert" style="font-size: 46pt; color: #c48a28; margin: 0; line-height: 1.1;">
                            ({{ $penilaian->magang->nama_lengkap }})
                        </p>
                    </div>

                    {{-- 3. Paragraf Kegiatan PKL / Magang --}}
                    <div style="max-width: 82%; margin: 0 auto; line-height: 1.6;">
                        <p style="font-size: 12pt; font-weight: 600; color: #0c3773; margin: 0;">
                            Telah melaksanakan Kegiatan Praktik Kerja Lapangan (PKL) di {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }} Provinsi Kalimantan Selatan
                        </p>
                        <p style="font-size: 12pt; font-weight: 600; color: #0c3773; margin: 1mm 0 0 0;">
                            Pada Tanggal {{ $tanggalMulai }} sampai dengan {{ $tanggalSelesai }}
                        </p>
                    </div>

                    {{-- 4. Paragraf Evaluasi Nilai Akhir --}}
                    <div style="margin: 2mm 0;">
                        <p style="font-size: 11.5pt; font-weight: 500; color: #0c3773; margin: 0;">
                            Berdasarkan hasil evaluasi, yang bersangkutan memperoleh nilai akhir sebesar
                        </p>
                        <p style="font-size: 18pt; font-weight: 900; color: #0c3773; margin: 1.5mm 0 0 0; letter-spacing: 0.04em;">
                            [{{ $penilaian->predikat }} ({{ $penilaian->keterangan_predikat }})]
                        </p>
                    </div>

                    {{-- 5. Bagian Tanda Tangan (Sebelah Kanan Bawah) --}}
                    <div style="width: 100%; display: flex; justify-content: flex-end;">
                        <div style="position: relative; z-index: 10; width: 38%; text-align: center; color: #0c3773; margin-right: 6mm;">
                            <p style="font-size: 11pt; font-weight: 500; margin: 0; line-height: 1.3;">
                                {{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ $tanggalCetak }}
                            </p>
                            <p style="font-size: 11pt; font-weight: 700; font-style: italic; margin: 1mm 0 0 0; line-height: 1.3;">
                                Kepala {{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}
                            </p>

                            {{-- Ruang Tanda Tangan --}}
                            <div style="height: 18mm;"></div>

                            <p style="font-size: 12pt; font-weight: 800; margin: 0; line-height: 1.3;">
                                [{{ $pengaturan->nama_kepala_dinas ?? 'Nama Kepala Dinas' }}]
                            </p>
                            <p style="font-size: 10pt; font-weight: 500; margin: 0.5mm 0 0 0; line-height: 1.3;">
                                {{ $pengaturan->pangkat_golongan ?? 'Pembina Utama Muda' }}
                            </p>
                            <p style="font-size: 9.5pt; font-weight: 500; color: #475569; margin: 0.5mm 0 0 0; line-height: 1.3;">
                                NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>
@endsection
