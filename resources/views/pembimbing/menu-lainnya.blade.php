{{-- Butuh: $variant = 'side' (sidebar / sheet) atau 'card' (kartu di beranda). Menghasilkan <li>. --}}
@php
    $menuLain = [
        ['Pekerjaan binaan',        route('pembimbing.pekerjaan.index'),   'pembimbing.pekerjaan*',
            'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0'],
        ['Validasi aktivitas',      route('pembimbing.aktivitas.index'),   'pembimbing.aktivitas*',
            'M9 12.75L11.25 15 15 9.75M10.5 3.75h3a1.5 1.5 0 011.5 1.5v.75h1.5a2.25 2.25 0 012.25 2.25v11.25a2.25 2.25 0 01-2.25 2.25H7.5a2.25 2.25 0 01-2.25-2.25V8.25A2.25 2.25 0 017.5 6H9v-.75a1.5 1.5 0 011.5-1.5z'],
        ['Verifikasi tidak hadir',  route('pembimbing.izin.index'),        'pembimbing.izin*',
            'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6-9.75-6'],
        ['Verifikasi tugas luar',   route('pembimbing.tugas-luar.index'),  'pembimbing.tugas-luar*',
            'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
        ['Penilaian akhir',         route('pembimbing.penilaian.index'),   'pembimbing.penilaian*',
            'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
    ];
@endphp

@foreach ($menuLain as [$label, $url, $pattern, $icon])
    @php $on = request()->routeIs($pattern); @endphp
    <li>
        @if ($variant === 'card')
            <a href="{{ $url }}" class="flex items-center gap-3 px-4 py-3.5 text-sm font-bold hover:bg-slate-50">
                <svg class="h-5 w-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                <span class="flex-1">{{ $label }}</span>
                <span class="text-lg text-slate-400" aria-hidden="true">›</span>
            </a>
        @else
            <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $on ? 'bg-brand-soft text-brand' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                {{ $label }}
            </a>
        @endif
    </li>
@endforeach