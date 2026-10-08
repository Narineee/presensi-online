@extends('layouts.mobile')
@section('title', 'Presensi Masuk')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    {{-- Samakan dengan <script> face-api yang sudah dipakai di wajah/create.blade.php Anda --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.js"></script>
    <script src="{{ asset('js/face-id.js') }}"></script>
    <script src="{{ asset('js/presensi-flow.js') }}"></script>
@endpush

@section('content')
<form method="POST" action="{{ route('presensi.masuk') }}" enctype="multipart/form-data"
      x-data="presensiFlow({
          mode: @js(old('mode_kerja', 'onsite')),
          office: @js($officeLocation),
          models: @js(asset('models')),
          verifyUrl: @js(route('presensi.verifikasi-wajah')),
          csrf: @js(csrf_token()),
      })"
      @submit="submitting = true">
    @csrf
    <input type="hidden" name="mode_kerja" :value="mode">
    <input type="hidden" name="lokasi_masuk" :value="coord">
    <input type="hidden" name="foto_masuk" :value="photo">
    <input type="hidden" name="face_descriptor" :value="descriptor">

    @include('presensi.header', ['title' => 'Presensi masuk', 'backUrl' => route('presensi.index')])

    {{-- LANGKAH 1 --}}
    <div x-show="step === 1" class="space-y-3.5">
        <div class="grid grid-cols-3 gap-2" role="tablist">
            @foreach (['onsite' => 'ONSITE', 'wfh' => 'WFH', 'tugas_luar' => 'Tugas Luar'] as $k => $label)
                <button type="button" role="tab" @click="mode = '{{ $k }}'"
                        :aria-selected="mode === '{{ $k }}'"
                        :class="mode === '{{ $k }}' ? 'border-brand bg-brand text-white shadow-sm' : 'border-slate-200/90 bg-white/80 backdrop-blur-sm text-slate-600 hover:bg-white'"
                        class="rounded-2xl border py-3 text-xs sm:text-sm font-extrabold transition cursor-pointer truncate px-1">{{ $label }}</button>
            @endforeach
        </div>

        @include('presensi.lokasi', ['office' => $officeLocation])

        {{-- Form Tugas Luar: fieldset disabled agar tidak ikut terkirim di mode lain --}}
        <fieldset x-show="mode === 'tugas_luar'" x-cloak :disabled="mode !== 'tugas_luar'" class="rounded-3xl glass-card p-4.5 shadow-sm">
            <legend class="sr-only">Rincian Tugas Luar</legend>
            <h2 class="mb-3 text-sm font-extrabold text-slate-900">Rincian Tugas Luar (TL)</h2>
            @include('presensi.form-tl')
            <p class="mt-3 text-xs text-slate-500 font-medium">Pengajuan akan diverifikasi oleh pembimbing.</p>
        </fieldset>

        <div class="pt-1">
            <button type="button" @click="next()" :disabled="!locReady"
                    class="btn-brand-primary flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
                Lanjut verifikasi wajah
            </button>
            <p class="mt-2 text-center text-xs text-slate-500 leading-tight">Dengan melanjutkan, Anda menyetujui pencatatan lokasi untuk presensi ini.</p>
        </div>
    </div>

    {{-- LANGKAH 2 --}}
    @include('presensi.wajah', ['submitLabel' => 'Simpan presensi masuk'])
</form>
@endsection