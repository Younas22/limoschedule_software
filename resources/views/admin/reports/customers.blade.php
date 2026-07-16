<x-admin.layouts.app :title="'Customers Report'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">Customers Report</h2>
            <p class="mt-1 text-sm text-luxury-muted">Bookings, spend, and loyalty per customer.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">&larr; Back to Reports</a>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-5 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET">
            <x-admin.reports.date-filter :from="$from" :to="$to" />
        </form>
        <x-admin.reports.export-buttons report="customers" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Customer</th>
                        <th class="px-6 py-3 font-medium">Email</th>
                        <th class="px-6 py-3 text-end font-medium">Bookings</th>
                        <th class="px-6 py-3 text-end font-medium">Total Spent</th>
                        <th class="px-6 py-3 text-end font-medium">Wallet</th>
                        <th class="px-6 py-3 text-end font-medium">Loyalty Pts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($rows as $customer)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-luxury-white hover:text-luxury-gold">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $customer->email }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ number_format($customer->bookings_count) }}</td>
                            <td class="px-6 py-3 text-end font-medium text-luxury-gold">{{ currency($customer->total_spent ?? 0) }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ currency($customer->wallet_balance) }}</td>
                            <td class="px-6 py-3 text-end text-luxury-muted">{{ number_format($customer->loyalty_points) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">No customers found.</td>
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
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                @else
                    <a href="{{ $rows->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                @endif
            </div>
            <p>Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}</p>
            <div>
                @if ($rows->hasMorePages())
                    <a href="{{ $rows->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
