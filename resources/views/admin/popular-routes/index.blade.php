<x-admin.layouts.app :title="'Popular Routes'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">Popular Routes</h2>
            <p class="mt-1 text-sm text-luxury-muted">Airport, city, and intercity routes shown on the public website.</p>
        </div>

        <div class="flex items-center gap-2">
            @permission('routes.view')
                <a href="{{ route('admin.popular-routes.route-types.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    Route Types
                </a>
            @endpermission

            @permission('routes.create')
                <a href="{{ route('admin.popular-routes.create') }}">
                    <x-admin.button type="button" variant="primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Route
                    </x-admin.button>
                </a>
            @endpermission
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="route_type_id" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">All Types</option>
            @foreach ($routeTypes as $routeType)
                <option value="{{ $routeType->id }}" @selected((string) request('route_type_id') === (string) $routeType->id)>{{ $routeType->name }}</option>
            @endforeach
        </select>

        @if (request()->filled('route_type_id'))
            <a href="{{ route('admin.popular-routes.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">Clear filter</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Pickup</th>
                        <th class="px-6 py-3 font-medium">Dropoff</th>
                        <th class="px-6 py-3 font-medium">Distance</th>
                        <th class="px-6 py-3 font-medium">Est. Price</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($routes as $route)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">{{ $route->routeType?->name ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium text-luxury-white">{{ $route->pickup }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $route->dropoff }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $route->distance ? $route->distance.' '.$route->distance_unit : '—' }}</td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $route->estimated_price ? currency($route->estimated_price) : '—' }}</td>
                            <td class="px-6 py-3"><x-admin.status-badge :active="$route->is_active" /></td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('routes.edit')
                                        <a href="{{ route('admin.popular-routes.edit', $route) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.popular-routes.toggle', $route) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                {{ $route->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    @endpermission

                                    @permission('routes.delete')
                                        <form method="POST" action="{{ route('admin.popular-routes.destroy', $route) }}" onsubmit="return confirm('Delete this route?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                Delete
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-luxury-muted">No popular routes added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($routes->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($routes->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                @else
                    <a href="{{ $routes->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                @endif
            </div>
            <p>Page {{ $routes->currentPage() }} of {{ $routes->lastPage() }}</p>
            <div>
                @if ($routes->hasMorePages())
                    <a href="{{ $routes->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
