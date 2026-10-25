@extends('layouts.mobile')
@section('title', 'Edit Aktivitas')

@section('content')
@php
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
    $adaRencana = $rencanaKerjaList->isNotEmpty();
    $normalizeProgres = function ($p) {
        $pLower = strtolower(trim((string) $p));
        if ($pLower === 'selesai') {
            return 'Selesai';
        }
        if (in_array($pLower, ['belum', 'belum selesai', 'proses', 'berjalan', 'aktif'])) {
            return 'Belum';
        }
        return !empty($p) ? (stripos($pLower, 'selesai') !== false ? 'Selesai' : 'Belum') : '';
    };

    $existingItems = $aktivitas->details->map(fn($d) => [
        'rencana_kerja_id' => (string) $d->rencana_kerja_id,
        'progres' => $normalizeProgres($d->progres),
        'alasan' => (string) ($d->alasan ?? ''),
    ])->values()->toArray();
    if (empty($existingItems)) {
        $existingItems = [['rencana_kerja_id' => '', 'progres' => $normalizeProgres($aktivitas->isi), 'alasan' => '']];
    }
    $oldItems = old('items', $existingItems);
@endphp

<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Edit Aktivitas</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ $aktivitas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

@if ($aktivitas->status === 'revisi')
    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-4 text-sm text-amber-900 shadow-2xs">
        <p class="font-bold">Catatan revisi dari pembimbing</p>
        <p class="mt-1 rounded-xl bg-white/80 p-3 text-xs leading-relaxed border border-amber-200 break-words">{{ $aktivitas->catatan_validasi ?? 'Harap melengkapi uraian progres aktivitas Anda.' }}</p>
        <p class="mt-2 text-[11px] text-amber-800">Setelah disimpan, status kembali menjadi <b>Pending</b> untuk ditinjau ulang oleh pembimbing.</p>
    </div>
@endif

<form method="POST" action="{{ route('aktivitas.update', $aktivitas->id) }}"
      x-data="{
          busy: false,
          items: {{ Js::from($oldItems) }},
          addItem() {
              this.items.push({ rencana_kerja_id: '', progres: '', alasan: '' });
          },
          removeItem(index) {
              if (this.items.length > 1) {
                  this.items.splice(index, 1);
              }
          }
      }"
      @submit="busy = true"
      class="space-y-4">
    @csrf
    @method('PUT')

    {{-- Tanggal Aktivitas --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <label class="block text-xs sm:text-sm font-bold text-slate-800">Tanggal Aktivitas <span class="text-rose-500">*</span>
            <input type="date" name="tanggal" value="{{ old('tanggal', $aktivitas->tanggal->format('Y-m-d')) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
        </label>
    </section>

    {{-- Daftar Rencana Kerja & Progres --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Rencana Kerja & Progres</h2>
                <p class="text-xs text-slate-500 font-medium">Sesuaikan rencana kerja dan status progres yang dikerjakan</p>
            </div>
            <a href="{{ route('rencana.create', ['redirect_to' => 'aktivitas']) }}" class="text-xs font-bold text-brand hover:underline shrink-0">
                + Rencana Baru
            </a>
        </div>

        <div class="space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-2xl glass-subcard p-4 border border-slate-200/80 shadow-2xs space-y-3 relative">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-brand/10 text-brand text-xs font-extrabold" x-text="'Rencana Kerja #' + (index + 1)"></span>
                        <button type="button"
                                @click="removeItem(index)"
                                x-show="items.length > 1"
                                class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer">
                            ✕ Hapus
                        </button>
                    </div>

                    {{-- Dropdown Rencana Kerja --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">
                            Pilih Rencana Kerja <span class="text-rose-500">*</span>
                        </label>
                        <select :name="'items[' + index + '][rencana_kerja_id]'"
                                x-model="item.rencana_kerja_id"
                                required
                                class="{{ $in }}">
                            <option value="">-- Pilih rencana kerja Anda --</option>
                            @foreach ($rencanaKerjaList as $rk)
                                <option value="{{ $rk->id }}">
                                    {{ $rk->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Pilihan Selesai / Belum --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">
                            Status Progres <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label class="flex items-center justify-center gap-2 rounded-2xl border px-4 py-3 text-sm font-extrabold cursor-pointer transition select-none active:scale-[0.99]"
                                   :class="item.progres === 'Selesai'
                                       ? 'border-emerald-500 bg-emerald-500 text-white shadow-xs'
                                       : 'border-slate-300 bg-white/90 text-slate-700 hover:bg-slate-50 hover:border-slate-400'">
                                <input type="radio"
                                       :name="'items[' + index + '][progres]'"
                                       value="Selesai"
                                       x-model="item.progres"
                                       required
                                       class="sr-only">
                                <span>✓ Selesai</span>
                            </label>

                            <label class="flex items-center justify-center gap-2 rounded-2xl border px-4 py-3 text-sm font-extrabold cursor-pointer transition select-none active:scale-[0.99]"
                                   :class="item.progres === 'Belum'
                                       ? 'border-amber-500 bg-amber-500 text-white shadow-xs'
                                       : 'border-slate-300 bg-white/90 text-slate-700 hover:bg-slate-50 hover:border-slate-400'">
                                <input type="radio"
                                       :name="'items[' + index + '][progres]'"
                                       value="Belum"
                                       x-model="item.progres"
                                       required
                                       class="sr-only">
                                <span>⏳ Belum</span>
                            </label>
                        </div>
                    </div>

                    {{-- Input Alasan Jika Belum Selesai --}}
                    <div x-show="item.progres === 'Belum'" x-cloak class="space-y-1.5 pt-1">
                        <label class="block text-xs font-bold text-amber-950">
                            Alasan Belum Selesai <span class="text-rose-500">*</span>
                        </label>
                        <textarea :name="'items[' + index + '][alasan]'"
                                  x-model="item.alasan"
                                  :required="item.progres === 'Belum'"
                                  :disabled="item.progres !== 'Belum'"
                                  rows="2"
                                  placeholder="Tuliskan kendala atau alasan mengapa rencana kerja ini belum selesai..."
                                  class="{{ $in }}"></textarea>
                        <p class="text-[11px] text-amber-800 font-medium">Wajib diisi untuk menjelaskan alasan atau kendala progres.</p>
                    </div>
                </div>
            </template>
        </div>

        <button type="button"
                @click="addItem()"
                class="w-full flex items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 hover:border-brand hover:bg-slate-50 py-3 text-xs sm:text-sm font-extrabold text-slate-700 hover:text-brand transition cursor-pointer">
            <span>+ Tambah Rencana Kerja Lainnya</span>
        </button>
    </section>

    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ route('aktivitas.show', $aktivitas->id) }}" class="rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 py-3.5 text-center text-sm font-extrabold text-slate-700 shadow-xs transition">Batal</a>
        <button type="submit" :disabled="busy" class="btn-brand-primary rounded-2xl py-3.5 text-sm font-extrabold text-white shadow-sm disabled:bg-slate-300 cursor-pointer">
            <span x-text="busy ? 'Menyimpan…' : 'Simpan Perubahan'"></span>
        </button>
    </div>
</form>
@endsection