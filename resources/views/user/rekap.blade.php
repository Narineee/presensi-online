@extends('layouts.user')

@section('title', 'Rekapitulasi & Cetak Dokumen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Top Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('presensi.index') }}" class="w-10 h-10 rounded-full bg-white/80 backdrop-blur-md border border-white/60 shadow-xs flex items-center justify-center text-slate-700 hover:text-blue-600 hover:bg-white transition" title="Kembali ke Presensi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Rekapitulasi &amp; Cetak Dokumen</h1>
            <p class="text-xs text-slate-500 font-medium">Pantau dan cetak rekapitulasi</p>
        </div>
    </div>

    <!-- Summary Metrics Card (Glassmorphism) -->
    <div class="rounded-3xl bg-white/75 backdrop-blur-xl border border-white/80 p-6 shadow-sm hover:shadow-md transition-all">
        <div class="grid grid-cols-3 gap-3 text-center sm:text-left">
            <div>
                <p class="text-xs font-medium text-slate-500">Kehadiran</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['persen_kehadiran'] }}%</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Jam Magang</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['jam_magang'] }}j</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $stats['total_hadir'] }} dari {{ $stats['target_hari'] }} hari</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Aktivitas</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_aktivitas'] }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Semua terisi</p>
            </div>
        </div>

        <!-- Progres Periode -->
        <div class="mt-6 pt-5 border-t border-slate-100/80">
            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                <span class="text-slate-600">Progres periode</span>
                <span class="text-emerald-600 font-bold">{{ $stats['progres_periode'] }}%</span>
            </div>
            <div class="w-full bg-slate-200/70 rounded-full h-2.5 overflow-hidden">
                <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $stats['progres_periode'] }}%"></div>
            </div>
        </div>
    </div>

    <!-- Section Riwayat Anda -->
    <div class="space-y-3">
        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Riwayat Anda</h2>

        <!-- Card 1: Rekap Presensi -->
        <a href="#modal-rekap-presensi" onclick="openModal('modal-rekap-presensi')" class="flex items-center justify-between p-4 sm:p-5 rounded-2xl bg-white/75 backdrop-blur-xl border border-white/80 hover:border-blue-300 shadow-xs hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-200/70 group-hover:bg-blue-100 text-slate-700 group-hover:text-blue-600 flex items-center justify-center transition shrink-0">
                    <!-- Face Scan Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition">Rekap Presensi</h3>
                    <p class="text-xs text-slate-500">Kehadiran, jam masuk, pulang, izin.</p>
                </div>
            </div>
            <div class="text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>

        <!-- Card 2: Rekap Aktivitas -->
        <a href="{{ route('aktivitas.index') }}" class="flex items-center justify-between p-4 sm:p-5 rounded-2xl bg-white/75 backdrop-blur-xl border border-white/80 hover:border-blue-300 shadow-xs hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-200/70 group-hover:bg-blue-100 text-slate-700 group-hover:text-blue-600 flex items-center justify-center transition shrink-0">
                    <!-- Clipboard Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition">Rekap Aktivitas</h3>
                    <p class="text-xs text-slate-500">Catatan aktivitas harian lengkap.</p>
                </div>
            </div>
            <div class="text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>

        <!-- Card 3: Lembar nilai magang -->
        <a href="{{ route('magang.penilaian.index') }}" class="flex items-center justify-between p-4 sm:p-5 rounded-2xl bg-white/75 backdrop-blur-xl border border-white/80 hover:border-blue-300 shadow-xs hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-200/70 group-hover:bg-blue-100 text-slate-700 group-hover:text-blue-600 flex items-center justify-center transition shrink-0">
                    <!-- Star Icon -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition">Lembar nilai magang</h3>
                    <p class="text-xs text-slate-500">Evaluasi pembimbing lapangan.</p>
                </div>
            </div>
            <div class="text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>
    </div>

</div>

<!-- Modal Rekap Presensi (Sesuai detail-presensi.png) -->
<div id="modal-rekap-presensi" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg rounded-3xl bg-white/95 backdrop-blur-2xl border border-white/80 p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Rekapitulasi Presensi</h3>
                <p class="text-xs text-slate-500">Filter dan cetak rekap kehadiran Anda</p>
            </div>
            <button type="button" onclick="closeModal('modal-rekap-presensi')" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form action="{{ route('presensi.cetak') }}" method="GET" target="_blank" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Awal</label>
                    <input type="date" name="tanggal_mulai" value="{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-hidden">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ \Carbon\Carbon::today()->toDateString() }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-hidden">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mode Kerja</label>
                <select name="mode_kerja" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-hidden">
                    <option value="">Semua Mode (Onsite &amp; WFH)</option>
                    <option value="onsite">Onsite (Di Kantor)</option>
                    <option value="wfh">WFH (Work From Home)</option>
                </select>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('modal-rekap-presensi')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                    </svg>
                    <span>Cetak Rekapitulasi (PDF)</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id)?.classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
    }
</script>
@endsection
