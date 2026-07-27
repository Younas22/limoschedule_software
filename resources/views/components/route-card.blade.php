@props(['route'])

@php
    $bookingSettings = booking_setting();
    $whatsappEnabled = (bool) ($bookingSettings->manual_booking_enabled && $bookingSettings->whatsapp_number);
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
                <x-icon name="cash" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-gold">{{ $route->estimated_price ? currency($route->estimated_price) : '—' }}</span>
            </div>
        </div>
    </div>

    @if ($whatsappEnabled)
        {{-- Manual booking mode: skip the form entirely, go straight to WhatsApp. --}}
        @php
            $waMessage = __("Hi! I'd like to book a ride from :pickup to :dropoff. Please confirm availability.", [
                'pickup' => $route->pickup,
                'dropoff' => $route->dropoff,
            ]);
            $waDigits = preg_replace('/\D/', '', $bookingSettings->whatsapp_number);
        @endphp
        <a href="https://wa.me/{{ $waDigits }}?text={{ urlencode($waMessage) }}" target="_blank" rel="noopener"
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
