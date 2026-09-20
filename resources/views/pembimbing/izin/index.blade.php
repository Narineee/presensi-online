@extends('layouts.pembimbing')

@section('title', 'Verifikasi Izin & Sakit')

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

    <!-- Header Halaman -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Permohonan Izin & Sakit</h1>
        <p class="text-sm text-slate-500 mt-1">Tinjau surat dokter dan berkas pendukung ketidakhadiran peserta binaan Anda.</p>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                <span class="text-[11px] text-amber-600 font-medium">Perlu tindakan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Telah Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['disetujui'] }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Otomatis sinkron</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
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

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Masuk</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Seluruh permohonan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                📑
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('pembimbing.izin.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
            <div>
                <select name="jenis_izin" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Jenis Izin</option>
                    <option value="sakit" {{ request('jenis_izin') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="izin" {{ request('jenis_izin') === 'izin' ? 'selected' : '' }}>Izin Keperluan</option>
                    <option value="cuti" {{ request('jenis_izin') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                </select>
            </div>

            <div>
                <select name="status_approval" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Status Approval</option>
                    <option value="pending" {{ request('status_approval') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="disetujui" {{ request('status_approval') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status_approval') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['jenis_izin', 'status_approval']))
                    <a href="{{ route('pembimbing.izin.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Pengajuan Izin -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Peserta Binaan</th>
                        <th class="px-6 py-4">Jenis Izin</th>
                        <th class="px-6 py-4">Rentang Waktu</th>
                        <th class="px-6 py-4">Alasan</th>
                        <th class="px-6 py-4">Bukti Lampiran</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($pengajuanIzin as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $role = $item->pengguna->role ?? 'magang';
                                    $magang = $item->pengguna->magang;
                                    $cs = $item->pengguna->cs;
                                    $fotoUrl = ($role === 'magang' && $magang && $magang->foto) ? asset('storage/' . $magang->foto) : '';
                                    $divisiNama = ($role === 'magang' && $magang && $magang->divisi) ? $magang->divisi->nama_divisi : ($cs ? ($cs->jabatan ?? 'CS') : '-');
                                    $nomorInduk = ($role === 'magang' && $magang) ? $magang->no_induk : ($cs ? $cs->nik : '-');
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        @if($fotoUrl)
                                            <img src="{{ $fotoUrl }}" alt="{{ $item->nama_lengkap }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                                        @else
                                            <div class="w-10 h-10 rounded-xl {{ $role === 'magang' ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }} flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white flex items-center justify-center text-[8px] font-bold {{ $role === 'magang' ? 'bg-amber-500 text-white' : 'bg-teal-500 text-white' }}">
                                            {{ $role === 'magang' ? 'M' : 'C' }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $item->nama_lengkap }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5 font-medium">
                                            @if($role === 'magang')
                                                <span class="inline-flex px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">Magang</span>
                                                <span class="font-mono text-slate-600">{{ $nomorInduk }}</span>
                                                &bull; <span class="text-purple-700 font-medium">{{ $divisiNama }}</span>
                                            @else
                                                <span class="inline-flex px-1.5 py-0.2 rounded text-[10px] font-semibold bg-teal-50 text-teal-700">CS</span>
                                                <span class="font-mono text-slate-600">{{ $nomorInduk }}</span>
                                                &bull; <span class="text-teal-700 font-medium">{{ $divisiNama }}</span>
                                            @endif
                                        </div>
                                    </div>
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
                                    <a href="{{ $item->bukti_file_url }}" target="_blank" class="inline-flex items-center gap-1 text-purple-600 hover:underline font-bold text-xs">
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
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($item->status_approval === 'pending')
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Setujui -->
                                        <form action="{{ route('pembimbing.izin.validasi', $item->id) }}" method="POST" onsubmit="return confirm('Setujui permohonan {{ $item->jenis_izin }} atas nama {{ $item->nama_lengkap }}? Kehadiran akan otomatis sinkron ke presensi.');">
                                            @csrf
                                            <input type="hidden" name="status_approval" value="disetujui">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer" title="Setujui">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <!-- Tombol Tolak -->
                                        <form action="{{ route('pembimbing.izin.validasi', $item->id) }}" method="POST" onsubmit="return confirm('Tolak permohonan {{ $item->jenis_izin }} atas nama {{ $item->nama_lengkap }}?');">
                                            @csrf
                                            <input type="hidden" name="status_approval" value="ditolak">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition cursor-pointer" title="Tolak">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Tolak</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Selesai diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada permohonan izin/sakit binaan yang sesuai filter.
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
