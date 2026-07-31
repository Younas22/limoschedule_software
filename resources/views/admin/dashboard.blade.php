<x-admin.layouts.app :title="__('Dashboard')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Overview') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __("Welcome back, :name. Here's what's happening today.", ['name' => auth()->guard('admin')->user()->name]) }}</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <x-admin.stat-card :label="__('Bookings')" :value="number_format($stats['bookings'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Revenue')" :value="currency($stats['revenue'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-4-4.5c0 1.38 1.79 2.5 4 2.5s4-1.12 4-2.5-1.79-2.5-4-2.5-4-1.12-4-2.5S9.79 6 12 6s4 1.12 4 2.5" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Vehicles')" :value="number_format($stats['vehicles'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM6 17H4v-4l1.5-4.5A2 2 0 017.4 7h9.2a2 2 0 011.9 1.5L20 13v4h-2m-12 0h8m-8 0H4" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Drivers')" :value="number_format($stats['drivers'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z" />
            </svg>
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Customers')" :value="number_format($stats['customers'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.36-1.86M9 20H4v-2a3 3 0 015.36-1.86m0 0a4 4 0 116.36 0M7 10a4 4 0 118 0 4 4 0 01-8 0z" />
            </svg>
        </x-admin.stat-card>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex items-center justify-between border-b border-luxury-border px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Live Fleet') }}</h3>
                <p class="mt-0.5 text-xs text-luxury-muted">
                    {{ __(':online drivers online, :busy currently on a trip.', ['online' => $fleetSummary['online'], 'busy' => $fleetSummary['busy']]) }}
                </p>
            </div>
            <a href="{{ route('admin.fleet.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View Live Fleet') }}</a>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="border-b border-luxury-border px-6 py-4">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Recent Bookings') }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking #') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Fare') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($recentBookings as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->driver?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium capitalize text-luxury-gold">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-end text-luxury-white">{{ currency($booking->fare_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No bookings yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
