@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 shadow-2xs transition';
@endphp
<div class="space-y-3.5">
    <label class="block text-xs sm:text-sm font-bold text-slate-800">Tujuan/Lokasi
        <input type="text" name="tujuan" value="{{ old('tujuan') }}" maxlength="255" required class="{{ $in }} font-normal" placeholder="Contoh: Kantor Dinas Pendidikan">
    </label>
    <label class="block text-xs sm:text-sm font-bold text-slate-800">Keperluan/Uraian Kegiatan
        <textarea name="keperluan" rows="3" required class="{{ $in }} font-normal" placeholder="Jelaskan kegiatan yang dilakukan">{{ old('keperluan') }}</textarea>
    </label>
    <div class="grid grid-cols-2 gap-3">
        <label class="block text-xs sm:text-sm font-bold text-slate-800">Waktu mulai
            <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required class="{{ $in }} font-normal">
        </label>
        <label class="block text-xs sm:text-sm font-bold text-slate-800">Perkiraan selesai
            <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}" class="{{ $in }} font-normal">
        </label>
    </div>
    <label class="block text-xs sm:text-sm font-bold text-slate-800">Bukti penugasan <span class="font-normal text-slate-500 text-xs">(PDF/JPG/PNG, maks. 5 MB)</span>
        <input type="file" name="bukti_tugas_luar" accept=".pdf,.jpg,.jpeg,.png"
               class="{{ $in }} font-normal file:mr-3 file:rounded-xl file:border-0 file:bg-brand-soft file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand">
    </label>
</div>