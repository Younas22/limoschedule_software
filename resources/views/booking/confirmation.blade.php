@php
    $whatsappNumber = booking_setting('whatsapp_number');
    $whatsappDigits = $whatsappNumber ? preg_replace('/\D+/', '', $whatsappNumber) : null;
    $whatsappMessage = "Hi! I'd like some help with my booking {$booking->booking_number}.";
    $paymentBadgeClass = match ($booking->payment_status) {
        'paid' => 'bg-emerald-500/10 text-emerald-400',
        'refunded' => 'bg-luxury-slate/40 text-luxury-muted',
        default => 'bg-luxury-gold/10 text-luxury-gold',
    };
@endphp

<x-layouts.public :title="'Booking Received'">
    <div class="mx-auto max-w-2xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="relative rounded-2xl border border-luxury-border bg-luxury-charcoal p-8 text-center sm:p-12">
            <div class="pointer-events-none absolute inset-x-0 -top-10 mx-auto h-40 w-40 rounded-full bg-luxury-gold/20 blur-3xl"></div>

            <span class="animate-fade-up relative mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-luxury-gold/10 text-luxury-gold">
                <x-icon name="check-circle" class="h-8 w-8" />
            </span>

            <h1 class="animate-fade-up delay-1 relative mt-6 text-2xl font-semibold text-luxury-white sm:text-3xl">{{ __('Booking Request Received') }}</h1>
            <p class="animate-fade-up delay-1 relative mt-3 text-sm text-luxury-muted">
                {{ __('Thank you! Your reference number is') }}
                <span class="font-semibold text-luxury-gold">{{ $booking->booking_number }}</span>.
                {{ __('Our team will confirm your ride shortly.') }}
            </p>

            <div class="animate-fade-up delay-2 relative mt-8 space-y-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-5 text-start">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Booking ID') }}</span>
                    <span class="font-medium text-luxury-white">{{ $booking->booking_number }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Status') }}</span>
                    <span class="font-medium text-luxury-white">{{ $booking->status_label }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Pickup') }}</span>
                    <span class="text-end font-medium text-luxury-white">{{ $booking->pickup_location }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Drop-off') }}</span>
                    <span class="text-end font-medium text-luxury-white">{{ $booking->dropoff_location }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Date & Time') }}</span>
                    <span class="font-medium text-luxury-white">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Vehicle') }}</span>
                    <span class="font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-luxury-muted">{{ __('Payment Status') }}</span>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $paymentBadgeClass }}">{{ $booking->payment_status_label }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-luxury-border/60 pt-3 text-sm">
                    <span class="text-luxury-muted">{{ __('Estimated Fare') }}</span>
                    <span class="text-lg font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</span>
                </div>
            </div>

            <div class="animate-fade-up delay-3 relative mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-6 py-3.5 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <x-icon name="download" class="h-4 w-4" />
                    {{ __('Download Invoice') }}
                </a>

                @if ($whatsappDigits)
                    <a href="https://wa.me/{{ $whatsappDigits }}?text={{ urlencode($whatsappMessage) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-6 py-3.5 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="chat" class="h-4 w-4" />
                        {{ __('WhatsApp Support') }}
                    </a>
                @endif
            </div>

            <a href="{{ route('pages.home') }}"
                class="animate-fade-up delay-3 relative mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light sm:w-auto">
                {{ __('Back to Home') }}
            </a>
        </div>
    </div>
</x-layouts.public>
