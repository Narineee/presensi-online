@extends('layouts.pembimbing-mobile')
@section('title', 'Detail Pekerjaan')

@section('content')
@php
    $proyek = $pekerjaan->isProyek();
    $progress = (int) ($pekerjaan->progress ?? 0);
    $magang = $pekerjaan->magang;
    $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D MMM Y') : '-';
    $statusLabel = ['aktif' => 'Sedang dikerjakan', 'selesai' => 'Selesai', 'nonaktif' => 'Nonaktif'][$pekerjaan->status] ?? $pekerjaan->status;
    $statusWarna = ['aktif' => 'bg-emerald-100 text-emerald-800', 'selesai' => 'bg-slate-200 text-slate-700', 'nonaktif' => 'bg-amber-100 text-amber-800'][$pekerjaan->status] ?? 'bg-slate-200';
    $reviewWarna = ['approve' => ['Disetujui', 'bg-emerald-100 text-emerald-800'], 'revisi' => ['Perlu revisi', 'bg-rose-100 text-rose-800'], 'pending' => ['Menunggu', 'bg-amber-100 text-amber-800']];
@endphp

<header class="-mx-4 -mt-5 mb-4 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:border">
    <a href="{{ route('pembimbing.pekerjaan.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-300 text-lg" aria-label="Kembali">←</a>
    <div class="min-w-0 flex-1">
        <h1 class="truncate text-base font-extrabold leading-tight">{{ $pekerjaan->judul }} ({{ $proyek ? 'proyek' : 'rutin' }})</h1>
        <p class="text-xs text-slate-500">Lihat Pekerjaan/Detail Aktivitas</p>
    </div>
    <a href="{{ route('pembimbing.pekerjaan.edit', $pekerjaan->id) }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-200 hover:bg-brand-soft" aria-label="Edit pekerjaan">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
    </a>
</header>

<div class="space-y-4">
    @if ($magang)
        @include('pembimbing.binaan.profil-ringkas', ['magang' => $magang])
    @endif

    {{-- Info pekerjaan --}}
    <section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-extrabold">{{ $proyek ? 'Proyek' : 'Rutin' }}</span>
            <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $statusWarna }}">{{ $statusLabel }}</span>
        </div>

        @if ($proyek)
            <div class="mt-4">
                <div class="mb-1 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Progres capaian proyek</span>
                    <span class="font-extrabold text-brand">{{ $progress }}%</span>
                </div>
                <div class="h-2.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <h2 class="mt-4 border-t border-slate-200 pt-3 text-base font-extrabold">Deskripsi/Petunjuk Tugas</h2>
        <p class="mt-2 whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm leading-relaxed ring-1 ring-slate-200">{{ $pekerjaan->deskripsi ?: 'Tidak ada deskripsi khusus untuk pekerjaan ini.' }}</p>

        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl border border-brand/30 px-3 py-2"><dt class="text-xs text-slate-500">Tanggal Mulai</dt><dd class="font-bold">{{ $tgl($pekerjaan->tanggal_mulai) }}</dd></div>
            <div class="rounded-xl border border-brand/30 px-3 py-2"><dt class="text-xs text-slate-500">Tanggal Selesai</dt><dd class="font-bold">{{ $pekerjaan->target_selesai ? $tgl($pekerjaan->target_selesai) : 'Tanpa batas' }}</dd></div>
        </dl>
    </section>

    {{-- Riwayat terkait pekerjaan ini --}}
    <section>
        <div class="mb-2 flex items-center justify-between gap-2">
            <h2 class="text-base font-extrabold">Riwayat terkait pekerjaan ini <span class="text-xs font-semibold text-slate-500">({{ $aktivitas->total() }})</span></h2>
            <a href="{{ route('pembimbing.aktivitas.index', ['pekerjaan_id' => $pekerjaan->id]) }}" class="shrink-0 rounded-full border border-slate-400 px-3 py-1 text-xs font-bold hover:bg-brand-soft">Buka validasi</a>
        </div>

        <ul class="space-y-3">
            @forelse ($aktivitas as $a)
                @php $r = $reviewWarna[$a->status] ?? $reviewWarna['pending']; @endphp
                <li class="rounded-2xl border border-brand/30 bg-white p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs text-slate-500">Tanggal</p>
                            <p class="text-sm font-extrabold">{{ $a->tanggal->locale('id')->isoFormat('D MMM Y') }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $r[1] }}">{{ $r[0] }}</span>
                    </div>

                    <p class="mt-3 text-xs text-slate-500">Uraian</p>
                    <p class="whitespace-pre-line text-sm leading-relaxed">{{ $a->isi }}</p>

                    @if ($proyek)
                        <div class="mt-3">
                            <div class="mb-1 flex justify-between text-xs"><span class="text-slate-500">Progres saat itu</span><span class="font-bold text-brand">{{ $a->progress }}%</span></div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-brand" style="width: {{ $a->progress }}%"></div></div>
                        </div>
                    @endif

                    @if ($a->catatan_validasi)
                        <p class="mt-3 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-900 ring-1 ring-amber-200"><b>Catatan:</b> {{ $a->catatan_validasi }}</p>
                    @endif

                    <a href="{{ route('pembimbing.aktivitas.index', ['pekerjaan_id' => $pekerjaan->id, 'tanggal' => $a->tanggal->format('Y-m-d')]) }}" class="mt-3 inline-block rounded-full border border-slate-400 px-3 py-1 text-xs font-bold hover:bg-brand-soft">Buka validasi</a>
                </li>
            @empty
                <li class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">Belum ada aktivitas harian untuk pekerjaan ini.</li>
            @endforelse
        </ul>

        @if ($aktivitas->hasPages())
            <nav class="mt-4 flex items-center justify-between gap-3" aria-label="Halaman riwayat">
                @if ($aktivitas->previousPageUrl())
                    <a href="{{ $aktivitas->previousPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">← Lebih baru</a>
                @else <span></span> @endif
                <span class="text-xs text-slate-500">Hal. {{ $aktivitas->currentPage() }} / {{ $aktivitas->lastPage() }}</span>
                @if ($aktivitas->nextPageUrl())
                    <a href="{{ $aktivitas->nextPageUrl() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold">Lebih lama →</a>
                @else <span></span> @endif
            </nav>
        @endif
    </section>
</div>
@endsection