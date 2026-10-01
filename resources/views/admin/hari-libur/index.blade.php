@extends('layouts.admin')

@section('title', 'Master Data Hari Libur & Cuti Bersama')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Master Data Hari Libur &amp; Cuti Bersama</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola tanggal merah nasional dan cuti bersama yang dikecualikan dari kewajiban jam kerja presensi magang.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.hari-libur.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-rose-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Libur Manual</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Card Sinkronisasi API Indonesia -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 sm:p-6 text-white shadow-lg border border-slate-700/50">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="space-y-1.5 max-w-xl">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isApiConfigured ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isApiConfigured ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                        {{ $isApiConfigured ? 'API Indonesia Terhubung' : 'API Key Belum Diisi' }}
                    </span>
                    <span class="text-xs text-slate-300">Endpoint: <code class="text-slate-200 font-mono text-[11px]">/api/v1/libur</code></span>
                </div>
                <h2 class="text-lg font-bold tracking-tight text-white">Sinkronisasi Otomatis dari API Indonesia</h2>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Ambil jadwal libur nasional dan cuti bersama resmi pemerintah. Data manual yang Anda tambahkan tidak akan tertimpa atau terhapus saat proses sinkronisasi.
                </p>
            </div>

            <!-- Form Sync Berdasarkan Tahun -->
            <form action="{{ route('admin.hari-libur.sync') }}" method="POST" class="flex flex-wrap sm:flex-nowrap items-center gap-3 bg-white/10 p-2.5 rounded-xl border border-white/10 backdrop-blur-sm">
                @csrf
                <div class="flex items-center gap-2">
                    <label for="sync_tahun" class="text-xs font-semibold text-slate-200 whitespace-nowrap">Tahun:</label>
                    <select
                        name="tahun"
                        id="sync_tahun"
                        class="bg-slate-900/90 text-white border border-slate-600 rounded-lg px-3 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-rose-500"
                    >
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ ($selectedYear == $year) ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg shadow transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Sinkronkan dari API</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Libur -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Tanggal Merah</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalLibur }}</p>
                <span class="text-[11px] text-slate-500 mt-0.5 inline-block">
                    {{ $selectedYear ? "Tahun {$selectedYear}" : 'Semua Tahun' }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Libur Nasional -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Libur Nasional</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalNasional }}</p>
                <span class="text-[11px] text-slate-500 mt-0.5 inline-block">Hari Libur Resmi</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                NAS
            </div>
        </div>

        <!-- Card 3: Cuti Bersama -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cuti Bersama</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $totalCuti }}</p>
                <span class="text-[11px] text-slate-500 mt-0.5 inline-block">Dispensasi Pemerintah</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">
                CUTI
            </div>
        </div>

        <!-- Card 4: Sumber Data -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Komposisi Sumber</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm font-bold text-emerald-600">{{ $totalApi }} API</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-sm font-bold text-slate-700">{{ $totalManual }} Manual</span>
                </div>
                <span class="text-[11px] text-slate-400 mt-0.5 inline-block">Database Lokal</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Tabel Data -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Form Filter & Pencarian -->
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50">
            <form action="{{ route('admin.hari-libur.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Filter Tahun -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                    <select
                        name="tahun"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-white rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                    >
                        <option value="semua" {{ $selectedYear === null ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Jenis -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Libur</label>
                    <select
                        name="jenis"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-white rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                    >
                        <option value="">Semua Jenis</option>
                        <option value="Hari Libur Nasional" {{ request('jenis') === 'Hari Libur Nasional' ? 'selected' : '' }}>Hari Libur Nasional</option>
                        <option value="Cuti Bersama" {{ request('jenis') === 'Cuti Bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                    </select>
                </div>

                <!-- Filter Sumber -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sumber Data</label>
                    <select
                        name="sumber"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-white rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                    >
                        <option value="">Semua Sumber</option>
                        <option value="api" {{ request('sumber') === 'api' ? 'selected' : '' }}>API Indonesia</option>
                        <option value="manual" {{ request('sumber') === 'manual' ? 'selected' : '' }}>Manual Admin</option>
                    </select>
                </div>

                <!-- Cari Nama / Keterangan -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Keterangan</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari nama hari libur..."
                                class="w-full pl-9 pr-3 py-2 bg-white rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                            >
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </span>
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition">
                            Cari
                        </button>
                        @if(request()->anyFilled(['search', 'jenis', 'sumber']) || ($selectedYear !== (int) now()->format('Y') && request()->has('tahun')))
                            <a href="{{ route('admin.hari-libur.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-200 rounded-xl transition" title="Reset Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Hari Libur -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 w-48">Tanggal &amp; Hari</th>
                        <th class="py-3.5 px-4">Nama Hari Libur</th>
                        <th class="py-3.5 px-4 w-36">Jenis</th>
                        <th class="py-3.5 px-4 w-28 text-center">Sumber</th>
                        <th class="py-3.5 px-4 text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($hariLiburList as $index => $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 text-center font-semibold text-slate-400">
                                {{ $hariLiburList->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">
                                    {{ $item->tanggal ? $item->tanggal->translatedFormat('d F Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-rose-600 font-semibold">
                                    {{ $item->tanggal ? $item->tanggal->translatedFormat('l') : '' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $item->nama ?: $item->keterangan }}
                                </div>
                                @if($item->keterangan && $item->keterangan !== $item->nama)
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $item->keterangan }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->jenis === 'Cuti Bersama')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Cuti Bersama
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Hari Libur Nasional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($item->sumber === 'api')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        API
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Manual
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.hari-libur.edit', $item->id) }}"
                                        class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition"
                                        title="Edit Libur"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('admin.hari-libur.destroy', $item->id) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data libur: {{ addslashes($item->nama ?: $item->keterangan) }}?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                            title="Hapus Libur"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak Ada Data Hari Libur</h3>
                                    <p class="text-xs text-slate-500">
                                        Belum ada hari libur untuk filter yang dipilih. Anda dapat melakukan sinkronisasi otomatis dari API atau menambahkannya secara manual.
                                    </p>
                                    <div class="pt-2 flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.hari-libur.create') }}" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold transition">
                                            + Tambah Manual
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($hariLiburList->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $hariLiburList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
