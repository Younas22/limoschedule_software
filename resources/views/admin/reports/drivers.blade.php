<x-admin.layouts.app :title="__('Drivers Report')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Drivers Report') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Completed trips, revenue, and commission earned per driver.') }}</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; {{ __('Back to Reports') }}</a>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET">
            <x-admin.reports.date-filter :from="$from" :to="$to" />
        </form>
        <x-admin.reports.export-buttons report="drivers" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Completed Trips') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Revenue') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Commission') }}</th>
                        <th class="px-6 py-3 text-end font-medium">{{ __('Avg Rating') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($rows as $driver)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $driver->name }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ number_format($driver->bookings_count) }}</td>
                            <td class="px-6 py-3 text-end font-medium text-luxury-gold">{{ currency($driver->revenue ?? 0) }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ currency(((float) ($driver->revenue ?? 0)) * ((float) $driver->commission_rate / 100)) }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ $driver->average_rating ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">{{ __('No drivers found.') }}</td>
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
