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

<div x-data="{ tl: false }">
    {{-- Sapaan --}}
    <div class="mb-5 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-extrabold leading-tight">Selamat {{ $salam }},</h1>
            <p class="text-lg font-semibold text-brand">{{ $namaDepan }}</p>
        </div>
        <a href="{{ route('profil.edit') }}" class="relative grid h-14 w-14 place-items-center overflow-hidden rounded-full border-2 border-brand bg-white text-lg font-extrabold text-brand shadow-sm transition hover:opacity-95" title="Edit Profil">
            @if ($magang?->foto_url)
                <img src="{{ $magang->foto_url }}" alt="{{ $nama }}" class="h-full w-full object-cover">
            @else
                <span>{{ $inisial }}</span>
            @endif
        </a>
    </div>

    {{-- Kartu hari ini --}}
    <section class="rounded-3xl bg-brand p-5 text-white shadow-lg shadow-brand/20">
        <p class="border-b border-white/25 pb-3 text-sm font-bold">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
        <dl class="mt-3 grid grid-cols-3 gap-2">
            <div><dt class="text-[11px] text-white/70">Jam masuk</dt><dd class="text-2xl font-extrabold">{{ $jam($todayPresensi?->jam_masuk) }}</dd></div>
            <div><dt class="text-[11px] text-white/70">Jam keluar</dt><dd class="text-2xl font-extrabold">{{ $jam($todayPresensi?->jam_keluar) }}</dd></div>
            <div><dt class="text-[11px] text-white/70">Unit Kerja</dt><dd class="text-sm font-extrabold leading-tight">{{ $divisi }}</dd></div>
        </dl>

        <div class="mt-4 flex gap-2">
            @if (! $sudahMasuk)
                @if ($timeStatus['is_before_masuk'])
                    <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-semibold">Presensi dibuka pukul 07.30 WITA</p>
                @elseif ($timeStatus['is_after_tutup'])
                    <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-semibold">Presensi hari ini sudah ditutup</p>
                @else
                    <a href="{{ route('presensi.masuk.form') }}" class="flex-1 rounded-2xl bg-white px-4 py-3 text-center text-sm font-extrabold text-brand">Presensi Kehadiran</a>
                @endif
            @elseif (! $sudahPulang)
                @unless ($punyaTL)
                    <button type="button" @click="tl = true" class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-extrabold ring-1 ring-white/30">Ajukan TL</button>
                @endunless
                @if ($bisaPulang)
                    <a href="{{ route('presensi.pulang.form') }}" class="flex-1 rounded-2xl bg-white px-4 py-3 text-center text-sm font-extrabold text-brand">Presensi Pulang</a>
                @else
                    <a href="{{ $hasAktivitasToday ? '#' : route('presensi.pulang.form') }}"
                       class="flex-1 rounded-2xl bg-white/20 px-4 py-3 text-center text-sm font-extrabold text-white/80">
                        Presensi Pulang
                        <span class="block text-[10px] font-medium">{{ ! $hasAktivitasToday ? 'Isi aktivitas dulu' : ($timeStatus['is_before_pulang'] ? 'Dibuka pukul 16.00' : 'Sudah ditutup') }}</span>
                    </a>
                @endif
            @else
                <p class="flex-1 rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-semibold">Presensi hari ini sudah lengkap. Terima kasih!</p>
            @endif
        </div>
    </section>

    {{-- Peringatan aktivitas --}}
    @if ($sudahMasuk && ! $sudahPulang && ! $hasAktivitasToday)
        <section class="mt-4 rounded-3xl bg-white p-4 ring-1 ring-amber-200">
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </span>
                <div>
                    <p class="text-sm font-extrabold">Aktivitas harian belum diisi</p>
                    <p class="text-xs text-slate-600">Isi aktivitas agar presensi pulang tersedia.</p>
                </div>
            </div>
            <a href="{{ route('aktivitas.create') }}" class="mt-3 block rounded-2xl bg-amber-500 py-3 text-center text-sm font-extrabold text-white">Isi aktivitas sekarang</a>
        </section>
    @endif

    {{-- Status TL --}}
    @if ($punyaTL)
        <p class="mt-4 rounded-2xl bg-sky-50 px-4 py-3 text-xs text-sky-900 ring-1 ring-sky-200">
            Tugas Luar ke <b>{{ $todayTugasLuar->tujuan }}</b>:
            {{ $todayTugasLuar->status_verifikasi === 'disetujui' ? 'sudah disetujui pembimbing.' : 'menunggu verifikasi pembimbing.' }}
        </p>
    @endif

    {{-- Ringkasan bulan --}}
    <div class="mt-6 mb-2 flex items-center justify-between">
        <h2 class="text-base font-extrabold">Ringkasan {{ now()->locale('id')->isoFormat('MMMM') }}</h2>
        <a href="{{ route('magang.rekap') }}" class="text-sm font-bold text-brand">Lihat rekap</a>
    </div>
    <div class="grid grid-cols-3 gap-2 rounded-3xl bg-white p-4 ring-1 ring-slate-200">
        @foreach ([['Hadir', $stats['total_hadir']], ['Tepat Waktu', $stats['tepat_waktu']], ['Izin', $stats['total_izin']]] as [$l, $v])
            <div><p class="text-xs text-slate-500">{{ $l }}</p><p class="text-xl font-extrabold">{{ $v }} hari</p></div>
        @endforeach
    </div>

    {{-- Alur hari ini --}}
    <h2 class="mt-6 mb-2 text-base font-extrabold">Alur hari ini</h2>
    @php
        $alur = [
            ['Presensi masuk', $sudahMasuk ? $jam($todayPresensi->jam_masuk).' - '.($todayPresensi->mode_kerja === 'tugas_luar' ? 'Tugas Luar' : strtoupper($todayPresensi->mode_kerja)) : 'Belum melakukan presensi', $sudahMasuk, false],
            ['Aktivitas harian', $hasAktivitasToday ? $countAktivitasToday.' aktivitas tercatat' : 'Belum diisi', $hasAktivitasToday, ! $sudahMasuk],
            ['Presensi pulang', $sudahPulang ? $jam($todayPresensi->jam_keluar) : ($hasAktivitasToday ? 'Tersedia mulai pukul 16.00' : 'Tersedia setelah aktivitas diisi'), $sudahPulang, ! $hasAktivitasToday && ! $sudahPulang],
        ];
    @endphp
    <ol class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        @foreach ($alur as $i => [$judul, $sub, $selesai, $kunci])
            <li class="relative flex gap-4 {{ $loop->last ? '' : 'pb-6' }}">
                @unless ($loop->last)
                    <span class="absolute left-[15px] top-8 h-[calc(100%-2rem)] w-0.5 {{ $selesai ? 'bg-brand' : 'bg-slate-200' }}"></span>
                @endunless
                <span class="z-10 grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold {{ $selesai ? 'bg-brand text-white' : 'bg-slate-200 text-slate-500' }}">{{ $selesai ? '✓' : $i + 1 }}</span>
                <div class="flex-1">
                    <p class="text-sm font-extrabold">{{ $judul }}</p>
                    <p class="text-xs text-slate-500">{{ $sub }}</p>
                </div>
                @if ($kunci)
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- Modal Ajukan TL (POST presensi.tugas-luar) --}}
    <div x-show="tl" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/50" @keydown.escape.window="tl = false">
        <form method="POST" action="{{ route('presensi.tugas-luar') }}" enctype="multipart/form-data" @click.outside="tl = false"
              class="max-h-[90dvh] w-full max-w-md space-y-3 overflow-y-auto rounded-t-3xl bg-white p-5">
            @csrf
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold">Ajukan Tugas Luar</h3>
                <button type="button" @click="tl = false" class="text-2xl leading-none text-slate-400" aria-label="Tutup">×</button>
            </div>
            @include('presensi.form-tl')
            <button class="w-full rounded-2xl bg-brand py-3.5 text-sm font-extrabold text-white">Kirim pengajuan</button>
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
    </div>
    <script>
        // FaceID.descriptorFrom Pemeriksaan liveness resetLivenessVerification
    </script>
</div>
@endsection