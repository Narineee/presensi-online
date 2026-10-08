@extends('layouts.mobile')
@section('title', 'Presensi Pulang')

@if ($hasAktivitas)
    @push('head')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
        <script src="{{ asset('js/face-id.js') }}"></script>
        <script src="{{ asset('js/presensi-flow.js') }}"></script>
    @endpush
@endif

@section('content')
@if (! $hasAktivitas)
    {{-- Layar terkunci: aktivitas harian belum diisi --}}
    <header class="sticky-top-nav -mx-4 mb-4 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
        <div class="flex items-center gap-3 pb-2.5">
            <a href="{{ route('presensi.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
                <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Presensi Pulang</h1>
                <p class="text-xs text-slate-500 font-medium">Langkah 1 dari 2</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-1.5"><div class="h-1.5 rounded-full bg-slate-200"></div><div class="h-1.5 rounded-full bg-slate-200"></div></div>
    </header>

    @include('layouts.partials.mobile-alerts')

    <section class="mt-4 rounded-3xl glass-card p-6 text-center shadow-sm">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-amber-100 text-amber-700 shadow-xs">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        </span>
        <p class="mx-auto mt-4 w-fit rounded-full bg-amber-100/80 border border-amber-300/80 px-3.5 py-1 text-xs font-black text-amber-900">Presensi Pulang Terkunci</p>
        <h2 class="mt-3 text-lg sm:text-xl font-extrabold text-slate-900">Wajib mengisi aktivitas harian</h2>
        <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-600 max-w-sm mx-auto">Anda belum mengisi aktivitas harian untuk hari ini. Catat rincian tugas atau progres pekerjaan hari ini terlebih dahulu agar formulir presensi kepulangan dapat terbuka.</p>
        <div class="mt-5 grid grid-cols-2 gap-2.5">
            <a href="{{ route('aktivitas.create') }}" class="btn-brand-primary rounded-2xl px-3 py-3.5 text-xs sm:text-sm font-extrabold text-white text-center">Catat aktivitas sekarang</a>
            <a href="{{ route('presensi.pulang.form') }}" class="rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 px-3 py-3.5 text-xs sm:text-sm font-extrabold text-slate-700 text-center transition">Segarkan</a>
        </div>
    </section>
@else
    <form method="POST" action="{{ route('presensi.keluar') }}"
          x-data="presensiFlow({
              mode: @js($cekRadius ? 'onsite' : 'free'),
              office: @js($officeLocation),
              models: @js(asset('models')),
              verifyUrl: @js(route('presensi.verifikasi-wajah')),
              csrf: @js(csrf_token()),
          })"
          @submit="submitting = true">
        @csrf
        <input type="hidden" name="lokasi_keluar" :value="coord">
        <input type="hidden" name="foto_keluar" :value="photo">
        <input type="hidden" name="face_descriptor" :value="descriptor">

        @include('presensi.header', ['title' => 'Presensi Pulang', 'backUrl' => route('presensi.index')])

        <div x-show="step === 1" class="space-y-3.5">
            @include('presensi.lokasi', ['office' => $officeLocation])
            <div class="pt-1">
                <button type="button" @click="next()" :disabled="!locReady"
                        class="btn-brand-primary flex w-full items-center justify-center rounded-2xl py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
                    Lanjut verifikasi wajah
                </button>
                <p class="mt-2 text-center text-xs text-slate-500 leading-tight">Dengan melanjutkan, Anda menyetujui pencatatan lokasi untuk presensi ini.</p>
            </div>
        </div>

        @include('presensi.wajah', ['submitLabel' => 'Simpan presensi pulang'])
    </form>
@endif
@endsection