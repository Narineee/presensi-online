@extends('layouts.user')

@section('title', 'Detail Aktivitas Harian')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Aktivitas Harian</h1>
            <p class="text-sm text-slate-500 mt-1">Rincian catatan pekerjaan dan status evaluasi dari Pembimbing.</p>
        </div>
        <a href="{{ route('aktivitas.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
        
        <!-- Header Info -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Pelaksanaan</span>
                <h2 class="text-lg font-bold text-slate-900 mt-0.5">
                    {{ $aktivitas->tanggal->isoFormat('dddd, D MMMM Y') }}
                </h2>
            </div>

            <div>
                @if($aktivitas->status === 'approve')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Disetujui (Approved)
                    </span>
                @elseif($aktivitas->status === 'revisi')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Perlu Revisi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Validasi
                    </span>
                @endif
            </div>
        </div>

        <!-- Progres Bar -->
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                <span>Capaian Progres Pekerjaan</span>
                <span class="text-blue-600 font-bold">{{ $aktivitas->progress }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $aktivitas->progress }}%"></div>
            </div>
        </div>

        <!-- Uraian Aktivitas -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Uraian Aktivitas yang Dikerjakan
            </label>
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                {{ $aktivitas->isi }}
            </div>
        </div>

        <!-- Bagian Feedback Pembimbing -->
        <div class="pt-6 border-t border-slate-100 space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Informasi Validasi Pembimbing
            </label>

            @if($aktivitas->status !== 'pending')
                <div class="p-5 rounded-2xl {{ $aktivitas->status === 'approve' ? 'bg-emerald-50/70 border-emerald-200' : 'bg-amber-50/70 border-amber-200' }} border space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold {{ $aktivitas->status === 'approve' ? 'text-emerald-900' : 'text-amber-900' }}">
                            Divalidasi oleh: {{ $aktivitas->nama_validator }}
                        </span>
                        <span class="text-slate-400 font-mono text-[11px]">
                            {{ $aktivitas->validated_at ? $aktivitas->validated_at->isoFormat('D MMM Y, HH:mm') . ' WIB' : '-' }}
                        </span>
                    </div>

                    @if($aktivitas->catatan_validasi)
                        <div class="text-xs {{ $aktivitas->status === 'approve' ? 'text-emerald-800' : 'text-amber-800' }} leading-relaxed pt-2 border-t {{ $aktivitas->status === 'approve' ? 'border-emerald-200/60' : 'border-amber-200/60' }}">
                            <strong>Catatan Feedback:</strong>
                            <p class="mt-1">{{ $aktivitas->catatan_validasi }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center text-xs text-slate-400">
                    Aktivitas ini belum divalidasi oleh Pembimbing. Silakan menunggu tinjauan dari pembimbing Anda.
                </div>
            @endif
        </div>

        <!-- Tombol Aksi Bawah -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('aktivitas.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                &larr; Kembali ke Daftar
            </a>

            @if($aktivitas->canBeEdited())
                <a href="{{ route('aktivitas.edit', $aktivitas->id) }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span>Edit Aktivitas Ini</span>
                </a>
            @endif
        </div>
    </div>

</div>
@endsection
