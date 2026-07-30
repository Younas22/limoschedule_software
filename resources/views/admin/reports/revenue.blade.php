<x-admin.layouts.app :title="__('Revenue Report')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Revenue Report') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Paid bookings within the selected date range.') }}</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to Reports') }}</a>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET">
            <x-admin.reports.date-filter :from="$from" :to="$to" />
        </form>
        <x-admin.reports.export-buttons report="revenue" />
    </div>

    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.stat-card :label="__('Total Revenue')" :value="currency($summary['total_revenue'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.72-3.44a1.5 1.5 0 011.342-.81h13.376a1.5 1.5 0 011.342.81l1.72 3.44M12 15.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" /></svg>
        </x-admin.stat-card>
        <x-admin.stat-card :label="__('Paid Bookings')" :value="number_format($summary['paid_count'])" :trend="__('Avg fare :amount', ['amount' => currency($summary['average_fare'])])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75" /></svg>
        </x-admin.stat-card>
        <x-admin.stat-card :label="__('Pending Payment')" :value="number_format($summary['pending_count'])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </x-admin.stat-card>
        <x-admin.stat-card :label="__('Refunded')" :value="number_format($summary['refunded_count'])" :trend="__(':amount refunded', ['amount' => currency($summary['refunded_amount'])])">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
        </x-admin.stat-card>
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Booking') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Fare') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($rows as $booking)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $booking->booking_number }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->customer?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->vehicle?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->pickup_datetime->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $booking->type_label }}</td>
                            <td class="px-6 py-3 text-end font-medium text-luxury-gold">{{ currency($booking->fare_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">{{ __('No paid bookings in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($rows->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($rows->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Previous') }}</span>
                @else
                    <a href="{{ $rows->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Previous') }}</a>
                @endif
            </div>
            <p>{{ __('Page :current of :last', ['current' => $rows->currentPage(), 'last' => $rows->lastPage()]) }}</p>
            <div>
                @if ($rows->hasMorePages())
                    <a href="{{ $rows->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">{{ __('Next') }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
