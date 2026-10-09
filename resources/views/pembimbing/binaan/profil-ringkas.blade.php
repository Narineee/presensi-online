{{-- Butuh: $magang --}}
@php
    $aktif = $magang->status === 'aktif';
    $mulai = $magang->tanggal_mulai?->locale('id')->isoFormat('D MMM');
    $selesai = $magang->tanggal_selesai?->locale('id')->isoFormat('D MMM Y');
@endphp
<section class="rounded-3xl bg-white p-5 ring-1 ring-slate-200">
    <div class="flex items-center gap-3">
        @include('pembimbing.binaan.avatar', ['magang' => $magang, 'ukuran' => 'h-14 w-14'])
        <div class="min-w-0 flex-1">
            <p class="truncate text-base font-extrabold">{{ $magang->nama_lengkap }}</p>
            <p class="truncate text-xs text-slate-500">{{ $magang->instansi_pendidikan ?? '-' }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $aktif ? 'Aktif' : 'Selesai' }}</span>
    </div>
    <dl class="mt-4 grid grid-cols-2 gap-x-3 gap-y-3 text-sm">
        <div><dt class="text-xs text-slate-500">Nomor Induk</dt><dd class="font-bold">{{ $magang->no_induk ?? '-' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Nomor Telepon</dt><dd class="font-bold">{{ $magang->no_hp ?? '-' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Jurusan</dt><dd class="font-bold">{{ $magang->jurusan ?? '-' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Periode magang</dt><dd class="font-bold">{{ $mulai ?? '-' }} - {{ $selesai ?? '-' }}</dd></div>
    </dl>
</section>