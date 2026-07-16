@props(['navPages' => null])

@php
    $navPages ??= \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $activeLanguages = \App\Models\Language::active();
    $currentLanguage = $activeLanguages->firstWhere('code', app()->getLocale()) ?? $activeLanguages->first();
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
@endphp

<footer class="border-t border-luxury-border pb-20 lg:pb-0">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Brand --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="{{ route('pages.home') }}" class="flex items-center gap-2.5">
                    @if (setting('logo_url'))
                        <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-9 w-9 rounded-lg object-contain">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold text-sm font-bold text-luxury-black">
                            {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                        </span>
                    @endif
                    <span class="text-sm font-semibold text-luxury-white">{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</span>
                </a>
                @if (setting('tagline'))
                    <p class="mt-4 max-w-xs text-sm text-luxury-muted">{{ setting('tagline') }}</p>
                @endif
            </div>

            {{-- Quick links --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Quick Links') }}</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach (\App\Models\Page::PAGES as $slug => $label)
                        @continue(! isset($navPages[$slug]))
                        @continue(in_array($slug, \App\Models\Page::LEGAL_PAGES, true) || in_array($slug, \App\Models\Page::SERVICE_PAGES, true))
                        <li>
                            <a href="{{ $slug === 'home' ? route('pages.home') : route('pages.show', $slug) }}" class="text-sm text-luxury-muted transition hover:text-luxury-gold">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('blog.index') }}" class="text-sm text-luxury-muted transition hover:text-luxury-gold">{{ __('Blog') }}</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Legal') }}</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach (\App\Models\Page::LEGAL_PAGES as $slug)
                        @continue(! isset($navPages[$slug]))
                        <li><a href="{{ route('pages.show', $slug) }}" class="text-sm text-luxury-muted transition hover:text-luxury-gold">{{ \App\Models\Page::PAGES[$slug] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-luxury-white">{{ __('Contact') }}</p>
                <ul class="mt-4 space-y-2.5">
                    @if (setting('address'))
                        <li class="flex items-start gap-2.5 text-sm text-luxury-muted">
                            <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0" />
                            <span>{{ setting('address') }}</span>
                        </li>
                    @endif
                    @if (setting('phone'))
                        <li>
                            <a href="tel:{{ setting('phone') }}" class="flex items-center gap-2.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                                <x-icon name="phone" class="h-4 w-4 shrink-0" />
                                {{ setting('phone') }}
                            </a>
                        </li>
                    @endif
                    @if (setting('email'))
                        <li>
                            <a href="mailto:{{ setting('email') }}" class="flex items-center gap-2.5 text-sm text-luxury-muted transition hover:text-luxury-gold">
                                <x-icon name="mail" class="h-4 w-4 shrink-0" />
                                {{ setting('email') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center gap-4 border-t border-luxury-border pt-6 sm:flex-row sm:justify-between">
            <p class="text-xs text-luxury-muted">&copy; {{ now()->year }} {{ setting('company_name', config('app.name')) }}. {{ __('All rights reserved.') }}</p>

            <div class="flex items-center gap-3">
                @if ($currentCurrency && $activeCurrencies->count() > 1)
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs text-luxury-muted">
                            <span class="font-semibold">{{ $currentCurrency->symbol }}</span> {{ $currentCurrency->code }}
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute end-0 bottom-full z-40 mb-2 w-44 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                            @foreach ($activeCurrencies as $currencyOption)
                                <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                        {{ $currencyOption->symbol }} {{ $currencyOption->code }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($currentLanguage && $activeLanguages->count() > 1)
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-xs text-luxury-muted">
                            {{ $currentLanguage->native_name }}
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute end-0 bottom-full z-40 mb-2 w-44 overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite shadow-xl">
                            @foreach ($activeLanguages as $language)
                                <form method="POST" action="{{ route('locale.switch', $language->code) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-3.5 py-2.5 text-start text-xs {{ $language->code === $currentLanguage->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                        {{ $language->native_name }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>
