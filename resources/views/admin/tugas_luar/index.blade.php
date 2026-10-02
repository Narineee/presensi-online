@extends('layouts.admin')

@section('title', 'Monitoring Pengajuan Tugas Luar')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 mb-2">
                <span>🚗</span>
                <span>Tugas Luar (TL)</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Pengajuan Tugas Luar</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau seluruh permohonan penugasan di luar kantor, agenda kegiatan, bukti lampiran, dan status verifikasi pembimbing.</p>
        </div>
    </div>

    <!-- Stats Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pengajuan</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Seluruh tugas luar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                📋
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Telah Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['disetujui'] }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Hadir Tugas Luar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['menunggu'] }}</p>
                <span class="text-[11px] text-amber-600 font-medium">Pending Pembimbing</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditolak</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['ditolak'] }}</p>
                <span class="text-[11px] text-rose-600 font-medium">Dibatalkan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                &times;
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.tugas-luar.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <!-- Filter Pencarian -->
            <div>
                <label for="q" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Kunci</label>
                <input
                    type="text"
                    name="q"
                    id="q"
                    value="{{ request('q') }}"
                    placeholder="Nama peserta, tujuan..."
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Filter Tanggal -->
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ request('tanggal') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Filter Status -->
            <div>
                <label for="status_verifikasi" class="block text-xs font-semibold text-slate-700 mb-1.5">Status Verifikasi</label>
                <select name="status_verifikasi" id="status_verifikasi" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status_verifikasi') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status_verifikasi') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status_verifikasi') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Filter Divisi -->
            <div>
                <label for="divisi_id" class="block text-xs font-semibold text-slate-700 mb-1.5">Divisi</label>
                <select name="divisi_id" id="divisi_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Divisi</option>
                    @foreach($divisiList as $divisi)
                        <option value="{{ $divisi->id }}" {{ request('divisi_id') == $divisi->id ? 'selected' : '' }}>
                            {{ $divisi->nama_divisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                    Filter
                </button>
                @if(request()->anyFilled(['q', 'tanggal', 'status_verifikasi', 'divisi_id']))
                    <a href="{{ route('admin.tugas-luar.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Monitoring Tugas Luar -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Riwayat Seluruh Tugas Luar</h2>
            <span class="text-xs text-slate-500">{{ $pengajuanTugasLuar->total() }} data ditemukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Peserta Magang</th>
                        <th class="px-6 py-4">Tanggal & Jam</th>
                        <th class="px-6 py-4">Tujuan / Lokasi</th>
                        <th class="px-6 py-4">Keperluan / Agenda</th>
                        <th class="px-6 py-4">Bukti Lampiran</th>
                        <th class="px-6 py-4">Status & Pembimbing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($pengajuanTugasLuar as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Peserta -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0">
                                        {{ substr($item->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->nama_lengkap }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $item->magang?->divisi?->nama_divisi ?? 'Tanpa Divisi' }} &bull; {{ $item->magang?->no_induk ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Tanggal & Jam -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMM Y') }}
                                </div>
                                <div class="text-[11px] text-indigo-700 font-mono font-medium mt-0.5">
                                    {{ substr($item->waktu_mulai, 0, 5) }} - {{ $item->waktu_selesai ? substr($item->waktu_selesai, 0, 5) : 'Selesai' }} WITA
                                </div>
                            </td>

                            <!-- Tujuan -->
                            <td class="px-6 py-4 font-semibold text-slate-900 max-w-xs">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    <span>{{ $item->tujuan }}</span>
                                </div>
                            </td>

                            <!-- Keperluan -->
                            <td class="px-6 py-4 text-slate-600 max-w-xs leading-relaxed">
                                {{ $item->keperluan }}
                            </td>

                            <!-- Bukti -->
                            <td class="px-6 py-4">
                                @if($item->bukti_url)
                                    <a href="{{ $item->bukti_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        <span>Buka Dokumen</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tidak ada berkas</span>
                                @endif
                            </td>

                            <!-- Status & Pembimbing -->
                            <td class="px-6 py-4">
                                @if ($item->status_verifikasi === 'disetujui')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Disetujui
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Oleh: {{ $item->nama_validator }}
                                    </div>
                                @elseif ($item->status_verifikasi === 'ditolak')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                    @if($item->catatan_pembimbing)
                                        <div class="text-[10px] text-rose-600 mt-1 max-w-[150px] truncate" title="{{ $item->catatan_pembimbing }}">
                                            {{ $item->catatan_pembimbing }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Menunggu Review
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    📭
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Tidak ada data pengajuan tugas luar</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada peserta yang mengajukan tugas luar sesuai filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengajuanTugasLuar->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $pengajuanTugasLuar->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
