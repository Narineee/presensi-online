@extends('layouts.admin')

@section('title', 'Monitoring Izin & Sakit')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Pengajuan Izin & Sakit</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau seluruh permohonan ketidakhadiran, surat dokter, dan status verifikasi Peserta Magang.</p>
        </div>
    </div>

    <!-- Stats Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Permohonan</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Seluruh riwayat</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                📑
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['disetujui'] }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Valid</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                <span class="text-[11px] text-amber-600 font-medium">Pending Review</span>
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
        <form action="{{ route('admin.izin.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 items-end">
            <!-- Filter Tanggal Awal -->
            <div>
                <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Awal</label>
                <input
                    type="date"
                    name="tanggal_mulai"
                    id="tanggal_mulai"
                    value="{{ request('tanggal_mulai') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Filter Tanggal Selesai -->
            <div>
                <label for="tanggal_akhir" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Selesai</label>
                <input
                    type="date"
                    name="tanggal_akhir"
                    id="tanggal_akhir"
                    value="{{ request('tanggal_akhir') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <!-- Jenis Izin -->
            <div>
                <label for="jenis_izin" class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Izin</label>
                <select name="jenis_izin" id="jenis_izin" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Jenis</option>
                    <option value="sakit" {{ request('jenis_izin') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="izin" {{ request('jenis_izin') === 'izin' ? 'selected' : '' }}>Izin Keperluan</option>
                    <option value="cuti" {{ request('jenis_izin') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                </select>
            </div>

            <!-- Status Approval -->
            <div>
                <label for="status_approval" class="block text-xs font-semibold text-slate-700 mb-1.5">Status Approval</label>
                <select name="status_approval" id="status_approval" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status_approval') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="disetujui" {{ request('status_approval') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status_approval') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Filter
                </button>
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_akhir', 'tanggal', 'jenis_izin', 'status_approval']))
                    <a href="{{ route('admin.izin.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Monitoring Izin & Sakit -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Peserta Magang</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Rentang Waktu</th>
                        <th class="px-6 py-4">Alasan</th>
                        <th class="px-6 py-4">Lampiran Bukti</th>
                        <th class="px-6 py-4">Status Approval</th>
                        <th class="px-6 py-4">Validator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($pengajuanIzin as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900 w-12">
                                {{ $pengajuanIzin->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $item->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $item->pengguna->magang->divisi->nama_divisi ?? '@' . ($item->pengguna->username ?? '-') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->jenis_izin === 'sakit')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        🩺 Sakit
                                    </span>
                                @elseif($item->jenis_izin === 'cuti')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        🌴 Cuti
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        📋 Izin
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 font-mono">
                                    {{ $item->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $item->tanggal_selesai->format('d/m/Y') }}
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold">
                                    ({{ $item->jumlah_hari }} Hari)
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-slate-800 line-clamp-2 leading-relaxed font-medium">{{ $item->alasan }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->bukti_file_url)
                                    <a href="{{ $item->bukti_file_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        <span>Lihat Dokumen</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status_approval === 'disetujui')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($item->status_approval === 'ditolak')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->validator)
                                    <div class="font-semibold text-slate-800">{{ $item->nama_validator }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $item->validated_at ? $item->validated_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data permohonan izin/sakit yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengajuanIzin->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pengajuanIzin->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
