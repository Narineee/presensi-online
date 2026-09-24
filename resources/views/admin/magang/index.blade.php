@extends('layouts.admin')

@section('title', 'Data Magang')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman & Tombol Tambah -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Anak Magang</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data profil anak magang, penempatan divisi, pembimbing, dan akun login.</p>
        </div>
        <div>
            <a href="{{ route('admin.magang.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Magang</span>
            </a>
        </div>
    </div>

    <!-- Notifikasi Sukses -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabel Data Magang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Peserta Magang</th>
                        <th class="px-6 py-4">Instansi & Jurusan</th>
                        <th class="px-6 py-4">Pembimbing & Divisi</th>
                        <th class="px-6 py-4">Akun Login</th>
                        <th class="px-6 py-4 text-center">Periode & Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($magang as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $magang->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_lengkap }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900">{{ $item->nama_lengkap }}</span>
                                            @if ($item->jenis_kelamin === 'L')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60" title="Laki-laki">L</span>
                                            @elseif ($item->jenis_kelamin === 'P')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-pink-50 text-pink-700 border border-pink-200/60" title="Perempuan">P</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">No. Induk: {{ $item->no_induk ?? '-' }}</div>
                                        @if ($item->no_hp)
                                            <div class="text-[11px] text-slate-500">HP: {{ $item->no_hp }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-semibold text-slate-800">{{ $item->instansi_pendidikan ?? '-' }}</div>
                                <div class="text-slate-500">{{ $item->jurusan ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div>
                                    <span class="text-slate-400">Pembimbing:</span>
                                    <span class="font-semibold text-slate-800 block">{{ $item->pembimbing->nama_lengkap ?? '-' }}</span>
                                </div>
                                <div class="mt-1">
                                    <span class="text-slate-400">Divisi:</span>
                                    <span class="font-semibold text-blue-600 block">{{ $item->divisi->nama_divisi ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-mono font-semibold text-slate-800">
                                    {{ $item->pengguna->username ?? '-' }}
                                </div>
                                <div class="mt-1">
                                    @if ($item->pengguna && $item->pengguna->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Non-Aktif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                <div class="font-medium text-slate-600">
                                    {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }} &ndash;
                                    {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                </div>
                                <div class="mt-1">
                                    @if ($item->status === 'aktif')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Aktif
                                        </span>
                                    @elseif ($item->status === 'selesai')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Cuti
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.magang.edit', $item->id) }}" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-200 transition" title="Edit Data Magang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.magang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data magang {{ $item->nama_lengkap }} beserta akun loginnya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition cursor-pointer" title="Hapus Data Magang">
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
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                </svg>
                                <p class="text-base font-semibold text-slate-600">Belum ada data anak magang</p>
                                <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Magang" untuk mendaftarkan peserta magang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($magang->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $magang->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
