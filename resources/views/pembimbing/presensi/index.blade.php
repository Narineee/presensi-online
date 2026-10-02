@extends('layouts.pembimbing')

@section('title', 'Riwayat Presensi Binaan')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Presensi Peserta Binaan</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Pantau rekaman presensi harian, jam masuk &amp; pulang, bukti swafoto, dan koordinat GPS peserta magang yang Anda bimbing.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('pembimbing.presensi.cetak', request()->all()) }}"
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-800 text-white text-xs font-bold shadow-sm transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                </svg>
                <span>Cetak Rekap Presensi</span>
            </a>
        </div>
    </div>

    <!-- Stats Ringkasan Hari Ini -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Hadir Hari Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Hadir Hari Ini</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">
                    {{ $statsToday['total_hadir'] }} <span class="text-xs font-semibold text-slate-400">/ {{ $statsToday['total_binaan'] }}</span>
                </p>
                <span class="text-[11px] text-emerald-600 font-medium">Binaan tercatat hadir</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                &check;
            </div>
        </div>

        <!-- Bekerja Onsite -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Bekerja Onsite</p>
                <p class="text-2xl font-black text-blue-600 mt-1">{{ $statsToday['total_onsite'] }} <span class="text-xs font-semibold text-slate-400">Orang</span></p>
                <span class="text-[11px] text-slate-400 font-medium">Di Kantor Hari Ini</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl">
                🏢
            </div>
        </div>

        <!-- Bekerja WFH -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Bekerja WFH</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $statsToday['total_wfh'] }} <span class="text-xs font-semibold text-slate-400">Orang</span></p>
                <span class="text-[11px] text-slate-400 font-medium">Dari Rumah / Remote</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl">
                🏠
            </div>
        </div>

        <!-- Selesai Jam Kerja (Sudah Pulang) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sudah Absen Pulang</p>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ $statsToday['total_sudah_pulang'] }} <span class="text-xs font-semibold text-slate-400">Orang</span></p>
                <span class="text-[11px] text-slate-400 font-medium">Jam kerja tuntas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
                🏁
            </div>
        </div>
    </div>

    <!-- Active Filter Info (Bila Memilih 1 Peserta Khusus) -->
    @if($selectedMagang)
        <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                @if($selectedMagang->foto)
                    <img src="{{ asset('storage/' . $selectedMagang->foto) }}" alt="{{ $selectedMagang->nama_lengkap }}" class="w-10 h-10 rounded-xl object-cover border border-purple-300">
                @else
                    <div class="w-10 h-10 rounded-xl bg-purple-200 text-purple-800 font-bold flex items-center justify-center text-xs">
                        {{ strtoupper(substr($selectedMagang->nama_lengkap, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="text-xs font-bold">Menampilkan Riwayat: <span class="text-purple-700 underline">{{ $selectedMagang->nama_lengkap }}</span> (NIM: {{ $selectedMagang->no_induk }})</div>
                    <div class="text-[11px] text-purple-600 mt-0.5">{{ $selectedMagang->divisi->nama_divisi ?? 'Divisi Umum' }} &bull; {{ $selectedMagang->instansi_pendidikan ?? '-' }}</div>
                </div>
            </div>
            <a href="{{ route('pembimbing.presensi.index', request()->except('magang_id')) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-purple-100 text-purple-700 text-xs font-bold border border-purple-200 transition shrink-0">
                <span>&times; Tampilkan Semua Binaan</span>
            </a>
        </div>
    @endif

    <!-- Filter Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('pembimbing.presensi.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <!-- Filter Peserta Magang -->
            <div class="md:col-span-1">
                <label for="magang_id" class="block text-xs font-bold text-slate-700 mb-1.5">Peserta Magang</label>
                <select
                    name="magang_id"
                    id="magang_id"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
                    <option value="">Semua Peserta Binaan ({{ $magangList->count() }})</option>
                    @foreach($magangList as $m)
                        <option value="{{ $m->id }}" {{ request('magang_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_lengkap }} ({{ $m->no_induk }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal Awal -->
            <div>
                <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Awal</label>
                <input
                    type="date"
                    name="tanggal_mulai"
                    id="tanggal_mulai"
                    value="{{ request('tanggal_mulai') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
            </div>

            <!-- Filter Tanggal Selesai -->
            <div>
                <label for="tanggal_akhir" class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Selesai</label>
                <input
                    type="date"
                    name="tanggal_akhir"
                    id="tanggal_akhir"
                    value="{{ request('tanggal_akhir') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
            </div>

            <!-- Mode Kerja -->
            <div>
                <label for="mode_kerja" class="block text-xs font-bold text-slate-700 mb-1.5">Mode Kerja</label>
                <select name="mode_kerja" id="mode_kerja" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Mode</option>
                    <option value="onsite" {{ request('mode_kerja') === 'onsite' ? 'selected' : '' }}>🏢 Onsite (Kantor)</option>
                    <option value="wfh" {{ request('mode_kerja') === 'wfh' ? 'selected' : '' }}>🏠 WFH (Remote)</option>
                    <option value="tugas_luar" {{ request('mode_kerja') === 'tugas_luar' ? 'selected' : '' }}>🚗 Tugas Luar (TL)</option>
                </select>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['magang_id', 'tanggal_mulai', 'tanggal_akhir', 'mode_kerja', 'status', 'tanggal']))
                    <a href="{{ route('pembimbing.presensi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Presensi Binaan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">No</th>
                        <th class="px-5 py-4">Tanggal &amp; Hari</th>
                        <th class="px-5 py-4">Peserta Magang</th>
                        <th class="px-5 py-4 text-center">Mode</th>
                        <th class="px-5 py-4 text-center">Jam Masuk</th>
                        <th class="px-5 py-4 text-center">Jam Pulang</th>
                        <th class="px-5 py-4 text-center">Bukti Foto</th>
                        <th class="px-5 py-4">Lokasi GPS</th>
                        <th class="px-5 py-4">Status &amp; Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($presensi as $index => $item)
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900 text-center">
                                {{ $presensi->firstItem() + $index }}
                            </td>

                            <!-- Tanggal & Hari -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-[11px] text-purple-700 font-semibold mt-0.5">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l') }}
                                </div>
                            </td>

                            <!-- Peserta Magang -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item->pengguna?->magang?->foto)
                                        <img src="{{ asset('storage/' . $item->pengguna->magang->foto) }}" alt="{{ $item->nama_lengkap }}" class="w-9 h-9 rounded-xl object-cover border border-purple-200 shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs shrink-0 border border-purple-200">
                                            {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->nama_lengkap }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                            NIM: {{ $item->pengguna?->magang?->no_induk ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Mode Kerja -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($item->is_tugas_luar || $item->mode_kerja === 'tugas_luar')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        🚗 Tugas Luar
                                    </span>
                                @elseif($item->mode_kerja === 'onsite')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        🏢 Onsite
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        🏠 WFH
                                    </span>
                                @endif
                            </td>

                            <!-- Jam Masuk -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($item->jam_masuk)
                                    <div class="font-mono font-bold text-slate-900">
                                        {{ substr($item->jam_masuk, 0, 5) }} <span class="text-[10px] font-normal text-slate-400">WITA</span>
                                    </div>
                                    @if(substr($item->jam_masuk, 0, 5) > '08:00')
                                        <span class="inline-block mt-0.5 text-[10px] font-semibold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-block mt-0.5 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($item->jam_keluar)
                                    <div class="font-mono font-bold text-slate-900">
                                        {{ substr($item->jam_keluar, 0, 5) }} <span class="text-[10px] font-normal text-slate-400">WITA</span>
                                    </div>
                                    <span class="inline-block mt-0.5 text-[10px] font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded">
                                        Checkout
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                        Belum Pulang
                                    </span>
                                @endif
                            </td>

                            <!-- Bukti Foto (Modal Preview) -->
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($item->foto_masuk)
                                        <button
                                            type="button"
                                            onclick="openPhotoModal('Foto Swafoto Masuk - {{ addslashes($item->nama_lengkap) }}', '{{ asset('storage/' . $item->foto_masuk) }}', 'Jam Masuk: {{ substr($item->jam_masuk, 0, 5) }} WITA ({{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }})', '{{ addslashes($item->lokasi_masuk ?? '-') }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-bold border border-blue-200 transition cursor-pointer"
                                            title="Tinjau Foto Masuk"
                                        >
                                            📸 Masuk
                                        </button>
                                    @endif

                                    @if($item->foto_keluar)
                                        <button
                                            type="button"
                                            onclick="openPhotoModal('Foto Swafoto Pulang - {{ addslashes($item->nama_lengkap) }}', '{{ asset('storage/' . $item->foto_keluar) }}', 'Jam Pulang: {{ substr($item->jam_keluar, 0, 5) }} WITA ({{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }})', '{{ addslashes($item->lokasi_keluar ?? '-') }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 text-[11px] font-bold border border-purple-200 transition cursor-pointer"
                                            title="Tinjau Foto Pulang"
                                        >
                                            📸 Pulang
                                        </button>
                                    @endif

                                    @if(! $item->foto_masuk && ! $item->foto_keluar)
                                        <span class="text-slate-400 font-mono text-xs">&mdash;</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Lokasi GPS -->
                            <td class="px-5 py-4 max-w-xs">
                                <div class="truncate text-[11px] text-slate-700 font-mono" title="{{ $item->lokasi_masuk ?? '-' }}">
                                    📍 {{ $item->lokasi_masuk ?? '-' }}
                                </div>
                                @if($item->lokasi_masuk && str_contains($item->lokasi_masuk, ','))
                                    @php
                                        $coords = trim($item->lokasi_masuk);
                                    @endphp
                                    <a
                                        href="https://www.google.com/maps?q={{ urlencode($coords) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-700 hover:text-purple-900 hover:underline mt-0.5"
                                    >
                                        Buka di Google Maps &rarr;
                                    </a>
                                @endif
                            </td>

                            <!-- Status & Keterangan -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($item->is_tugas_luar)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Hadir &bull; Tugas Luar
                                        </span>
                                    @elseif($item->status === 'hadir')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Hadir
                                        </span>
                                    @elseif(in_array($item->status, ['izin', 'sakit', 'cuti']))
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    @endif

                                    @if($item->keterangan)
                                        <span class="text-[11px] text-slate-600 max-w-xs truncate" title="{{ $item->keterangan }}">
                                            {{ $item->keterangan }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xl mb-3">
                                    📅
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada riwayat presensi yang ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter tanggal atau pilih peserta binaan lainnya.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensi->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $presensi->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Lightbox Swafoto Presensi (Terselubung/Hidden secara Default) -->
    <div
        id="photo-modal"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs hidden items-center justify-center p-4"
        onclick="if(event.target === this) closePhotoModal()"
    >
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden relative">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 id="modal-title" class="text-sm font-bold text-slate-900">Swafoto Presensi</h3>
                    <p id="modal-time" class="text-xs text-purple-700 font-medium mt-0.5"></p>
                </div>
                <button
                    type="button"
                    onclick="closePhotoModal()"
                    class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 flex flex-col items-center justify-center bg-slate-900/5">
                <img id="modal-photo-img" src="" alt="Swafoto Presensi" class="rounded-2xl max-h-80 w-auto object-contain shadow-md border-2 border-white">
            </div>

            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 text-xs text-slate-600">
                <div class="font-bold text-slate-800">Koordinat GPS / Alamat Lokasi:</div>
                <div id="modal-location" class="font-mono text-[11px] text-slate-700 mt-1 break-all"></div>
            </div>

            <div class="p-4 bg-white border-t border-slate-100 flex justify-end">
                <button
                    type="button"
                    onclick="closePhotoModal()"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Modal Script (Vanilla JavaScript, 100% kompatibel tanpa library eksternal) -->
<script>
    function openPhotoModal(title, photoUrl, timeInfo, locationInfo) {
        document.getElementById('modal-title').textContent = title || 'Swafoto Presensi';
        document.getElementById('modal-time').textContent = timeInfo || '';
        document.getElementById('modal-photo-img').src = photoUrl || '';
        document.getElementById('modal-location').textContent = locationInfo || '-';
        
        const modal = document.getElementById('photo-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePhotoModal() {
        const modal = document.getElementById('photo-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });
</script>
@endsection
