@extends('layouts.pembimbing')

@section('title', 'Validasi Aktivitas Binaan')

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
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Validasi Aktivitas Harian Binaan</h1>
        <p class="text-sm text-slate-500 mt-1">Tinjau uraian tugas peserta, berikan persetujuan, perbarui progres proyek, atau minta perbaikan revisi.</p>
    </div>

    <!-- Ringkasan Statistik Validasi -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Menunggu Validasi</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                <span class="text-[11px] text-amber-600 font-semibold">Perlu ditinjau</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Telah Disetujui</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['approve'] }}</p>
                <span class="text-[11px] text-emerald-600 font-semibold">Approved</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                &check;
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Permintaan Revisi</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $stats['revisi'] }}</p>
                <span class="text-[11px] text-rose-600 font-semibold">Dikembalikan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl">
                ✏️
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Binaan</p>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total'] }}</p>
                <span class="text-[11px] text-slate-400 font-medium">Seluruh aktivitas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
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
                    <option value="">Semua Status Validasi</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui (Approve)</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer shadow-xs">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['tanggal', 'status', 'pekerjaan_id', 'magang_id']))
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
                        <th class="px-6 py-4">Foto &amp; Peserta Magang</th>
                        <th class="px-6 py-4">Pekerjaan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Uraian Aktivitas</th>
                        <th class="px-6 py-4">Progres</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($aktivitas as $item)
                        @php
                            $magang = $item->pengguna->magang;
                            $fotoUrl = ($magang && $magang->foto) ? asset('storage/' . $magang->foto) : '';
                            $divisiNama = ($magang && $magang->divisi) ? $magang->divisi->nama_divisi : 'Peserta Magang';
                            $nomorInduk = $magang ? $magang->no_induk : ($item->pengguna->username ?? '-');
                            $pekerjaan = $item->pekerjaan;
                            $pekerjaanJudul = $pekerjaan ? $pekerjaan->judul : 'Pekerjaan Umum / Lama';
                            $pekerjaanJenis = $pekerjaan ? $pekerjaan->jenis : 'proyek';
                            $pekerjaanProgress = $pekerjaan ? ($pekerjaan->progress ?? 0) : ($item->progress ?? 0);
                            $isProyek = ($pekerjaanJenis === 'proyek');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Kolom Peserta dengan Foto Avatar -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="shrink-0">
                                        @if($fotoUrl)
                                            <img src="{{ $fotoUrl }}" alt="{{ $item->nama_lengkap }}" class="w-11 h-11 rounded-xl object-cover border-2 border-purple-200 shadow-xs">
                                        @else
                                            <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-700 border-2 border-purple-200 flex items-center justify-center font-extrabold text-xs shadow-xs">
                                                {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">
                                            {{ $item->nama_lengkap }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 font-medium">
                                            <span class="font-mono text-slate-600">NIM: {{ $nomorInduk }}</span>
                                            &bull; <span class="text-purple-700 font-semibold">{{ $divisiNama }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Pekerjaan -->
                            <td class="px-6 py-4 max-w-xs">
                                <div class="font-bold text-slate-900 text-xs leading-snug">
                                    {{ $pekerjaanJudul }}
                                </div>
                                <div class="mt-1">
                                    @if($isProyek)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            🚀 Proyek ({{ $pekerjaanProgress }}%)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            📋 Rutin
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tanggal -->
                            <td class="px-6 py-4 font-mono whitespace-nowrap text-slate-700 font-semibold">
                                {{ $item->tanggal->format('d/m/Y') }}
                            </td>

                            <!-- Uraian Aktivitas -->
                            <td class="px-6 py-4 max-w-sm">
                                <p class="text-slate-800 line-clamp-3 leading-relaxed font-medium">{{ $item->isi }}</p>
                                @if($item->catatan_validasi)
                                    <div class="mt-2 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
                                        <strong>Catatan Anda:</strong> {{ $item->catatan_validasi }}
                                    </div>
                                @endif
                            </td>

                            <!-- Progres -->
                            <td class="px-6 py-4 whitespace-nowrap w-32">
                                @if($isProyek)
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                                        <span>{{ $pekerjaanProgress }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                        <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $pekerjaanProgress }}%"></div>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs font-semibold">&mdash;</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status === 'approve')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        &check; Disetujui
                                    </span>
                                @elseif($item->status === 'revisi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        &excl; Perlu Revisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        ⏳ Menunggu
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi Validasi -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="openModalValidasi({{ $item->id }}, '{{ addslashes($item->nama_lengkap) }}', '{{ addslashes($divisiNama) }}', '{{ $fotoUrl }}', '{{ $item->tanggal->format('d/m/Y') }}', '{{ addslashes($item->isi) }}', {{ $pekerjaanProgress }}, '{{ $item->status }}', '{{ addslashes($item->catatan_validasi ?? '') }}', '{{ addslashes($pekerjaanJudul) }}', '{{ $pekerjaanJenis }}')"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-semibold transition cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    <span>Tinjau</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Belum ada log aktivitas dari peserta binaan pada periode ini.
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

<!-- Modal Validasi Aktivitas dengan Foto Peserta Lengkap -->
<div id="modal-validasi" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-5 animate-scaleIn max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Validasi Aktivitas Binaan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tinjau uraian dan tetapkan status keputusan</p>
            </div>
            <button type="button" onclick="closeModalValidasi()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Kartu Peserta Binaan dalam Modal -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3.5">
            <div id="modal-avatar-container" class="shrink-0">
                <!-- Diisi via JS (foto atau initial badge) -->
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h4 id="modal-participant-name" class="text-sm font-bold text-slate-900 truncate">-</h4>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">Magang</span>
                </div>
                <p id="modal-participant-divisi" class="text-xs text-slate-500 mt-0.5">-</p>
                <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-600">
                    <span>Tanggal: <strong id="modal-tanggal" class="font-mono text-slate-800">-</strong></span>
                </div>
            </div>
        </div>

        <!-- Kartu Pekerjaan Terkait -->
        <div class="p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100 flex items-start justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Pekerjaan Terkait:</span>
                <p id="modal-pekerjaan-judul" class="text-xs font-bold text-slate-900 mt-0.5">-</p>
            </div>
            <span id="modal-pekerjaan-badge" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase shrink-0">
                -
            </span>
        </div>

        <!-- Uraian Aktivitas Peserta -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Uraian Kegiatan Peserta
            </label>
            <div id="modal-isi-aktivitas" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed max-h-36 overflow-y-auto whitespace-pre-wrap font-medium">
                -
            </div>
        </div>

        <form id="form-validasi" action="" method="POST" class="space-y-4">
            @csrf

            <!-- Keputusan Status -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Keputusan Validasi <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3 rounded-2xl border-2 border-slate-200 hover:border-emerald-400 cursor-pointer transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50" onclick="handleStatusChange('approve')">
                        <input type="radio" name="status" value="approve" class="sr-only" id="status-approve">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold text-base">&check;</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Setujui</div>
                                <div class="text-[10px] text-slate-500">Approve aktivitas</div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center p-3 rounded-2xl border-2 border-slate-200 hover:border-rose-400 cursor-pointer transition has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/50" onclick="handleStatusChange('revisi')">
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

            <!-- Input Update Progress (Khusus Pekerjaan Proyek) -->
            <div id="modal-wrapper-proyek-progress" class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label for="modal-input-progress" class="block text-xs font-bold text-purple-900 uppercase tracking-wider">
                        Progress Pekerjaan Baru (%) <span class="text-rose-500">*</span>
                    </label>
                    <span id="modal-current-progress-text" class="text-xs font-mono font-bold text-purple-700">
                        Saat ini: 0%
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <input
                        type="number"
                        name="progress"
                        id="modal-input-progress"
                        min="0"
                        max="100"
                        step="5"
                        class="w-24 px-3 py-2 rounded-xl border border-purple-300 text-sm font-bold text-purple-900 font-mono focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                    >
                    <span class="text-xs text-purple-800 font-semibold">% (Capaian akumulatif)</span>
                </div>
                <p class="text-[11px] text-purple-600 leading-relaxed">
                    * Pembimbing mengendalikan progress proyek. Nilai 100% akan menandai pekerjaan ini selesai.
                </p>
            </div>

            <!-- Catatan Validasi -->
            <div>
                <label for="modal-catatan" class="block text-xs font-semibold text-slate-700 mb-1">
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
let currentPekerjaanJenis = 'proyek';

function handleStatusChange(status) {
    const progressWrapper = document.getElementById('modal-wrapper-proyek-progress');
    const inputProgress = document.getElementById('modal-input-progress');

    if (currentPekerjaanJenis === 'proyek' && status === 'approve') {
        progressWrapper.classList.remove('hidden');
        inputProgress.removeAttribute('disabled');
    } else {
        progressWrapper.classList.add('hidden');
        inputProgress.setAttribute('disabled', 'disabled');
    }
}

function openModalValidasi(id, nama, divisi, fotoUrl, tanggal, isi, progress, status, catatan, pekerjaanJudul, pekerjaanJenis) {
    const modal = document.getElementById('modal-validasi');
    const form = document.getElementById('form-validasi');
    const statusApprove = document.getElementById('status-approve');
    const statusRevisi = document.getElementById('status-revisi');
    const inputCatatan = document.getElementById('modal-catatan');
    const progressWrapper = document.getElementById('modal-wrapper-proyek-progress');
    const inputProgress = document.getElementById('modal-input-progress');
    const badgePekerjaan = document.getElementById('modal-pekerjaan-badge');

    currentPekerjaanJenis = pekerjaanJenis;
    form.action = `/pembimbing/aktivitas/${id}/validasi`;

    // Populate participant detail
    document.getElementById('modal-participant-name').textContent = nama;
    document.getElementById('modal-participant-divisi').textContent = divisi;
    document.getElementById('modal-tanggal').textContent = tanggal;
    document.getElementById('modal-pekerjaan-judul').textContent = pekerjaanJudul;
    document.getElementById('modal-isi-aktivitas').textContent = isi;

    // Badge pekerjaan
    if (pekerjaanJenis === 'proyek') {
        badgePekerjaan.textContent = '🚀 Proyek';
        badgePekerjaan.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase shrink-0 bg-blue-50 text-blue-700 border border-blue-200';
        document.getElementById('modal-current-progress-text').textContent = `Saat ini: ${progress}%`;
        inputProgress.value = progress;
        progressWrapper.classList.remove('hidden');
        inputProgress.removeAttribute('disabled');
    } else {
        badgePekerjaan.textContent = '📋 Rutin';
        badgePekerjaan.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase shrink-0 bg-slate-200 text-slate-700 border border-slate-300';
        progressWrapper.classList.add('hidden');
        inputProgress.setAttribute('disabled', 'disabled');
    }

    // Avatar preview
    const avatarContainer = document.getElementById('modal-avatar-container');
    if (fotoUrl) {
        avatarContainer.innerHTML = `<img src="${fotoUrl}" alt="${nama}" class="w-12 h-12 rounded-xl object-cover border-2 border-purple-200 shadow-xs">`;
    } else {
        const initials = nama.substring(0, 2).toUpperCase();
        avatarContainer.innerHTML = `<div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 border-2 border-purple-200 flex items-center justify-center font-extrabold text-sm shadow-xs">${initials}</div>`;
    }

    if (status === 'revisi') {
        statusRevisi.checked = true;
        handleStatusChange('revisi');
    } else {
        statusApprove.checked = true;
        handleStatusChange('approve');
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
