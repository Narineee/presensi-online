@extends('layouts.mobile')
@section('title', 'Pendaftaran Wajah')
@section('hideNav', '1')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
    <script src="{{ asset('js/face-id.js') }}"></script>
    <script src="{{ asset('js/presensi-flow.js') }}"></script>
@endpush

@section('content')
<form method="POST" action="{{ route('wajah.store') }}" x-data="wajahRegister({ models: @js(asset('models')) })" class="pb-4">
    @csrf
    <input type="hidden" name="face_foto" :value="shots[0] || ''">
    <input type="hidden" name="face_descriptors" :value="json">

    <header class="sticky-top-nav -mx-4 mb-4 flex items-start gap-3 border-b border-slate-200 bg-white/95 px-4 pb-3.5 backdrop-blur-md lg:mx-0 lg:rounded-2xl lg:border">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 text-brand">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 003.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0120.25 6v1.5m0 9V18A2.25 2.25 0 0118 20.25h-1.5m-9 0H6A2.25 2.25 0 013.75 18v-1.5M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </span>
        <div>
            <h1 class="text-base font-extrabold leading-tight">Pendaftaran Wajah Biometrik</h1>
            <p class="text-xs text-slate-500">Langkah ini hanya perlu dilakukan satu kali sebelum Anda dapat melakukan presensi.</p>
        </div>
    </header>

    <div class="mb-2 flex items-center justify-between text-xs font-bold">
        <span class="text-slate-500">Tahap Perekaman Biometrik</span>
        <span class="text-brand" x-text="'Langkah ' + Math.min(idx + 1, 4) + ' dari 4'"></span>
    </div>
    <div class="mb-4 h-2 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full rounded-full bg-brand transition-all duration-500" :style="'width:' + (idx * 25) + '%'"></div>
    </div>

    {{-- 4 slot hasil rekam --}}
    <div class="mb-4 grid grid-cols-4 gap-2">
        <template x-for="(s, i) in shots" :key="i">
            <div class="grid aspect-square place-items-center overflow-hidden rounded-2xl bg-slate-200 ring-2"
                 :class="s ? 'ring-brand' : (i === idx && cam === 'ready' ? 'ring-brand/40' : 'ring-transparent')">
                <img x-show="s" :src="s" alt="" class="h-full w-full -scale-x-100 object-cover">
                <span x-show="!s" class="text-xs font-bold text-slate-400" x-text="i + 1"></span>
            </div>
        </template>
    </div>

    {{-- Kamera --}}
    <div class="relative mx-auto aspect-[3/4] w-full overflow-hidden rounded-3xl bg-slate-800">
        <video x-ref="video" playsinline muted class="absolute inset-0 h-full w-full -scale-x-100 object-cover"></video>
        <div class="absolute inset-0 grid place-items-center">
            <div class="h-[76%] w-[60%] rounded-[50%] border-[3px] transition-colors" :class="complete ? 'border-emerald-400' : 'border-white/80'"
                 style="box-shadow:0 0 0 999px rgba(10,30,24,.55)"></div>
        </div>
        <button type="button" x-show="cam === 'idle'" @click="start()" class="absolute inset-0 m-auto h-12 w-fit rounded-2xl bg-white px-6 text-sm font-extrabold text-brand">Mulai perekaman</button>
        <p x-show="cam === 'loading'" class="absolute inset-0 grid place-items-center text-sm font-semibold text-white">Memuat kamera…</p>
    </div>

    <p class="mt-3 rounded-2xl border px-4 py-2.5 text-sm font-semibold"
       :class="cam === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-brand/30 bg-brand-soft/60 text-brand-dark'"
       x-text="cam === 'idle' ? 'Tekan Mulai perekaman, lalu ikuti instruksi pada bingkai oval.' : (cam === 'loading' ? 'Menyiapkan kamera…' : msg)"></p>

    <section class="mt-3 rounded-3xl bg-white p-4 ring-1 ring-slate-200">
        <h2 class="text-sm font-extrabold">Petunjuk pendaftaran</h2>
        <ol class="mt-2 space-y-1.5 text-sm">
            <template x-for="(p, i) in poses" :key="i">
                <li class="flex items-center gap-2" :class="i < idx ? 'text-brand font-semibold' : (i === idx && cam === 'ready' ? 'font-bold' : 'text-slate-500')">
                    <span class="grid h-5 w-5 place-items-center rounded-full text-[11px] font-bold" :class="i < idx ? 'bg-brand text-white' : 'border border-slate-300'" x-text="i < idx ? '✓' : i + 1"></span>
                    <span x-text="p.t"></span>
                </li>
            </template>
        </ol>
        <p class="mt-3 text-xs text-slate-500">Gunakan ruangan terang, lepas masker dan kacamata hitam, dan pastikan hanya satu wajah di kamera.</p>
    </section>

    <label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-slate-600">
        <input type="checkbox" name="persetujuan" value="1" x-model="agree" class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-brand focus:ring-brand">
        <span>Saya menyetujui foto dan data biometrik wajah saya disimpan dengan aman dan hanya dipergunakan untuk keperluan verifikasi presensi kehadiran.</span>
    </label>

    <button type="submit" :disabled="!complete || !agree"
            class="mt-4 w-full rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
        Simpan &amp; Kunci Data Wajah
    </button>
    <button type="button" x-show="cam === 'done'" @click="start()" class="mt-2 w-full py-2 text-sm font-bold text-brand">Ulangi perekaman</button>
</form>
@endsection