<x-driver.layouts.app :title="__('Dashboard')">
    {{-- Welcome --}}
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-gold/30 bg-gradient-to-br from-luxury-charcoal to-luxury-graphite p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Welcome back, :name', ['name' => explode(' ', $driver->name)[0]]) }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __("Here's your activity overview.") }}</p>
        </div>
    </div>

    {{-- Active Ride --}}
    @if ($activeRide)
        <div class="mb-6 rounded-2xl border border-blue-500/30 bg-blue-500/5 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-400">{{ __('Ride in Progress') }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $activeRide->pickup_location }} &rarr; {{ $activeRide->dropoff_location }}</p>
                    @if ($activeRide->estimated_arrival_at)
                        <p class="mt-1 text-xs text-luxury-muted">{{ __('Estimated arrival') }}: {{ $activeRide->estimated_arrival_at->format('h:i A') }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('driver.bookings.complete', $activeRide) }}">
                    @csrf
                    <x-admin.button type="submit" variant="primary">{{ __('Complete Ride') }}</x-admin.button>
                </form>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.stat-card :label="__('Today\'s Trips')" :value="$stats['todayTrips']">
            <x-icon name="car" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('This Month\'s Earnings')" :value="currency($stats['monthEarnings'])">
            <x-icon name="cash" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Completed Trips')" :value="$stats['totalTrips']">
            <x-icon name="check-circle" class="h-5 w-5" />
        </x-admin.stat-card>

        <x-admin.stat-card :label="__('Average Rating')" :value="$stats['averageRating'] ?? __('N/A')">
            <x-icon name="star" class="h-5 w-5" />
        </x-admin.stat-card>
    </div>

    {{-- Today's Trips --}}
    <div class="mt-8 rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="flex items-center justify-between border-b border-luxury-border px-6 py-4">
            <h3 class="text-sm font-semibold text-luxury-white">{{ __("Today's Trips") }}</h3>
            <a href="{{ route('driver.bookings.index') }}" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('View All') }}</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Route') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Time') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($todayBookings as $booking)
                        <tr class="cursor-pointer hover:bg-luxury-graphite" onclick="window.location='{{ route('driver.bookings.show', $booking) }}'">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $booking->pickup_location }} &rarr; {{ $booking->dropoff_location }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('h:i A') }}</td>
                            <td class="px-6 py-3"><x-customer.status-badge :status="$booking->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No trips scheduled for today.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-driver.layouts.app>
