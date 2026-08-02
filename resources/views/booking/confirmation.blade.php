@php
    $phone = setting('phone');
    $whatsapp = setting('whatsapp');
    $whatsappDigits = $whatsapp ? preg_replace('/\D+/', '', $whatsapp) : null;
    $whatsappMessage = __('Hi! I\'d like some help with my booking :number.', ['number' => $booking->booking_number]);
@endphp

<x-layouts.public :title="'Booking Received'">
    <div class="mx-auto max-w-lg px-4 py-20 sm:px-6 lg:px-8">
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

            @if ($phone || $whatsappDigits)
                <div class="animate-fade-up delay-2 relative mt-8 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Need Help?') }}</p>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                        @if ($phone)
                            <a href="tel:{{ $phone }}"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-luxury-border px-5 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="phone" class="h-4 w-4" />
                                {{ $phone }}
                            </a>
                        @endif
                        @if ($whatsappDigits)
                            <a href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($whatsappMessage) }}" target="_blank" rel="noopener"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-luxury-border px-5 py-3 text-sm font-semibold text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                <x-icon name="chat" class="h-4 w-4" />
                                {{ __('WhatsApp') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('pages.home') }}"
                class="animate-fade-up delay-3 relative mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light sm:w-auto">
                {{ __('Back to Home') }}
            </a>
        </div>
    </div>
</x-layouts.public>
