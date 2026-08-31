@props(['booking'])

{{--
    Shareable, no-login-required payment link for a booking — reuses the
    existing public booking.confirmation URL, since that page already shows
    the payment buttons for anyone holding the booking number (see
    booking-payment-buttons.blade.php). One link works for the admin sharing
    it with a customer who hasn't paid, and for a customer forwarding it to
    a friend or parent to pay on their behalf.
--}}
@php
    $paymentLink = route('booking.confirmation', $booking->booking_number);
@endphp

<div x-data="{ copied: false }" {{ $attributes->merge(['class' => 'inline-flex']) }}>
    <button type="button"
        @click="navigator.clipboard.writeText(@js($paymentLink)).then(() => { copied = true; setTimeout(() => (copied = false), 2000); })"
        class="inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
        <x-icon name="link" class="h-4 w-4" x-show="!copied" />
        <x-icon name="check-circle" class="h-4 w-4 text-emerald-400" x-show="copied" x-cloak />
        <span x-text="copied ? '{{ __('Link Copied!') }}' : '{{ __('Copy Payment Link') }}'"></span>
    </button>
</div>
