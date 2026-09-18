@extends('layouts.pembimbing')

@section('title', 'Validasi Aktivitas')

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
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Validasi Aktivitas Binaan</h1>
        <p class="text-sm text-slate-500 mt-1">Periksa dan berikan persetujuan atau catatan evaluasi terhadap log aktivitas harian peserta bimbingan Anda.</p>
    </div>

    <!-- Ringkasan Statistik Validasi -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Menunggu Validasi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                <span class="text-[11px] text-amber-600 font-medium">Perlu ditinjau</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Telah Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approve'] }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Approved</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Permintaan Revisi</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['revisi'] }}</p>
                <span class="text-[11px] text-rose-600 font-medium">Dikembalikan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                ✏️
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Binaan</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400">Seluruh aktivitas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                📊
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('pembimbing.aktivitas.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
            <div>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ request('tanggal') }}"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                >
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui (Approve)</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['tanggal', 'status']))
                    <a href="{{ route('pembimbing.aktivitas.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Validasi Aktivitas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Peserta Binaan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Uraian Aktivitas</th>
                        <th class="px-6 py-4">Progres</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Tindakan Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($aktivitas as $item)
                        <tr class="hover:bg-slate-50/70 transition">
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
                                @if($item->catatan_validasi)
                                    <div class="mt-2 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
                                        <strong>Catatan Anda:</strong> {{ $item->catatan_validasi }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap w-32">
                                <span class="font-bold text-slate-800">{{ $item->progress }}%</span>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $item->progress }}%"></div>
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
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="openModalValidasi({{ $item->id }}, '{{ addslashes($item->nama_lengkap) }}', '{{ $item->tanggal->format('d/m/Y') }}', '{{ $item->status }}', '{{ addslashes($item->catatan_validasi ?? '') }}')"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-semibold transition cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    <span>Tinjau</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Belum ada aktivitas peserta binaan pada periode ini.
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

<!-- Modal Validasi Aktivitas -->
<div id="modal-validasi" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-5 animate-scaleIn">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Validasi Aktivitas Binaan</h3>
                <p id="modal-subtitle" class="text-xs text-slate-500 mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeModalValidasi()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="form-validasi" action="" method="POST" class="space-y-4">
            @csrf

            <!-- Keputusan Status -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Keputusan Validasi <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3 rounded-2xl border-2 border-slate-200 hover:border-emerald-400 cursor-pointer transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50">
                        <input type="radio" name="status" value="approve" class="sr-only" id="status-approve">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold text-base">&check;</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Setujui</div>
                                <div class="text-[10px] text-slate-500">Approve aktivitas</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3 rounded-2xl border-2 border-slate-200 hover:border-rose-400 cursor-pointer transition has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50">
                        <input type="radio" name="status" value="revisi" class="sr-only" id="status-revisi">
                        <div class="flex items-center gap-2">
                            <span class="text-rose-600 font-bold text-base">&#8635;</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Minta Revisi</div>
                                <div class="text-[10px] text-slate-500">Perlu perbaikan</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Catatan Validasi -->
            <div>
                <label for="catatan_validasi" class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Pembimbing (Wajib jika meminta revisi)
                </label>
                <textarea
                    name="catatan_validasi"
                    id="modal-catatan"
                    rows="3"
                    placeholder="Tuliskan catatan evaluasi atau instruksi perbaikan..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                ></textarea>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalValidasi()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 transition cursor-pointer">
                    Simpan Keputusan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModalValidasi(id, nama, tanggal, status, catatan) {
    const modal = document.getElementById('modal-validasi');
    const subtitle = document.getElementById('modal-subtitle');
    const form = document.getElementById('form-validasi');
    const statusApprove = document.getElementById('status-approve');
    const statusRevisi = document.getElementById('status-revisi');
    const inputCatatan = document.getElementById('modal-catatan');

    form.action = `/pembimbing/aktivitas/${id}/validasi`;
    subtitle.textContent = `${nama} - Tanggal ${tanggal}`;

    if (status === 'approve') {
        statusApprove.checked = true;
    } else if (status === 'revisi') {
        statusRevisi.checked = true;
    } else {
        statusApprove.checked = true; // default
    }

    inputCatatan.value = catatan || '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalValidasi() {
    const modal = document.getElementById('modal-validasi');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
