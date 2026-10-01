@extends('layouts.pembimbing')

@section('title', 'Detail Pekerjaan - ' . $pekerjaan->judul)

@section('content')
<div class="space-y-6">

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pembimbing.pekerjaan.index') }}" class="text-xs font-semibold text-purple-700 hover:text-purple-900 transition">
                    &larr; Kembali ke Daftar Pekerjaan
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $pekerjaan->judul }}</h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('pembimbing.pekerjaan.edit', $pekerjaan->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-300 shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                <span>Edit Pekerjaan</span>
            </a>

            <a href="{{ route('pembimbing.aktivitas.index', ['pekerjaan_id' => $pekerjaan->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-purple-500/20 transition">
                <span>Validasi Aktivitas Masuk &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Overview Card Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Info Pekerjaan -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-extrabold uppercase tracking-wider {{ $pekerjaan->isProyek() ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ $pekerjaan->isProyek() ? '🚀 Proyek' : '📋 Rutin' }}
                    </span>

                    @if($pekerjaan->status === 'aktif')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Sedang Berjalan
                        </span>
                    @elseif($pekerjaan->status === 'selesai')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            &check; Telah Selesai
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-600">
                            Nonaktif
                        </span>
                    @endif
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Dibuat: <span class="font-mono text-slate-700 font-semibold">{{ $pekerjaan->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <!-- Progres Bar (Khusus Proyek) -->
            @if($pekerjaan->isProyek())
                <div class="p-5 rounded-2xl bg-gradient-to-r from-purple-50 via-indigo-50 to-purple-50 border border-purple-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-purple-900">
                            Progres Capaian Proyek
                        </span>
                        <span class="text-lg font-black text-purple-700 font-mono">
                            {{ $pekerjaan->progress ?? 0 }}%
                        </span>
                    </div>
                    <div class="w-full bg-purple-200/70 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pekerjaan->progress ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-purple-600 font-medium">
                        * Progres diisi dan dikendalikan oleh Anda saat melakukan validasi (review) aktivitas harian peserta.
                    </p>
                </div>
            @endif

            <!-- Deskripsi -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Deskripsi &amp; Petunjuk Tugas</h3>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                    {{ $pekerjaan->deskripsi ?: 'Tidak ada deskripsi khusus yang dicatat untuk pekerjaan ini.' }}
                </div>
            </div>

            <!-- Metadata Tanggal -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Tanggal Mulai</span>
                    <p class="text-xs font-bold text-slate-800 mt-1 font-mono">
                        {{ $pekerjaan->tanggal_mulai ? $pekerjaan->tanggal_mulai->format('d M Y') : '-' }}
                    </p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Target Selesai</span>
                    <p class="text-xs font-bold text-slate-800 mt-1 font-mono">
                        {{ $pekerjaan->target_selesai ? $pekerjaan->target_selesai->format('d M Y') : 'Tanpa batas' }}
                    </p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Total Catatan Aktivitas</span>
                    <p class="text-xs font-bold text-purple-700 mt-1">
                        {{ $aktivitas->total() }} Log Aktivitas
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Peserta Magang Penerima -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-5">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peserta Magang Penerima</h3>

            @php
                $magang = $pekerjaan->magang;
                $fotoUrl = ($magang && $magang->foto) ? asset('storage/' . $magang->foto) : '';
            @endphp

            <div class="flex items-center gap-4">
                @if($fotoUrl)
                    <img src="{{ $fotoUrl }}" alt="{{ $magang->nama_lengkap }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-purple-200 shadow-xs shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 border-2 border-purple-200 flex items-center justify-center font-extrabold text-lg shrink-0 shadow-xs">
                        {{ strtoupper(substr($magang->nama_lengkap ?? 'P', 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h4 class="text-sm font-bold text-slate-900 leading-tight">{{ $magang->nama_lengkap ?? '-' }}</h4>
                    <p class="text-xs font-mono text-slate-500 font-semibold mt-0.5">NIM: {{ $magang->no_induk ?? '-' }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 mt-1.5">
                        {{ $magang->divisi->nama_divisi ?? 'Divisi Magang' }}
                    </span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400 text-[11px]">Instansi / Kampus:</span>
                    <span class="font-semibold text-slate-800 text-right">{{ $magang->instansi_pendidikan ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400 text-[11px]">Jurusan:</span>
                    <span class="font-semibold text-slate-800 text-right">{{ $magang->jurusan ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400 text-[11px]">No. WhatsApp:</span>
                    <span class="font-mono text-slate-800 font-medium">{{ $magang->no_hp ?? '-' }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center gap-2">
                <a href="{{ route('pembimbing.pekerjaan.index', ['magang_id' => $magang->id]) }}" class="w-full text-center py-2 px-3 bg-slate-50 hover:bg-purple-50 text-purple-700 text-xs font-semibold rounded-xl border border-slate-200 transition">
                    Lihat Semua Tugas Peserta Ini &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Riwayat Aktivitas Harian di Bawah Pekerjaan Ini -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Aktivitas Terkait Pekerjaan Ini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas harian yang dilaporkan peserta magang untuk pekerjaan ini.</p>
            </div>
            <div class="inline-flex items-center px-3 py-1 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold border border-purple-200">
                Total {{ $aktivitas->total() }} Laporan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Uraian Aktivitas</th>
                        @if($pekerjaan->isProyek())
                            <th class="px-6 py-4">Progres Saat Itu</th>
                        @endif
                        <th class="px-6 py-4">Status Review</th>
                        <th class="px-6 py-4">Catatan Pembimbing</th>
                        <th class="px-6 py-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($aktivitas as $act)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $act->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 max-w-sm">
                                <p class="text-slate-800 line-clamp-3 leading-relaxed font-medium">{{ $act->isi }}</p>
                            </td>

                            @if($pekerjaan->isProyek())
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-purple-700">
                                    {{ $act->progress }}%
                                </td>
                            @endif

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($act->status === 'approve')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        &check; Disetujui
                                    </span>
                                @elseif($act->status === 'revisi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        &excl; Perlu Revisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        ⏳ Menunggu
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 max-w-xs">
                                @if($act->catatan_validasi)
                                    <div class="p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] leading-tight">
                                        {{ $act->catatan_validasi }}
                                    </div>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('pembimbing.aktivitas.index', ['tanggal' => $act->tanggal->format('Y-m-d')]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-semibold transition">
                                    <span>Buka di Validasi &rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $pekerjaan->isProyek() ? 6 : 5 }}" class="px-6 py-12 text-center text-slate-400">
                                Belum ada aktivitas harian yang dicatat oleh peserta untuk pekerjaan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($aktivitas->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $aktivitas->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
