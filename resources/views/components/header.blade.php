@props(['navPages' => null, 'currentSlug' => null])

{{--
    Assumes it renders within a parent scope exposing Alpine state
    `sidebarOpen` and `searchOpen` (declared once on layouts.public).
--}}

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
@endphp

<header class="sticky top-0 z-30 border-b border-luxury-border bg-luxury-black/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <a href="{{ route('pages.home') }}" class="flex min-w-0 shrink items-center">
            @if (setting('logo_url'))
                <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-6 w-auto max-w-[150px] object-contain sm:h-9 sm:max-w-none">
            @else
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-luxury-gold text-sm font-bold text-luxury-black">
                    {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                </span>
            @endif
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex">
            @foreach (\App\Models\Page::PAGES as $slug => $label)
                @continue(! isset($navPages[$slug]))
                @continue(in_array($slug, \App\Models\Page::LEGAL_PAGES, true) || in_array($slug, \App\Models\Page::SERVICE_PAGES, true))
                <a href="{{ $slug === 'home' ? route('pages.home') : route('pages.show', $slug) }}"
                    class="text-sm font-medium text-luxury-muted transition hover:text-luxury-gold {{ $currentSlug === $slug ? 'text-luxury-gold' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
            <a href="{{ route('blog.index') }}" class="text-sm font-medium text-luxury-muted transition hover:text-luxury-gold {{ request()->routeIs('blog.*') ? 'text-luxury-gold' : '' }}">
                {{ __('Blog') }}
            </a>
        </nav>

        {{-- Right actions --}}
        <div class="ms-auto flex items-center gap-1.5 lg:ms-0">
            {{-- Search --}}
            <button type="button" @click="searchOpen = true" aria-label="{{ __('Search') }}"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                <x-icon name="search" class="h-5 w-5" />
            </button>

            {{-- Currency switcher --}}
            @if ($currentCurrency && $activeCurrencies->count() > 1)
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex h-10 cursor-pointer items-center gap-1.5 rounded-lg px-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                        <span class="fi fi-{{ $currentCurrency->flag_country_code }} rounded-sm"></span>
                        <span class="font-semibold">{{ $currentCurrency->symbol }}</span>
                        <span>{{ $currentCurrency->code }}</span>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition
                        class="absolute end-0 z-40 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                        @foreach ($activeCurrencies as $currencyOption)
                            <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <span class="fi fi-{{ $currencyOption->flag_country_code }} shrink-0 rounded-sm"></span>
                                    <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span>
                                    <span>{{ $currencyOption->name }}</span>
                                    <span class="ms-auto text-xs text-luxury-muted">{{ $currencyOption->code }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Language switcher --}}
            @if ($currentLanguage && $activeLanguages->count() > 1)
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex h-10 cursor-pointer items-center gap-1.5 rounded-lg px-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                            @if ($currentLanguage->flag_url)
                                <img src="{{ $currentLanguage->flag_url }}" alt="{{ $currentLanguage->name }}" class="h-full w-full object-cover">
                            @elseif ($currentLanguage->flag_country_code)
                                <span class="fi fi-{{ $currentLanguage->flag_country_code }}"></span>
                            @else
                                {{ $currentLanguage->code }}
                            @endif
                        </div>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition
                        class="absolute end-0 z-40 mt-2 w-52 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                        @foreach ($activeLanguages as $language)
                            <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <div class="flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded border border-luxury-border bg-luxury-graphite text-[9px] font-semibold uppercase">
                                        @if ($language->flag_url)
                                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                                        @elseif ($language->flag_country_code)
                                            <span class="fi fi-{{ $language->flag_country_code }}"></span>
                                        @else
                                            {{ $language->code }}
                                        @endif
                                    </div>
                                    <span>{{ $language->native_name }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Auth --}}
            <div class="hidden items-center gap-2 sm:flex">
                @if (\Illuminate\Support\Facades\Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-lg px-3.5 py-2 text-sm font-medium text-luxury-muted transition hover:text-luxury-white">
                        {{ __('Login') }}
                    </a>
                @else
                    <span class="cursor-not-allowed rounded-lg px-3.5 py-2 text-sm font-medium text-luxury-muted/40" title="{{ __('Coming soon') }}">
                        {{ __('Login') }}
                    </span>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-lg bg-luxury-gold px-4 py-2 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                        {{ __('Register') }}
                    </a>
                @else
                    <span class="cursor-not-allowed rounded-lg bg-luxury-gold/40 px-4 py-2 text-sm font-semibold text-luxury-black/60" title="{{ __('Coming soon') }}">
                        {{ __('Register') }}
                    </span>
                @endif
            </div>

            {{-- Hamburger (mobile) --}}
            <button type="button" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white lg:hidden">
                <x-icon name="menu" class="h-5 w-5" />
            </button>
        </div>
    </div>
</header>
