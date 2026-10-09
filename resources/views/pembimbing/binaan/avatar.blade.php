{{-- Butuh: $magang. Opsional: $ukuran (kelas Tailwind, default h-12 w-12). --}}
@php
    $ukuran = $ukuran ?? 'h-12 w-12';
    $foto = $magang->foto ? asset('storage/'.$magang->foto) : null;
    $inisial = collect(explode(' ', trim((string) $magang->nama_lengkap)))->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
@endphp
@if ($foto)
    <img src="{{ $foto }}" alt="Foto {{ $magang->nama_lengkap }}" class="{{ $ukuran }} shrink-0 rounded-full border border-slate-300 object-cover">
@else
    <span class="{{ $ukuran }} grid shrink-0 place-items-center rounded-full border border-brand/40 bg-brand-soft text-sm font-extrabold text-brand" aria-hidden="true">{{ $inisial }}</span>
@endif