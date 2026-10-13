@extends('layouts.mobile')
@section('title', 'Catat Aktivitas Harian')

@section('content')
@php
    $dariPresensi = request('redirect_to') === 'presensi';
    $in = 'mt-1.5 w-full rounded-2xl border border-slate-300 bg-white/90 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition shadow-2xs';
    $adaRencana = $rencanaKerjaList->isNotEmpty();
    $oldItems = old('items', [['rencana_kerja_id' => '', 'progres' => '']]);
@endphp

{{-- Header --}}
<header class="sticky-top-nav -mx-4 mb-4 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 pb-3 backdrop-blur-xl lg:mx-0 lg:rounded-2xl lg:border">
    <a href="{{ route('presensi.index') }}"
       class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 bg-white text-lg hover:bg-slate-50 transition shadow-xs" aria-label="Kembali">
        <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
    </a>
    <div class="min-w-0 flex-1">
        <h1 class="text-base font-extrabold leading-tight text-slate-900 truncate">Aktivitas Harian</h1>
        <p class="text-xs text-slate-500 font-medium truncate">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
</header>

@include('layouts.partials.mobile-alerts')

@if ($dariPresensi)
    <div class="mb-4 flex gap-3 rounded-2xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-3.5 text-sm shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-amber-500 text-[11px] font-bold text-white">!</span>
        <div class="min-w-0 flex-1">
            <p class="font-bold text-amber-950">Lengkapi sebelum pulang</p>
            <p class="text-xs text-amber-800 break-words">Presensi pulang akan terbuka setelah aktivitas hari ini disimpan.</p>
        </div>
    </div>
@endif

@unless ($adaRencana)
    <div class="mb-4 rounded-3xl border border-amber-300 bg-amber-50/90 backdrop-blur-sm p-4 text-sm text-amber-900 shadow-2xs space-y-2.5">
        <div class="flex items-start gap-2.5">
            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-amber-500 text-xs font-bold text-white">!</span>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-amber-950">Belum ada Rencana Kerja</p>
                <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">Anda belum memiliki rencana kerja aktif. Tambahkan rencana kerja mandiri terlebih dahulu untuk mulai melaporkan progres aktivitas hari ini.</p>
            </div>
        </div>
        <a href="{{ route('rencana.create', ['redirect_to' => 'aktivitas']) }}" class="btn-brand-primary block w-full rounded-2xl py-3 text-center text-xs font-extrabold text-white shadow-xs cursor-pointer">
            + Tambah Rencana Kerja Sekarang
        </a>
    </div>
@endunless

<form method="POST" action="{{ route('aktivitas.store') }}"
      x-data="{
          busy: false,
          items: {{ Js::from($oldItems) }},
          addItem() {
              this.items.push({ rencana_kerja_id: '', progres: '' });
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
    @if ($dariPresensi)<input type="hidden" name="redirect_to" value="presensi">@endif

    {{-- Tanggal Aktivitas --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm">
        <label class="block text-xs sm:text-sm font-bold text-slate-800">Tanggal Aktivitas <span class="text-rose-500">*</span>
            <input type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $in }}">
        </label>
        <p class="text-[11px] text-slate-500 mt-1.5 font-medium">Hanya laporkan rencana kerja yang benar-benar Anda kerjakan pada tanggal ini.</p>
    </section>

    {{-- Daftar Rencana Kerja & Progres Masing-Masing --}}
    <section class="rounded-3xl glass-card p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Rencana Kerja & Progres</h2>
                <p class="text-xs text-slate-500 font-medium">Pilih rencana kerja dan uraikan hasil progresnya</p>
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
                                class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer"
                                title="Hapus baris rencana kerja ini">
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
                                @disabled(! $adaRencana)
                                class="{{ $in }}">
                            <option value="">-- Pilih rencana kerja Anda --</option>
                            @foreach ($rencanaKerjaList as $rk)
                                <option value="{{ $rk->id }}">
                                    {{ $rk->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kolom Progres Khusus Rencana Tersebut --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">
                            Uraian Progres Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <textarea :name="'items[' + index + '][progres]'"
                                  x-model="item.progres"
                                  rows="3"
                                  minlength="5"
                                  required
                                  placeholder="Contoh: Menyelesaikan perancangan antarmuka sebesar 70%, membantu verifikasi berkas 5 pemohon..."
                                  class="{{ $in }}"></textarea>
                    </div>
                </div>
            </template>
        </div>

        {{-- Tombol Tambah Pilihan Rencana Kerja Lainnya --}}
        @if ($adaRencana)
            <button type="button"
                    @click="addItem()"
                    class="w-full flex items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 hover:border-brand hover:bg-slate-50 py-3 text-xs sm:text-sm font-extrabold text-slate-700 hover:text-brand transition cursor-pointer">
                <span>+ Tambah Rencana Kerja Lainnya</span>
            </button>
        @endif
    </section>

    {{-- Info Validasi --}}
    <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50/80 backdrop-blur-sm p-3.5 text-xs leading-relaxed text-slate-700 shadow-2xs">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">i</span>
        <p class="break-words"><b>Alur validasi:</b> Aktivitas yang Anda laporkan akan ditinjau oleh pembimbing lapangan. Pembimbing dapat menyetujui atau memberikan catatan revisi jika uraian progres perlu dilengkapi.</p>
    </div>

    {{-- Submit Button --}}
    <button type="submit" :disabled="busy || {{ $adaRencana ? 'false' : 'true' }}"
            class="btn-brand-primary flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[.99] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 cursor-pointer">
        <span x-text="busy ? 'Menyimpan…' : '✓ {{ $dariPresensi ? 'Simpan Aktivitas & Buka Presensi Pulang' : 'Simpan Aktivitas Harian' }}'"></span>
    </button>
</form>
@endsection