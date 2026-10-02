@extends('layouts.pembimbing')

@section('title', 'Verifikasi Tugas Luar')

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

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            <p class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                Ada kesalahan pada input data:
            </p>
            <ul class="list-disc list-inside text-xs space-y-0.5 ml-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Halaman -->
    <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 mb-2">
            <span>🚗</span>
            <span>Fitur Tugas Luar (TL)</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Pengajuan Tugas Luar</h1>
        <p class="text-sm text-slate-500 mt-1">Tinjau penugasan di luar kantor anak binaan Anda. Peserta yang disetujui tetap dihitung <strong>Hadir</strong> penuh dalam rekap presensi.</p>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['menunggu'] }}</p>
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
                <span class="text-[11px] text-emerald-600 font-medium">Hadir Tugas Luar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ditolak</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['ditolak'] }}</p>
                <span class="text-[11px] text-rose-600 font-medium">Tidak disetujui</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                &times;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pengajuan</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Seluruh tugas luar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                📋
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('pembimbing.tugas-luar.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            <div>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama, tujuan, agenda..."
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
            </div>

            <div>
                <select name="status_verifikasi" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status_verifikasi') === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="disetujui" {{ request('status_verifikasi') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status_verifikasi') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ request('tanggal') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->anyFilled(['q', 'status_verifikasi', 'tanggal']))
                    <a href="{{ route('pembimbing.tugas-luar.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Pengajuan Tugas Luar -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Daftar Pengajuan Tugas Luar Binaan</h2>
            <span class="text-xs text-slate-500">{{ $pengajuanTugasLuar->total() }} total pengajuan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Peserta Binaan</th>
                        <th class="px-6 py-4">Tanggal & Waktu</th>
                        <th class="px-6 py-4">Tujuan / Lokasi</th>
                        <th class="px-6 py-4">Keperluan / Kegiatan</th>
                        <th class="px-6 py-4">Bukti Lampiran</th>
                        <th class="px-6 py-4">Status Verifikasi</th>
                        <th class="px-6 py-4 text-center">Aksi / Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($pengajuanTugasLuar as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Peserta -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center shrink-0">
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

                            <!-- Tanggal & Waktu -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMM Y') }}
                                </div>
                                <div class="text-[11px] text-indigo-700 font-mono font-medium mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ substr($item->waktu_mulai, 0, 5) }} s/d {{ $item->waktu_selesai ? substr($item->waktu_selesai, 0, 5) : 'Selesai' }} WITA</span>
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

                            <!-- Bukti Lampiran -->
                            <td class="px-6 py-4">
                                @if($item->bukti_url)
                                    <a href="{{ $item->bukti_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition text-[11px] shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        <span>Buka Bukti</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tidak ada file</span>
                                @endif
                            </td>

                            <!-- Status Verifikasi -->
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
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi Keputusan -->
                            <td class="px-6 py-4 text-center">
                                @if ($item->isMenunggu())
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Setujui -->
                                        <form action="{{ route('pembimbing.tugas-luar.validasi', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan tugas luar ini? Kehadiran peserta otomatis dicatat.');">
                                            @csrf
                                            <input type="hidden" name="status_verifikasi" value="disetujui">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <!-- Tombol Tolak Modal Trigger -->
                                        <button
                                            type="button"
                                            onclick="openRejectModal({{ $item->id }}, '{{ addslashes($item->nama_lengkap) }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[11px] transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Selesai Diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    📭
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Tidak ada pengajuan tugas luar</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada pengajuan tugas luar dari peserta binaan yang sesuai filter.</p>
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

<!-- Modal Form Penolakan -->
<div id="modal-reject" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100 transform transition-all">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold">⛔</span>
                Tolak Pengajuan Tugas Luar
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>

        <p class="text-xs text-slate-600">
            Anda akan menolak pengajuan Tugas Luar peserta <strong id="reject-nama-peserta">-</strong>. Silakan cantumkan alasan penolakan agar peserta dapat mengetahuinya:
        </p>

        <form id="form-reject-tugas-luar" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="status_verifikasi" value="ditolak">
            <div>
                <label for="catatan_pembimbing" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                    Catatan Penolakan (Opsional)
                </label>
                <textarea
                    name="catatan_pembimbing"
                    id="catatan_pembimbing"
                    rows="3"
                    placeholder="Contoh: Kegiatan belum mendapat konfirmasi resmi, harap lakukan presensi Onsite."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition">
                    Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id, nama) {
        document.getElementById('reject-nama-peserta').innerText = nama;
        document.getElementById('form-reject-tugas-luar').action = '/pembimbing/tugas-luar/' + id + '/validasi';
        document.getElementById('modal-reject').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('modal-reject').classList.add('hidden');
    }
</script>
@endsection
