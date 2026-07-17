@props(['navPages' => null, 'currentSlug' => null])

{{--
    Assumes it renders within a parent scope exposing Alpine state
    `sidebarOpen` (declared once on layouts.public).
--}}

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
@endphp

{{-- Overlay --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity
    class="fixed inset-0 z-40 bg-black/70 lg:hidden"></div>

{{-- Drawer --}}
<aside x-show="sidebarOpen" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full rtl:translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full rtl:translate-x-full"
    class="fixed inset-y-0 start-0 z-50 flex w-[85%] max-w-sm flex-col overflow-y-auto border-e border-luxury-border bg-luxury-charcoal lg:hidden"
    @keydown.escape.window="sidebarOpen = false">

    <div class="flex h-16 shrink-0 items-center justify-between border-b border-luxury-border px-5">
        <a href="{{ route('pages.home') }}" @click="sidebarOpen = false" class="flex items-center">
            @if (setting('logo_url'))
                <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-8 w-auto max-w-none object-contain">
            @else
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-luxury-gold text-sm font-bold text-luxury-black">
                    {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                </span>
            @endif
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="{{ __('Close menu') }}"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white">
            <x-icon name="close" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1 px-3 py-4">
        <a href="{{ route('pages.home') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $currentSlug === 'home' ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            <x-icon name="home" class="h-5 w-5" />
            {{ __('Home') }}
        </a>
        @foreach (\App\Models\Page::PAGES as $slug => $label)
            @continue(! isset($navPages[$slug]))
            @continue($slug === 'home' || in_array($slug, \App\Models\Page::LEGAL_PAGES, true) || in_array($slug, \App\Models\Page::SERVICE_PAGES, true))
            <a href="{{ route('pages.show', $slug) }}" @click="sidebarOpen = false"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $currentSlug === $slug ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
                <x-icon name="{{ match ($slug) {
                    'about' => 'users',
                    'services' => 'car',
                    'faq' => 'chat',
                    'contact' => 'phone',
                    default => 'star',
                } }}" class="h-5 w-5" />
                {{ $label }}
            </a>
        @endforeach
        <a href="{{ route('blog.index') }}" @click="sidebarOpen = false"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('blog.*') ? 'bg-luxury-gold/10 text-luxury-gold' : 'text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            <x-icon name="pencil" class="h-5 w-5" />
            {{ __('Blog') }}
        </a>
    </nav>

    <div class="space-y-4 border-t border-luxury-border px-4 py-4">
        {{-- Switchers --}}
        <div class="grid grid-cols-2 gap-2">
            @if ($currentCurrency && $activeCurrencies->count() > 1)
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2.5 text-sm text-luxury-muted">
                        <span class="font-semibold">{{ $currentCurrency->symbol }}</span> {{ $currentCurrency->code }}
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute start-0 z-40 mt-2 w-48 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                        @foreach ($activeCurrencies as $currencyOption)
                            <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-sm {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span> {{ $currencyOption->code }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($currentLanguage && $activeLanguages->count() > 1)
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2.5 text-sm text-luxury-muted">
                        {{ $currentLanguage->code }}
                        <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute start-0 z-40 mt-2 w-48 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                        @foreach ($activeLanguages as $language)
                            <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-sm {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                    {{ $language->native_name }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Auth --}}
        <div class="flex items-center gap-2">
            @if (\Illuminate\Support\Facades\Route::has('login'))
                <a href="{{ route('login') }}" class="flex-1 rounded-lg border border-luxury-border px-4 py-2.5 text-center text-sm font-medium text-luxury-white">
                    {{ __('Login') }}
                </a>
            @else
                <span class="flex-1 cursor-not-allowed rounded-lg border border-luxury-border px-4 py-2.5 text-center text-sm font-medium text-luxury-muted/40">
                    {{ __('Login') }}
                </span>
            @endif

            @if (\Illuminate\Support\Facades\Route::has('register'))
                <a href="{{ route('register') }}" class="flex-1 rounded-lg bg-luxury-gold px-4 py-2.5 text-center text-sm font-semibold text-luxury-black">
                    {{ __('Register') }}
                </a>
            @else
                <span class="flex-1 cursor-not-allowed rounded-lg bg-luxury-gold/40 px-4 py-2.5 text-center text-sm font-semibold text-luxury-black/60">
                    {{ __('Register') }}
                </span>
            @endif
        </div>
    </div>
</aside>
