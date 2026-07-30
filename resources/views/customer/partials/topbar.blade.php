@php
    $activeCurrencies = \App\Models\Currency::active();
    $currentCurrency = active_currency();
@endphp

<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-luxury-border bg-luxury-charcoal/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="tap-scale text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Menu') }}">
        <x-icon name="menu" class="h-6 w-6" />
    </button>

    <div class="hidden flex-1 sm:block">
        <h1 class="text-sm font-medium text-luxury-muted">{{ $title ?? __('Overview') }}</h1>
    </div>

    <div class="ms-auto flex items-center gap-2">
        @if ($currentCurrency && $activeCurrencies->count() > 1)
            <div x-data="{ open: false }" class="relative hidden sm:block">
                <button type="button" @click="open = !open" @click.outside="open = false"
                    class="tap-scale flex h-10 items-center gap-1 rounded-lg px-2.5 text-sm text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">
                    <span class="font-semibold">{{ $currentCurrency->symbol }}</span>
                    <span>{{ $currentCurrency->code }}</span>
                    <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                </button>
                <div x-show="open" x-cloak x-transition class="absolute end-0 z-40 mt-2 w-48 overflow-hidden rounded-xl border border-luxury-border bg-luxury-charcoal shadow-xl">
                    @foreach ($activeCurrencies as $currencyOption)
                        <form method="POST" action="{{ route('currency.switch', $currencyOption->code) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-start text-sm transition hover:bg-luxury-graphite {{ $currencyOption->code === $currentCurrency->code ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                                <span class="w-5 shrink-0 text-center font-semibold">{{ $currencyOption->symbol }}</span> {{ $currencyOption->name }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif

        @include('customer.partials.notifications')
        @include('customer.partials.profile-menu')
    </div>
</header>
