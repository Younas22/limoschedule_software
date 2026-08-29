@props(['route'])

@php
    $waNumber = setting('whatsapp');
    $waDigits = $waNumber ? preg_replace('/\D+/', '', $waNumber) : null;

    $waLines = [
        __('Hi! I found this route on your website (Popular Routes) and would like to book it:'),
        '',
        __('From').': '.$route->pickup,
        __('To').': '.$route->dropoff,
    ];

    if ($route->distance) {
        $waLines[] = __('Distance').': '.rtrim(rtrim(number_format((float) $route->distance, 1), '0'), '.').' '.$route->distance_unit;
    }

    if ($route->estimated_price) {
        $waLines[] = $route->has_discount
            ? __('Estimated Price').': '.currency($route->estimated_price).' ('.__('discounted from').' '.currency($route->original_price).')'
            : __('Estimated Price').': '.currency($route->estimated_price);
    }

    $waLines[] = '';
    $waLines[] = __('Please confirm availability.');

    $waMessage = implode("\n", $waLines);
@endphp

<div {{ $attributes->merge(['class' => 'flex h-full flex-col justify-between rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 transition hover:border-luxury-gold/40']) }}>
    <div>
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex flex-col items-center">
                <span class="h-2 w-2 shrink-0 rounded-full bg-luxury-gold"></span>
                <span class="my-1 h-6 w-px bg-luxury-border"></span>
                <x-icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-luxury-gold" />
            </div>
            <div class="min-w-0 flex-1 space-y-2.5">
                <p class="truncate text-sm font-semibold text-luxury-white">{{ $route->pickup }}</p>
                <p class="truncate text-sm font-semibold text-luxury-white">{{ $route->dropoff }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs text-luxury-muted">
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="trending-up" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-white">{{ $route->distance ? rtrim(rtrim(number_format((float) $route->distance, 1), '0'), '.') : '—' }}</span>
                {{ $route->distance ? $route->distance_unit : '' }}
            </div>
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="cash" class="h-3.5 w-3.5 shrink-0" />
                @if ($route->has_discount)
                    <span class="text-luxury-muted line-through decoration-solid decoration-red-500 decoration-2">{{ currency($route->original_price) }}</span>
                    <span class="font-semibold text-luxury-gold">{{ currency($route->estimated_price) }}</span>
                @else
                    <span class="font-semibold text-luxury-gold">{{ $route->estimated_price ? currency($route->estimated_price) : '—' }}</span>
                @endif
            </div>
        </div>
    </div>

    @if ($waDigits)
        {{-- Popular Routes always books straight via WhatsApp, regardless of
             the site's manual/website booking mode — these are quick,
             pre-priced routes best confirmed by chat. --}}
        <a href="https://wa.me/{{ $waDigits }}?text={{ rawurlencode($waMessage) }}" target="_blank" rel="noopener"
            class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
            <x-icon name="chat" class="h-4 w-4" />
            {{ __('Book This Route') }}
        </a>
    @else
        <a href="#booking-widget"
            @click="window.dispatchEvent(new CustomEvent('select-route', { detail: { pickup: {{ \Illuminate\Support\Js::from($route->pickup) }}, dropoff: {{ \Illuminate\Support\Js::from($route->dropoff) }} } }))"
            class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
            <x-icon name="calendar" class="h-4 w-4" />
            {{ __('Book This Route') }}
        </a>
    @endif
</div>
