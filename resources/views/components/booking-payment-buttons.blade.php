@props(['booking'])

@php
    $gateways = \App\Models\PaymentGateway::whereIn('code', ['stripe', 'paypal'])->get()->keyBy('code');
    $stripeReady = $gateways->get('stripe')?->isReady() ?? false;
    $paypalReady = $gateways->get('paypal')?->isReady() ?? false;
    $isPayable = $booking->payment_status !== 'paid' && $booking->status !== 'cancelled';
    // An online gateway being active means this booking's confirmation is
    // gated on payment (see BookingCreationService::applyPolicies()) —
    // reflect that in the copy rather than the generic "complete your
    // payment" line once it's still sitting in "pending".
    $awaitingConfirmation = $isPayable && $booking->status === 'pending' && ($stripeReady || $paypalReady);
@endphp

@if ($isPayable && ($stripeReady || $paypalReady))
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-luxury-gold/30 bg-luxury-gold/5 p-5']) }}>
        <p class="text-xs font-semibold uppercase tracking-wide text-luxury-gold">
            {{ $awaitingConfirmation ? __('Payment Required to Confirm Booking') : __('Complete Your Payment') }}
        </p>
        <p class="mt-1 text-2xl font-bold text-luxury-white">{{ currency($booking->fare_amount) }}</p>

        <div class="mt-4 flex flex-col gap-2.5 sm:flex-row">
            @if ($stripeReady)
                <a href="{{ route('booking.pay', [$booking->booking_number, 'stripe']) }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-luxury-gold px-5 py-3 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                    <x-icon name="credit-card" class="h-4 w-4 shrink-0" />
                    {{ __('Pay with Card') }}
                </a>
            @endif
            @if ($paypalReady)
                <a href="{{ route('booking.pay', [$booking->booking_number, 'paypal']) }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-luxury-border bg-luxury-charcoal px-5 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 active:scale-[0.98]">
                    <span class="text-[13px] font-bold italic tracking-tight">
                        <span class="text-[#003087]">Pay</span><span class="text-[#009cde]">Pal</span>
                    </span>
                </a>
            @endif
        </div>

        <p class="mt-3 text-[11px] leading-snug text-luxury-muted">
            @if ($awaitingConfirmation)
                {{ __("You'll be redirected to a secure payment page. This booking is confirmed only once payment is received.") }}
            @else
                {{ __("You'll be redirected to a secure payment page. Your booking stays reserved either way.") }}
            @endif
        </p>

        <div class="mt-4 border-t border-luxury-border/60 pt-4">
            <p class="text-[11px] leading-snug text-luxury-muted">
                {{ __("Paying for someone else? Copy this link and send it to whoever's covering the fare — no account needed.") }}
            </p>
            <x-copy-payment-link-button :booking="$booking" class="mt-2.5 inline-flex" />
        </div>
    </div>
@endif
