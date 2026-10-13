@php
    $fid = $formId ?? 'tl';
    $inputClasses = 'block w-full min-w-0 max-w-full box-border rounded-2xl border border-slate-300 bg-white/90 px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition';
    $minTanggalSelesai = $minTanggalSelesai ?? \App\Models\HariLibur::getBatasMundurHariKerja(3)->toDateString();
@endphp
<div class="space-y-4" x-data="{
    selesai: @js(old('tanggal_selesai', '')),
    mulai: @js(old('tanggal_mulai', '')),
    minSelesai: @js($minTanggalSelesai),
    fileName: '',
    onSelesaiChange() {
        if (this.mulai && this.selesai && this.mulai > this.selesai) {
            this.mulai = '';
        }
    }
}">
    {{-- 1. Tanggal Selesai (Wajib Dipilih Terlebih Dahulu) --}}
    <div class="space-y-1.5">
        <label for="{{ $fid }}_tanggal_selesai" class="block text-xs sm:text-sm font-bold text-slate-800">
            Tanggal Selesai Penugasan <span class="text-rose-500 font-bold">*</span>
        </label>
        <input type="date"
               id="{{ $fid }}_tanggal_selesai"
               name="tanggal_selesai"
               x-model="selesai"
               @change="onSelesaiChange()"
               :min="minSelesai"
               required
               class="{{ $inputClasses }}">
        <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed break-words">
            Batas pengajuan maksimal 3 hari kerja ke belakang (terhitung hari kerja dan libur).
        </p>
    </div>

    {{-- 2. Tanggal Mulai (Dinonaktifkan Sampai Tanggal Selesai Dipilih) --}}
    <div class="space-y-1.5">
        <label for="{{ $fid }}_tanggal_mulai"
               class="block text-xs sm:text-sm font-bold transition"
               :class="!selesai ? 'text-slate-400' : 'text-slate-800'">
            Tanggal Mulai Penugasan <span class="text-rose-500 font-bold">*</span>
        </label>
        <input type="date"
               id="{{ $fid }}_tanggal_mulai"
               name="tanggal_mulai"
               x-model="mulai"
               :disabled="!selesai"
               :max="selesai"
               required
               class="block w-full min-w-0 max-w-full box-border rounded-2xl border px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium shadow-2xs transition"
               :class="!selesai 
                   ? 'border-slate-200 bg-slate-100/90 text-slate-400 cursor-not-allowed select-none' 
                   : 'border-slate-300 bg-white/90 text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'">
        
        <p x-show="!selesai" class="text-[11px] sm:text-xs font-semibold text-amber-600 leading-relaxed break-words">
            Pilih tanggal selesai terlebih dahulu untuk mengaktifkan tanggal mulai.
        </p>
        <p x-show="selesai" x-cloak class="text-[11px] sm:text-xs text-slate-500 leading-relaxed break-words">
            Dapat memilih tanggal pelaksanaan tugas sebenarnya (maksimal tanggal selesai).
        </p>
    </div>

    {{-- 3. Tujuan/Lokasi --}}
    <div class="space-y-1.5">
        <label for="{{ $fid }}_tujuan" class="block text-xs sm:text-sm font-bold text-slate-800">
            Tujuan / Lokasi <span class="text-rose-500 font-bold">*</span>
        </label>
        <input type="text"
               id="{{ $fid }}_tujuan"
               name="tujuan"
               value="{{ old('tujuan') }}"
               maxlength="255"
               required
               placeholder="Contoh: Kantor Dinas Pendidikan"
               class="{{ $inputClasses }}">
    </div>

    {{-- 4. Keperluan/Uraian Kegiatan --}}
    <div class="space-y-1.5">
        <label for="{{ $fid }}_keperluan" class="block text-xs sm:text-sm font-bold text-slate-800">
            Keperluan / Uraian Kegiatan <span class="text-rose-500 font-bold">*</span>
        </label>
        <textarea id="{{ $fid }}_keperluan"
                  name="keperluan"
                  rows="3"
                  required
                  placeholder="Jelaskan agenda / kegiatan yang dilakukan"
                  class="{{ $inputClasses }} resize-y min-h-[88px] leading-relaxed">{{ old('keperluan') }}</textarea>
    </div>

    {{-- 5. Bukti Penugasan --}}
    <div class="space-y-1.5">
        <div class="flex items-center justify-between gap-2">
            <label for="{{ $fid }}_bukti" class="block text-xs sm:text-sm font-bold text-slate-800 truncate">
                Bukti Penugasan
            </label>
            <span class="text-[11px] font-normal text-slate-500 shrink-0">Opsional (Maks. 5 MB)</span>
        </div>
        <label for="{{ $fid }}_bukti"
               class="group flex min-h-[46px] w-full min-w-0 max-w-full box-border cursor-pointer items-center justify-between gap-2.5 rounded-2xl border border-slate-300 bg-white/90 px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-2xs transition hover:border-brand hover:bg-slate-50/80 focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20">
            <div class="flex min-w-0 flex-1 items-center gap-2.5 overflow-hidden">
                <span class="inline-flex shrink-0 items-center justify-center rounded-xl bg-brand-soft px-2.5 py-1 text-xs font-bold text-brand group-hover:bg-brand/15 transition">
                    Pilih File
                </span>
                <span class="truncate text-xs sm:text-sm font-medium"
                      :class="fileName ? 'text-slate-900 font-semibold' : 'text-slate-400'"
                      x-text="fileName ? fileName : 'Belum ada file dipilih'">
                    Belum ada file dipilih
                </span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button"
                        x-show="fileName"
                        x-cloak
                        @click.stop.prevent="fileName = ''; $refs.fileInput.value = ''"
                        class="grid h-6 w-6 place-items-center rounded-full text-slate-400 hover:bg-slate-200/70 hover:text-rose-600 transition"
                        title="Hapus file">
                    <span class="text-xs font-bold leading-none">&times;</span>
                </button>
                <svg class="h-4 w-4 text-slate-400 group-hover:text-brand transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                </svg>
            </div>
            <input type="file"
                   x-ref="fileInput"
                   id="{{ $fid }}_bukti"
                   name="bukti_tugas_luar"
                   accept=".pdf,.jpg,.jpeg,.png"
                   class="sr-only"
                   @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''">
        </label>
        <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed break-words">
            Format yang didukung: PDF, JPG, JPEG, atau PNG (maksimal 5 MB).
        </p>
    </div>
</div>