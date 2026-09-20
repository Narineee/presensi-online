@extends('layouts.admin')

@section('title', 'Monitoring Aktivitas Harian')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Aktivitas Harian</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau seluruh catatan pekerjaan, capaian progres, dan status validasi Magang & CS.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.aktivitas.cetak', request()->all()) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456"/></svg>
                <span>Cetak Rekap Aktivitas</span>
            </a>
        </div>
    </div>

    <!-- Stats Ringkasan Aktivitas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Log Aktivitas</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }} Catatan</p>
                <span class="text-[11px] text-slate-400">Seluruh peserta</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                📝
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Telah Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approve'] }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Valid</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Validasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                <span class="text-[11px] text-amber-600 font-medium">Pending Review</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Perlu Revisi</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['revisi'] }}</p>
                <span class="text-[11px] text-rose-600 font-medium">Dikembalikan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                ✏️
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.aktivitas.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ request('tanggal') }}"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">Peran</label>
                <select name="role" id="role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Peran</option>
                    <option value="magang" {{ request('role') === 'magang' ? 'selected' : '' }}>Magang</option>
                    <option value="cs" {{ request('role') === 'cs' ? 'selected' : '' }}>CS</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>

            <div>
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Kunci</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ request('search') }}"
                    placeholder="Cari uraian..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition cursor-pointer">
                    Filter
                </button>
                @if(request()->hasAny(['tanggal', 'role', 'status', 'search']))
                    <a href="{{ route('admin.aktivitas.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Monitoring Aktivitas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Peserta</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Uraian Aktivitas</th>
                        <th class="px-6 py-4">Progres</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Validator & Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($aktivitas as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900 w-12">
                                {{ $aktivitas->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $item->nama_lengkap }}</div>
                                <div class="mt-0.5">
                                    @if($item->pengguna->role === 'magang')
                                        <span class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">Magang</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold bg-teal-50 text-teal-700">CS</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono whitespace-nowrap text-slate-700">
                                {{ $item->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 max-w-sm">
                                <p class="text-slate-800 line-clamp-3 leading-relaxed font-medium">{{ $item->isi }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap w-28">
                                <span class="font-bold text-slate-800">{{ $item->progress }}%</span>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $item->progress }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status === 'approve')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($item->status === 'revisi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Perlu Revisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                @if($item->validator)
                                    <div class="font-semibold text-slate-800 text-[11px]">{{ $item->nama_validator }}</div>
                                    @if($item->catatan_validasi)
                                        <div class="text-slate-500 text-[11px] mt-0.5 italic">"{{ $item->catatan_validasi }}"</div>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada log aktivitas yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($aktivitas->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $aktivitas->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
