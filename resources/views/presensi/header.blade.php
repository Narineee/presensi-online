{{-- Butuh: $title, $backUrl. Dipakai di dalam x-data="presensiFlow(...)" --}}
<header class="-mx-4 -mt-5 mb-4 border-b border-slate-200 bg-white px-4 pt-4">
    <div class="flex items-center gap-3 pb-3">
        <a href="{{ $backUrl }}" @click="if (step === 2) { $event.preventDefault(); back() }"
           class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-lg text-brand-ink" aria-label="Kembali">←</a>
        <div>
            <h1 class="text-base font-extrabold leading-tight" x-text="step === 1 ? '{{ $title }}' : 'Verifikasi Wajah'"></h1>
            <p class="text-xs text-slate-500" x-text="'Langkah ' + step + ' dari 2'"></p>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-1">
        <div class="h-1 rounded-full bg-brand"></div>
        <div class="h-1 rounded-full transition-colors" :class="step === 2 ? 'bg-brand' : 'bg-slate-200'"></div>
    </div>
</header>