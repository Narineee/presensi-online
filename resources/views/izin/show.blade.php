@extends('layouts.user')

@section('title', 'Detail Permohonan Izin / Sakit')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Permohonan Izin</h1>
            <p class="text-sm text-slate-500 mt-1">Rincian pengajuan ketidakhadiran dan status verifikasi pembimbing.</p>
        </div>
        <a href="{{ route('izin.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
        
        <!-- Status & Jenis Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori Izin</span>
                <div class="flex items-center gap-2 mt-1">
                    @if($izin->jenis_izin === 'sakit')
                        <span class="text-xl">🩺</span>
                        <h2 class="text-lg font-bold text-rose-700">Izin Sakit</h2>
                    @elseif($izin->jenis_izin === 'cuti')
                        <span class="text-xl">🌴</span>
                        <h2 class="text-lg font-bold text-purple-700">Cuti Resmi</h2>
                    @else
                        <span class="text-xl">📋</span>
                        <h2 class="text-lg font-bold text-blue-700">Izin Keperluan</h2>
                    @endif
                </div>
            </div>

            <div>
                @if($izin->status_approval === 'disetujui')
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Disetujui Pembimbing
                    </span>
                @elseif($izin->status_approval === 'ditolak')
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Permohonan Ditolak
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Persetujuan
                    </span>
                @endif
            </div>
        </div>

        <!-- Rentang Tanggal & Durasi -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rentang Waktu</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">
                    {{ $izin->tanggal_mulai->isoFormat('D MMMM Y') }} &ndash; {{ $izin->tanggal_selesai->isoFormat('D MMMM Y') }}
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Durasi</span>
                <div class="text-sm font-bold text-blue-600 mt-0.5">
                    {{ $izin->jumlah_hari }} Hari Kalender
                </div>
            </div>
        </div>

        <!-- Alasan Izin -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Alasan / Penjelasan Ketidakhadiran
            </label>
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                {{ $izin->alasan }}
            </div>
        </div>

        <!-- Dokumen Bukti / Surat Dokter -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Dokumen Bukti / Surat Keterangan
            </label>
            @if($izin->bukti_file_url)
                @php
                    $isImage = in_array(strtolower(pathinfo($izin->bukti_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
                @endphp
                
                @if($isImage)
                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                        <img src="{{ $izin->bukti_file_url }}" alt="Dokumen Bukti" class="w-full max-h-96 object-contain">
                    </div>
                @endif

                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-between text-xs">
                    <span class="font-medium text-blue-900">File lampiran tersedia: {{ basename($izin->bukti_file) }}</span>
                    <a href="{{ $izin->bukti_file_url }}" target="_blank" download class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold">
                        <span>Buka / Unduh Berkas</span> &rarr;
                    </a>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-400 italic">
                    Tidak ada dokumen lampiran yang diunggah.
                </div>
            @endif
        </div>

        <!-- Info Validasi Pembimbing -->
        @if($izin->status_approval !== 'pending')
            <div class="p-5 rounded-2xl {{ $izin->status_approval === 'disetujui' ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200' }} border space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold {{ $izin->status_approval === 'disetujui' ? 'text-emerald-900' : 'text-rose-900' }}">
                        Diverifikasi oleh: {{ $izin->nama_validator }}
                    </span>
                    <span class="text-slate-400 font-mono">
                        {{ $izin->validated_at ? $izin->validated_at->isoFormat('D MMM Y, HH:mm') . ' WIB' : '-' }}
                    </span>
                </div>
                <p class="{{ $izin->status_approval === 'disetujui' ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ $izin->status_approval === 'disetujui' ? 'Permohonan ini telah disetujui secara resmi dan jadwal presensi telah disinkronkan.' : 'Permohonan ini ditolak oleh pembimbing Anda.' }}
                </p>
            </div>
        @endif

        <!-- Tombol Aksi Bawah -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('izin.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                &larr; Kembali ke Daftar
            </a>

            @if($izin->canBeEdited())
                <a href="{{ route('izin.edit', $izin->id) }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span>Edit Permohonan</span>
                </a>
            @endif
        </div>
    </div>

</div>
@endsection
