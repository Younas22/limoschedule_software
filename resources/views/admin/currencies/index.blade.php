<x-admin.layouts.app :title="'Currencies'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">Currencies</h2>
            <p class="mt-1 text-sm text-luxury-muted">Manage supported currencies and exchange rates.</p>
        </div>

        @permission('currencies.create')
            <a href="{{ route('admin.currencies.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Currency
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Currency</th>
                        <th class="px-6 py-3 font-medium">Code</th>
                        <th class="px-6 py-3 font-medium">Symbol</th>
                        <th class="px-6 py-3 font-medium">Exchange Rate</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($currencies as $currency)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-luxury-white">{{ $currency->name }}</span>
                                    @if ($currency->is_default)
                                        <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">Default</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $currency->code }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $currency->symbol }}</td>
                            <td class="px-6 py-3 text-luxury-muted">
                                {{ number_format($currency->exchange_rate, 6) }}
                                @if ($currency->is_default)
                                    <span class="text-[11px] text-luxury-muted">(base)</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $currency->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $currency->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('currencies.edit')
                                        <a href="{{ route('admin.currencies.edit', $currency) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            Edit
                                        </a>

                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.default', $currency) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                                    Set Default
                                                </button>
                                            </form>
                                        @endunless

                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.toggle', $currency) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $currency->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission

                                    @permission('currencies.delete')
                                        @unless ($currency->is_default)
                                            <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" onsubmit="return confirm('Delete this currency?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    Delete
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">No currencies configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
