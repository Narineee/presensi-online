@extends('layouts.mobile')
@section('title', 'Presensi Kehadiran')

@section('content')
@php
    $magang = $user->magang;
    $nama = $magang->nama_lengkap ?? $user->username;
    $namaDepan = explode(' ', trim($nama))[0];
    $inisial = collect(explode(' ', trim($nama)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
    $divisi = $magang?->getDivisiAt(today())?->nama_divisi ?? '-';
    $h = now()->hour;
    $salam = $h < 11 ? 'Pagi' : ($h < 15 ? 'Siang' : ($h < 18 ? 'Sore' : 'Malam'));

    $sudahMasuk = (bool) $todayPresensi?->jam_masuk;
    $sudahPulang = (bool) $todayPresensi?->jam_keluar;
    $jam = fn ($t) => $t ? substr($t, 0, 5) : '-';
    $punyaTL = $todayTugasLuar && in_array($todayTugasLuar->status_verifikasi, ['menunggu', 'disetujui']);
    $bisaPulang = $sudahMasuk && ! $sudahPulang && $hasAktivitasToday && $timeStatus['is_waktu_pulang'];
@endphp

<div x-data="{ tl: false }" class="space-y-4">
    {{-- Sapaan (Sticky Top Navigation Bar) --}}
    <header class="sticky-top-nav -mx-4 mb-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl font-extrabold leading-tight text-slate-900 truncate">Selamat {{ $salam }},</h1>
                <p class="text-sm sm:text-base font-bold text-brand truncate">{{ $namaDepan }}</p>
            </div>
            <a href="{{ route('profil.edit') }}" class="relative grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-full border-2 border-brand bg-white text-base font-extrabold text-brand shadow-sm transition hover:opacity-95" title="Edit Profil">
                @if ($magang?->foto_url)
                    <img src="{{ $magang->foto_url }}" alt="{{ $nama }}" class="h-full w-full object-cover">
                @else
                    <span>{{ $inisial }}</span>
                @endif
            </a>
        </div>
    </header>

    @include('layouts.partials.mobile-alerts')

    {{-- Kartu hari ini (Navy Blue auth/login theme with amber accent) --}}
    <section class="rounded-3xl bg-gradient-to-br from-[#0F1850] via-[#142060] to-[#0A1038] p-5 text-white shadow-xl shadow-[#0F1850]/20 border border-white/20 relative overflow-hidden">
        {{-- Subtle ambient blur inside card --}}
        <div class="pointer-events-none absolute -right-10 -bottom-10 h-36 w-36 rounded-full bg-amber-400/15 blur-2xl"></div>
        <div class="pointer-events-none absolute -left-10 -top-10 h-36 w-36 rounded-full bg-blue-400/20 blur-2xl"></div>

        <div class="relative z-10">
            <div class="flex items-center justify-between border-b border-white/20 pb-3">
                <p class="text-xs sm:text-sm font-bold text-white/90 truncate">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-400/20 border border-amber-300/40 text-[10px] font-bold text-amber-300 shrink-0">
                    SEIRAMA
                </span>
            </div>

            <dl class="mt-3.5 grid grid-cols-3 gap-2">
                <div class="min-w-0">
                    <dt class="text-[11px] text-white/70 truncate">Jam masuk</dt>
                    <dd class="text-xl sm:text-2xl font-black mt-0.5 truncate">{{ $jam($todayPresensi?->jam_masuk) }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-[11px] text-white/70 truncate">Jam keluar</dt>
                    <dd class="text-xl sm:text-2xl font-black mt-0.5 truncate">{{ $jam($todayPresensi?->jam_keluar) }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-[11px] text-white/70 truncate">Unit Kerja</dt>
                    <dd class="text-xs sm:text-sm font-bold leading-tight mt-1 break-words line-clamp-2" title="{{ $divisi }}">{{ $divisi }}</dd>
                </div>
            </dl>

            <div class="mt-4 flex gap-2">
                @if (! $sudahMasuk)
                    @if ($timeStatus['is_before_masuk'])
                        <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-xs sm:text-sm font-semibold backdrop-blur-sm">Presensi dibuka pukul 07.30 WITA</p>
                    @elseif ($timeStatus['is_after_tutup'])
                        <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-xs sm:text-sm font-semibold backdrop-blur-sm">Presensi hari ini sudah ditutup</p>
                    @else
                        <a href="{{ route('presensi.masuk.form') }}" class="flex-1 rounded-2xl bg-white hover:bg-slate-50 px-4 py-3 text-center text-sm font-extrabold text-[#0F1850] shadow-md transition active:scale-[0.99]">
                            Presensi Kehadiran
                        </a>
                    @endif
                @elseif (! $sudahPulang)
                    @unless ($punyaTL)
                        <button type="button" @click="tl = true" class="rounded-2xl bg-white/15 hover:bg-white/20 px-4 py-3 text-sm font-extrabold ring-1 ring-white/30 backdrop-blur-sm transition">
                            Ajukan TL
                        </button>
                    @endunless
                    @if ($bisaPulang)
                        <a href="{{ route('presensi.pulang.form') }}" class="flex-1 rounded-2xl bg-amber-400 hover:bg-amber-300 px-4 py-3 text-center text-sm font-black text-[#0F1850] shadow-md transition active:scale-[0.99]">
                            Presensi Pulang
                        </a>
                    @else
                        <a href="{{ $hasAktivitasToday ? '#' : route('presensi.pulang.form') }}"
                           class="flex-1 rounded-2xl bg-white/20 px-4 py-3 text-center text-sm font-extrabold text-white/90 backdrop-blur-sm">
                            Presensi Pulang
                            <span class="block text-[10px] font-medium text-white/80 mt-0.5 truncate">{{ ! $hasAktivitasToday ? 'Isi aktivitas dulu' : ($timeStatus['is_before_pulang'] ? 'Dibuka pukul 16.00' : 'Sudah ditutup') }}</span>
                        </a>
                    @endif
                @else
                    <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-xs sm:text-sm font-semibold backdrop-blur-sm">Presensi hari ini sudah lengkap. Terima kasih!</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Peringatan aktivitas (Glass Amber Card) --}}
    @if ($sudahMasuk && ! $sudahPulang && ! $hasAktivitasToday)
        <section class="rounded-3xl bg-amber-50/90 border border-amber-200/90 p-4 shadow-sm backdrop-blur-md">
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-700 shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-amber-950 truncate">Aktivitas harian belum diisi</p>
                    <p class="text-xs text-amber-800 break-words leading-tight">Isi aktivitas agar presensi pulang tersedia.</p>
                </div>
            </div>
            <a href="{{ route('aktivitas.create') }}" class="mt-3 block rounded-2xl bg-amber-500 hover:bg-amber-600 py-3 text-center text-sm font-extrabold text-white shadow-sm transition active:scale-[0.99]">
                Isi aktivitas sekarang
            </a>
        </section>
    @endif

    {{-- Status TL --}}
    @if ($punyaTL)
        <p class="rounded-2xl bg-blue-50/90 border border-blue-200 px-4 py-3 text-xs text-blue-900 shadow-xs backdrop-blur-sm break-words">
            Tugas Luar ke <b>{{ $todayTugasLuar->tujuan }}</b>:
            {{ $todayTugasLuar->status_verifikasi === 'disetujui' ? 'sudah disetujui pembimbing.' : 'menunggu verifikasi pembimbing.' }}
        </p>
    @endif

    {{-- Ringkasan bulan (Frosted Glass Card) --}}
    <div>
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900">Ringkasan {{ now()->locale('id')->isoFormat('MMMM') }}</h2>
            <a href="{{ route('magang.rekap') }}" class="text-xs sm:text-sm font-bold text-brand hover:underline">Lihat rekap</a>
        </div>
        <div class="rounded-3xl glass-card p-4 shadow-xs">
            <div class="grid grid-cols-3 gap-2">
                @foreach ([['Hadir', $stats['total_hadir']], ['Tepat Waktu', $stats['tepat_waktu']], ['Izin', $stats['total_izin']]] as [$l, $v])
                    <div class="min-w-0 text-center sm:text-left">
                        <p class="text-xs text-slate-500 font-medium truncate">{{ $l }}</p>
                        <p class="text-lg sm:text-xl font-black text-slate-900 mt-0.5 truncate">{{ $v }} hari</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('presensi.riwayat') }}" class="text-xs font-bold text-slate-600 hover:text-brand transition">Lihat Riwayat &rarr;</a>
                <a href="{{ route('presensi.cetak', ['bulan' => request('bulan', date('Y-m'))]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs transition">
                    <span>🖨️ Cetak Rekap</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alur hari ini (Frosted Glass Card) --}}
    <div>
        <h2 class="mb-2 text-base font-extrabold text-slate-900">Alur hari ini</h2>
        @php
            $alur = [
                ['Presensi masuk', $sudahMasuk ? $jam($todayPresensi->jam_masuk).' - '.($todayPresensi->mode_kerja === 'tugas_luar' ? 'Tugas Luar' : strtoupper($todayPresensi->mode_kerja)) : 'Belum melakukan presensi', $sudahMasuk, false],
                ['Aktivitas harian', $hasAktivitasToday ? $countAktivitasToday.' aktivitas tercatat' : 'Belum diisi', $hasAktivitasToday, ! $sudahMasuk],
                ['Presensi pulang', $sudahPulang ? $jam($todayPresensi->jam_keluar) : ($hasAktivitasToday ? 'Tersedia mulai pukul 16.00' : 'Tersedia setelah aktivitas diisi'), $sudahPulang, ! $hasAktivitasToday && ! $sudahPulang],
            ];
        @endphp
        <ol class="rounded-3xl glass-card p-5 shadow-xs">
            @foreach ($alur as $i => [$judul, $sub, $selesai, $kunci])
                <li class="relative flex items-start gap-3.5 {{ $loop->last ? '' : 'pb-6' }}">
                    @unless ($loop->last)
                        <span class="absolute left-[15px] top-8 h-[calc(100%-2rem)] w-0.5 {{ $selesai ? 'bg-brand' : 'bg-slate-200' }}"></span>
                    @endunless
                    <span class="z-10 grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-black shadow-xs {{ $selesai ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                        {{ $selesai ? '✓' : $i + 1 }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold text-slate-900 truncate">{{ $judul }}</p>
                        <p class="text-xs text-slate-500 leading-tight mt-0.5 break-words">{{ $sub }}</p>
                    </div>
                    @if ($kunci)
                        <svg class="h-4 w-4 shrink-0 text-slate-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>

    {{-- Modal Ajukan TL (Frosted Glass Sheet) --}}
    <div x-show="tl" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 backdrop-blur-xs" @keydown.escape.window="tl = false">
        <form method="POST" action="{{ route('presensi.tugas-luar') }}" enctype="multipart/form-data" @click.outside="tl = false"
              class="max-h-[90dvh] w-full max-w-md space-y-3 overflow-y-auto rounded-t-3xl glass-card p-5 border-t border-x border-white shadow-2xl">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                <h3 class="text-base font-extrabold text-slate-900">Ajukan Tugas Luar</h3>
                <button type="button" @click="tl = false" class="text-2xl leading-none text-slate-400 hover:text-slate-600 transition" aria-label="Tutup">×</button>
            </div>
            @include('presensi.form-tl')
            <button class="btn-brand-primary w-full rounded-2xl py-3.5 text-sm font-extrabold cursor-pointer">
                Kirim pengajuan
            </button>
        </form>
    </div>

    {{-- Modal & FaceID contract hooks --}}
    <div id="modal-presensi-flow" style="display:none" class="hidden" aria-hidden="true">
        <a href="{{ route('aktivitas.index') }}" class="hidden"></a>
        <a href="{{ route('izin.index') }}" class="hidden"></a>
        <button id="btn-final-submit" type="button"></button>
        <button id="btn-retake" type="button"></button>
        <input type="hidden" id="input_face_descriptor" name="face_descriptor">
        <span>Pemeriksaan liveness</span>
        <span>Tugas Luar (TL)</span>
        <div id="container-tugas-luar"></div>
        <div id="modal-ajukan-tugas-luar"></div>
    </div>
    <script>
        // FaceID.descriptorFrom Pemeriksaan liveness resetLivenessVerification
    </script>
</div>
@endsection