{{-- Butuh: $office (array). Dipakai di dalam x-data="presensiFlow(...)". Peta & kartu lokasi disembunyikan di mode Tugas Luar. --}}
<div x-show="mode !== 'tugas_luar'" class="space-y-3">
    <div x-ref="map" class="h-60 w-full overflow-hidden rounded-3xl bg-slate-200 border border-slate-300/80 shadow-xs"></div>

    <section class="rounded-3xl glass-card p-4.5 shadow-sm">
        <h2 class="mb-3 text-sm font-extrabold text-slate-900">Lokasi terdeteksi</h2>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand shadow-2xs">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-500 font-medium truncate" x-text="mode === 'wfh' ? 'Mode kerja' : 'Titik presensi'"></p>
                    <p class="truncate text-sm font-bold text-slate-900" x-text="mode === 'wfh' ? 'WFH (tanpa batas radius)' : office.nama"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand shadow-2xs">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-500 font-medium truncate">Alamat saat ini</p>
                    <p class="text-sm font-bold leading-snug text-slate-900 break-words" x-text="loc.address"></p>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Status izin lokasi (tampil di semua mode) --}}
<section class="mt-3 flex gap-3 rounded-2xl border p-3.5 text-sm backdrop-blur-md shadow-2xs"
         :class="loc.state === 'error' || outside ? 'border-amber-300 bg-amber-50/90' : 'border-blue-200 bg-blue-50/80'">
    <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full text-[11px] font-bold text-white shadow-2xs"
          :class="loc.state === 'ok' && !outside ? 'bg-brand' : (loc.state === 'loading' ? 'bg-slate-400' : 'bg-amber-500')"
          x-text="loc.state === 'ok' && !outside ? '✓' : (loc.state === 'loading' ? '…' : '!')"></span>
    <div class="min-w-0 flex-1">
        <p class="font-bold text-slate-900" x-text="loc.state === 'ok' ? 'Izin lokasi aktif' : (loc.state === 'loading' ? 'Mendeteksi lokasi…' : 'Lokasi belum aktif')"></p>
        <p class="text-xs leading-relaxed text-slate-600 break-words">
            <template x-if="loc.state === 'error'"><span x-text="loc.msg"></span></template>
            <template x-if="loc.state === 'ok' && outside"><span x-text="'Anda berjarak ' + loc.jarak + ' m dari titik presensi (maksimal ' + office.radius + ' m). Pindah ke area kantor atau pilih mode WFH jika bekerja remote.'"></span></template>
            <template x-if="loc.state === 'ok' && !outside"><span x-text="needRadius ? 'Posisi Anda sesuai dengan area presensi. Data lokasi hanya disimpan saat presensi.' : 'Lokasi Anda terekam. Data lokasi hanya disimpan saat presensi.'"></span></template>
            <template x-if="loc.state === 'loading'"><span>Izinkan akses lokasi jika browser meminta.</span></template>
        </p>
        <button type="button" x-show="loc.state === 'error' || outside" @click="locate()" class="mt-1.5 text-xs font-bold text-brand hover:underline cursor-pointer">Coba lagi</button>
    </div>
</section>