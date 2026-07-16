<x-admin.layouts.app :title="'Dynamic Pricing'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Dynamic Pricing Engine</h2>
        <p class="mt-1 text-sm text-luxury-muted">Control base fare, KM/hour rates, waiting, night, weekend, toll, airport and service charges per fleet category.</p>
    </div>

    {{-- Global Default --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-luxury-gold/30 bg-luxury-charcoal">
        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">Global Default</span>
                </div>
                <p class="mt-2 text-sm text-luxury-muted">
                    Base {{ currency($global->base_fare) }} &middot; KM {{ currency($global->km_fare) }} &middot; Hour {{ currency($global->hour_fare) }} &middot; Airport {{ currency($global->airport_surcharge) }}
                </p>
                <p class="mt-1 text-xs text-luxury-muted">Used by any fleet category without its own custom pricing rule.</p>
            </div>

            @permission('pricing.edit')
                <a href="{{ route('admin.pricing.global.edit') }}">
                    <x-admin.button type="button" variant="primary">Edit Global Pricing</x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Per-category rules --}}
    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Category</th>
                        <th class="px-6 py-3 font-medium">Base Fare</th>
                        <th class="px-6 py-3 font-medium">KM Fare</th>
                        <th class="px-6 py-3 font-medium">Hour Fare</th>
                        <th class="px-6 py-3 font-medium">Airport Surcharge</th>
                        <th class="px-6 py-3 font-medium">Pricing Source</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($categories as $category)
                        @php $rule = $category->pricingRule; @endphp
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $category->name }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->base_fare : $global->base_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->km_fare : $global->km_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->hour_fare : $global->hour_fare) }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ currency($rule?->is_active ? $rule->airport_surcharge : $global->airport_surcharge) }}</td>
                            <td class="px-6 py-3">
                                @if ($rule?->is_active)
                                    <span class="rounded-full bg-luxury-secondary/10 px-2.5 py-1 text-xs font-medium text-luxury-secondary">Custom</span>
                                @else
                                    <span class="rounded-full bg-luxury-slate px-2.5 py-1 text-xs font-medium text-luxury-muted">Global Default</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('pricing.edit')
                                        <a href="{{ route('admin.pricing.category.edit', $category) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            {{ $rule ? 'Edit' : 'Set Custom Pricing' }}
                                        </a>
                                    @endpermission
                                    @if ($rule)
                                        @permission('pricing.delete')
                                            <form method="POST" action="{{ route('admin.pricing.category.reset', $category) }}" onsubmit="return confirm('Reset this category to the global default pricing?');">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    Reset to Default
                                                </button>
                                            </form>
                                        @endpermission
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">No fleet categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
