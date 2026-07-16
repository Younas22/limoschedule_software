@php
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
@endphp

@if ($currentLanguage && $activeLanguages->count() > 1)
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false"
            class="flex h-10 items-center gap-2 rounded-lg border border-luxury-border px-3 text-sm text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
            <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                @if ($currentLanguage->flag_url)
                    <img src="{{ $currentLanguage->flag_url }}" alt="{{ $currentLanguage->name }}" class="h-full w-full object-cover">
                @else
                    {{ $currentLanguage->code }}
                @endif
            </div>
            <span class="hidden sm:inline">{{ $currentLanguage->native_name }}</span>
        </button>

        <div x-show="open" x-cloak x-transition
            class="absolute end-0 z-30 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
            @foreach ($activeLanguages as $language)
                <form method="POST" action="{{ route('admin.locale.switch', $language->code) }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                            @if ($language->flag_url)
                                <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                            @else
                                {{ $language->code }}
                            @endif
                        </div>
                        <span>{{ $language->native_name }}</span>
                        @if ($language->code === $currentLanguage->code)
                            <svg class="ms-auto h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
