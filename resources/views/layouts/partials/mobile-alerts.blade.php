@if (session('success') || session('error') || $errors->any())
    <div class="mb-4 space-y-2.5">
        @if (session('success'))
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-emerald-50/95 p-3.5 text-xs sm:text-sm font-semibold text-emerald-800 shadow-xs backdrop-blur-md">
                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-emerald-600 text-white shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1 break-words leading-relaxed pt-0.5">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 rounded-2xl border border-rose-200/80 bg-rose-50/95 p-3.5 text-xs sm:text-sm font-semibold text-rose-800 shadow-xs backdrop-blur-md">
                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-rose-600 text-white shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1 break-words leading-relaxed pt-0.5">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-2xl border border-rose-200/80 bg-rose-50/95 p-3.5 text-xs sm:text-sm font-semibold text-rose-800 shadow-xs backdrop-blur-md">
                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-rose-600 text-white shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1 break-words leading-relaxed pt-0.5 space-y-1">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
