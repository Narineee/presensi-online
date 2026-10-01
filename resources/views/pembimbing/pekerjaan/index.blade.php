@extends('layouts.pembimbing')

@section('title', 'Pekerjaan Peserta Binaan')

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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pekerjaan &amp; Proyek Binaan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola penugasan proyek dan aktivitas rutin khusus untuk masing-masing peserta magang binaan Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pembimbing.pekerjaan.create', request('magang_id') ? ['magang_id' => request('magang_id')] : []) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-purple-500/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pekerjaan Baru</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Pekerjaan -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Tugas</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</p>
            <span class="text-[11px] text-slate-400">Semua pekerjaan</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-500">Jenis Proyek</p>
            <p class="text-2xl font-black text-blue-600 mt-1">{{ $stats['proyek'] }}</p>
            <span class="text-[11px] text-blue-500 font-medium">Ada target progres</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jenis Rutin</p>
            <p class="text-2xl font-black text-slate-700 mt-1">{{ $stats['rutin'] }}</p>
            <span class="text-[11px] text-slate-400 font-medium">Tanpa progres</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">Sedang Berjalan</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['aktif'] }}</p>
            <span class="text-[11px] text-emerald-600 font-medium">Status aktif</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs col-span-2 sm:col-span-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-500">Telah Selesai</p>
            <p class="text-2xl font-black text-purple-600 mt-1">{{ $stats['selesai'] }}</p>
            <span class="text-[11px] text-purple-600 font-medium">Target tuntas</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('pembimbing.pekerjaan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            <!-- Filter Peserta Magang -->
            <div>
                <select name="magang_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Peserta Binaan</option>
                    @foreach($supervisedMagang as $m)
                        <option value="{{ $m->id }}" {{ request('magang_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_lengkap }} ({{ $m->no_induk ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenis -->
            <div>
                <select name="jenis" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Jenis Pekerjaan</option>
                    <option value="proyek" {{ request('jenis') === 'proyek' ? 'selected' : '' }}>Proyek (dengan Progress)</option>
                    <option value="rutin" {{ request('jenis') === 'rutin' ? 'selected' : '' }}>Rutin (tanpa Progress)</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif (Berjalan)</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Pencarian & Tombol -->
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul pekerjaan..."
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer shadow-xs">
                    Cari
                </button>
                @if(request()->hasAny(['magang_id', 'jenis', 'status', 'search']))
                    <a href="{{ route('pembimbing.pekerjaan.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Daftar Card Pekerjaan -->
    @if($pekerjaanList->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold mb-3">
                💼
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Pekerjaan Terdaftar</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Buat penugasan proyek atau tugas rutin untuk peserta bimbingan Anda agar mereka dapat mencatat aktivitas harian.
            </p>
            <a href="{{ route('pembimbing.pekerjaan.create', request('magang_id') ? ['magang_id' => request('magang_id')] : []) }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                + Buat Pekerjaan Pertama
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($pekerjaanList as $item)
                @php
                    $isProyek = $item->isProyek();
                    $magang = $item->magang;
                    $fotoUrl = ($magang && $magang->foto) ? asset('storage/' . $magang->foto) : '';
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-purple-300 hover:shadow-md transition p-5 flex flex-col justify-between">
                    <div>
                        <!-- Header Card: Badge Jenis & Status -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider {{ $isProyek ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                <span>{{ $isProyek ? '🚀 Proyek' : '📋 Rutin' }}</span>
                            </span>

                            @if($item->status === 'aktif')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Berjalan
                                </span>
                            @elseif($item->status === 'selesai')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    &check; Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        <!-- Judul & Deskripsi Pekerjaan -->
                        <h3 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2">
                            <a href="{{ route('pembimbing.pekerjaan.show', $item->id) }}" class="hover:text-purple-700 transition">
                                {{ $item->judul }}
                            </a>
                        </h3>

                        @if($item->deskripsi)
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                {{ $item->deskripsi }}
                            </p>
                        @endif

                        <!-- Progress Bar (Khusus Proyek) -->
                        @if($isProyek)
                            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-500">Progress Proyek:</span>
                                    <span class="text-purple-700 font-mono">{{ $item->progress ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $item->progress ?? 0 }}%"></div>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <span class="text-[11px] text-slate-400 italic">Pekerjaan rutin berulang (tanpa progress).</span>
                            </div>
                        @endif

                        <!-- Profil Pemilik (Peserta Magang Binaan) -->
                        <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
                            @if($fotoUrl)
                                <img src="{{ $fotoUrl }}" alt="{{ $magang->nama_lengkap ?? 'Peserta' }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($magang->nama_lengkap ?? 'P', 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ $magang->nama_lengkap ?? '-' }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $magang->divisi->nama_divisi ?? 'Divisi Magang' }}</p>
                            </div>
                        </div>

                        <!-- Periode & Aktivitas Info -->
                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                            <span title="Target Selesai">
                                🎯 {{ $item->target_selesai ? $item->target_selesai->format('d M Y') : 'Tanpa batas waktu' }}
                            </span>
                            <span class="text-purple-700 font-bold bg-purple-50 px-2 py-0.5 rounded">
                                {{ $item->aktivitas_count }} Aktivitas
                            </span>
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('pembimbing.pekerjaan.show', $item->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition">
                            <span>Detail &amp; Aktivitas &rarr;</span>
                        </a>

                        <div class="flex items-center gap-1">
                            <a href="{{ route('pembimbing.pekerjaan.edit', $item->id) }}" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Pekerjaan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            </a>

                            @if($item->aktivitas_count == 0)
                                <form action="{{ route('pembimbing.pekerjaan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pekerjaan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus Pekerjaan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($pekerjaanList->hasPages())
            <div class="mt-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                {{ $pekerjaanList->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
