{{-- Butuh: $submitLabel. Dipakai di dalam x-data="presensiFlow(...)" --}}
<div x-show="step === 2" x-cloak class="space-y-3">
    <div class="relative mx-auto aspect-[3/4] w-full overflow-hidden rounded-3xl bg-slate-800">
        <video x-ref="video" playsinline muted class="absolute inset-0 h-full w-full -scale-x-100 object-cover"></video>
        <div class="absolute inset-0 grid place-items-center">
            <div class="h-[76%] w-[60%] rounded-[50%] border-[3px] transition-colors"
                 :class="verified ? 'border-emerald-400' : 'border-white/80'"
                 style="box-shadow:0 0 0 999px rgba(10,30,24,.55)"></div>
        </div>
        <p x-show="cam === 'loading'" class="absolute inset-0 grid place-items-center text-sm font-semibold text-white">Memuat kamera…</p>
        <p x-show="cam === 'ready'" x-text="faceMsg" class="absolute inset-x-3 bottom-3 rounded-xl bg-black/55 px-3 py-2 text-center text-xs font-semibold text-white"></p>
    </div>

    <section class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-extrabold">Pemeriksaan liveness</h2>
            <span class="rounded-full bg-brand-soft px-2.5 py-1 text-xs font-bold text-brand" x-text="done + '/3 selesai'"></span>
        </div>
        <ul class="space-y-2.5">
            <template x-for="c in checks" :key="c.t">
                <li class="flex items-center gap-3 text-sm">
                    <span class="grid h-6 w-6 place-items-center rounded-full text-xs font-bold transition-colors"
                          :class="c.ok ? 'bg-brand text-white' : 'border border-slate-300 text-transparent'">✓</span>
                    <span :class="c.ok ? 'font-semibold' : 'text-slate-600'" x-text="c.t"></span>
                </li>
            </template>
        </ul>
    </section>

    {{-- Hasil verifikasi --}}
    <section x-show="verified" class="flex gap-3 rounded-2xl border border-brand/30 bg-brand-soft/60 p-3.5 text-sm">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">✓</span>
        <div>
            <p class="font-bold">Verifikasi berhasil</p>
            <p class="text-xs text-slate-600">Wajah cocok dan liveness terkonfirmasi. Presensi siap disimpan.</p>
        </div>
    </section>
    <section x-show="(cam === 'done' && !verified) || cam === 'error'" class="rounded-2xl border border-red-200 bg-red-50 p-3.5 text-sm">
        <p class="font-bold text-red-800">Verifikasi belum berhasil</p>
        <p class="text-xs text-red-700" x-text="faceMsg"></p>
        <button type="button" @click="retry()" class="mt-1.5 text-xs font-bold text-red-800 underline">Ulangi verifikasi</button>
    </section>

    <button type="submit" :disabled="!verified || submitting"
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
        <span x-text="submitting ? 'Menyimpan…' : '{{ $submitLabel }}'"></span>
    </button>
</div>