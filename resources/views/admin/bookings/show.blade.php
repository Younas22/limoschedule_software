<x-admin.layouts.app :title="'Booking '.$booking->booking_number">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $booking->booking_number }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">Booked on {{ $booking->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('booking.invoice', $booking->booking_number) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <x-icon name="eye" class="h-4 w-4" />
                View Invoice
            </a>
            <a href="{{ route('booking.invoice.download', $booking->booking_number) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-luxury-border px-3 py-2 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <x-icon name="download" class="h-4 w-4" />
                Download PDF
            </a>
            @permission('bookings.edit')
                <a href="{{ route('admin.bookings.edit', $booking) }}">
                    <x-admin.button type="button" variant="primary">Edit Booking</x-admin.button>
                </a>
            @endpermission
            <a href="{{ route('admin.bookings.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">&larr; Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize
                        {{ match ($booking->status) {
                            'completed' => 'bg-emerald-500/10 text-emerald-400',
                            'cancelled' => 'bg-red-500/10 text-red-400',
                            'assigned' => 'bg-luxury-gold/10 text-luxury-gold',
                            'confirmed' => 'bg-luxury-secondary/10 text-luxury-secondary',
                            default => 'bg-luxury-slate text-luxury-muted',
                        } }}">
                        {{ $booking->status_label }}
                    </span>
                    <span class="text-xs text-luxury-muted">{{ $booking->type_label }}</span>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 border-t border-luxury-border pt-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Pickup</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_location }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Drop-off</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->dropoff_location }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Pickup Date & Time</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->pickup_datetime->format('M d, Y — h:i A') }}</p>
                    </div>
                    @if ($booking->return_datetime)
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Return Date & Time</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->return_datetime->format('M d, Y — h:i A') }}</p>
                        </div>
                    @endif
                    @if ($booking->hours)
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Duration</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->hours }} hour(s)</p>
                        </div>
                    @endif
                    @if ($booking->distance_km)
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Distance</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->distance_km }} km</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Passengers</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->passengers }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Luggage</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->luggage }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Waiting Time</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->waiting_minutes }} min</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Toll</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->has_toll ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                @if (! empty($booking->stops))
                    <div class="mt-5 border-t border-luxury-border pt-5">
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Stops</p>
                        <ul class="mt-1 list-inside list-disc text-sm text-luxury-white">
                            @foreach ($booking->stops as $stop)
                                <li>{{ $stop }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($booking->notes)
                    <div class="mt-5 border-t border-luxury-border pt-5">
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Notes</p>
                        <p class="mt-1 text-sm text-luxury-white">{{ $booking->notes }}</p>
                    </div>
                @endif

                @if ($booking->status === 'cancelled' && $booking->cancellation_reason)
                    <div class="mt-5 border-t border-luxury-border pt-5">
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Cancellation Reason</p>
                        <p class="mt-1 text-sm text-red-400">{{ $booking->cancellation_reason_label ?? $booking->cancellation_reason }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Customer</h3>
                @if ($booking->customer)
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Name</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->customer->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Email</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->customer->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-luxury-muted">Phone</p>
                            <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->customer->phone ?? '—' }}</p>
                        </div>
                        @permission('customers.view')
                            <div>
                                <a href="{{ route('admin.customers.show', $booking->customer) }}" class="text-sm text-luxury-gold hover:text-luxury-gold-light">View Customer Profile &rarr;</a>
                            </div>
                        @endpermission
                    </div>
                @else
                    <p class="mt-2 text-sm text-luxury-muted">No customer on file.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Vehicle & Driver</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Vehicle</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->vehicle?->name ?? '—' }}</p>
                        <p class="text-xs text-luxury-muted">{{ $booking->vehicle?->category?->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Driver</p>
                        <p class="mt-1 text-sm font-medium text-luxury-white">{{ $booking->driver?->name ?? 'Unassigned' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">Payment Status</p>
                <p class="mt-1 text-sm font-medium capitalize {{ $booking->payment_status === 'paid' ? 'text-emerald-400' : 'text-luxury-muted' }}">
                    {{ $booking->payment_status_label }}
                </p>

                <div class="mt-4 border-t border-luxury-border pt-4">
                    <p class="text-xs uppercase tracking-wide text-luxury-muted">Total Fare</p>
                    <p class="mt-1 text-2xl font-semibold text-luxury-gold">{{ currency($booking->fare_amount) }}</p>
                </div>

                @if (! empty($booking->fare_breakdown))
                    <div class="mt-4 space-y-1.5 border-t border-luxury-border pt-4 text-sm">
                        @foreach ($booking->fare_breakdown as $label => $amount)
                            <div class="flex items-center justify-between text-luxury-muted">
                                <span class="capitalize">{{ str_replace('_', ' ', $label) }}</span>
                                <span class="text-luxury-white">{{ is_numeric($amount) ? currency($amount) : $amount }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($booking->payment_gateway)
                    <div class="mt-4 border-t border-luxury-border pt-4">
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Payment Method</p>
                        <p class="mt-1 text-sm text-luxury-white">{{ $booking->payment_gateway_label }}</p>
                        @if ($booking->transaction_id)
                            <p class="text-xs text-luxury-muted">{{ $booking->transaction_id }}</p>
                        @endif
                    </div>
                @endif

                @if ($booking->refund_status && $booking->refund_status !== 'not_applicable')
                    <div class="mt-4 border-t border-luxury-border pt-4">
                        <p class="text-xs uppercase tracking-wide text-luxury-muted">Refund</p>
                        <p class="mt-1 text-sm {{ $booking->refund_status === 'refunded' ? 'text-emerald-400' : 'text-luxury-gold' }}">{{ $booking->refund_status_label }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layouts.app>
