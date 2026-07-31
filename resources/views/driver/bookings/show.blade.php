<x-driver.layouts.app :title="__('Booking').' '.$booking->booking_number">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $booking->booking_number }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Booked on :date', ['date' => $booking->created_at->format('M d, Y')]) }}</p>
        </div>
        <a href="{{ route('driver.bookings.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">&larr; {{ __('Back') }}</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <x-customer.status-badge :status="$booking->status" class="text-sm" />

                @if ($booking->status === 'assigned')
                    <form method="POST" action="{{ route('driver.bookings.start', $booking) }}">
                        @csrf
                        <x-admin.button type="submit" variant="primary">{{ __('Start Ride') }}</x-admin.button>
                    </form>
                @elseif ($booking->status === 'in_progress')
                    <form method="POST" action="{{ route('driver.bookings.complete', $booking) }}">
                        @csrf
                        <x-admin.button type="submit" variant="primary">{{ __('Complete Ride') }}</x-admin.button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-luxury-border pt-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Pickup') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Drop-off') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Date & Time') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Vehicle') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Passengers') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->passengers }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Luggage') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->luggage }}</p>
                </div>
            </div>

            @if ($booking->notes)
                <div class="border-t border-luxury-border pt-4">
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Notes') }}</p>
                    <p class="mt-1 text-sm text-luxury-white">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Customer') }}</p>
                <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->customer?->name ?? '—' }}</p>
                @if ($booking->customer?->phone)
                    <a href="tel:{{ $booking->customer->phone }}" class="mt-3 flex items-center justify-center gap-2 rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-white transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <x-icon name="phone" class="h-4 w-4" />
                        {{ __('Call Customer') }}
                    </a>
                @endif
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Fare') }}</p>
                <p class="mt-1 text-2xl font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                <div class="mt-2"><x-customer.payment-badge :status="$booking->payment_status" /></div>
            </div>

            @if ($booking->review)
                <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6 text-center">
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Customer Review') }}</p>
                    <div class="mt-2 flex justify-center"><x-rating-stars :rating="$booking->review->rating" size="h-3.5 w-3.5" /></div>
                    @if ($booking->review->comment)
                        <p class="mt-2 text-sm text-luxury-muted">{{ $booking->review->comment }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-driver.layouts.app>
