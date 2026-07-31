<x-admin.layouts.app :title="__('Live Fleet')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Live Fleet') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Real-time status of every online driver.') }}</p>
    </div>

    <div x-data="adminFleet(@js($rows), @js($office), @js(route('admin.fleet.data')))" x-init="init()" class="space-y-6">
        <x-dispatch-map id="admin-fleet-map" class="h-72 sm:h-96" />

        <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Driver') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Location') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Current Ride') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Ride Ends') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Distance from Office') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Next Pickup') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('ETA') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-luxury-graphite">
                                <td class="px-6 py-3 font-medium text-luxury-white" x-text="row.name"></td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="row.status === 'busy' ? 'bg-blue-500/10 text-blue-400' : 'bg-emerald-500/10 text-emerald-400'"
                                        x-text="row.status === 'busy' ? '{{ __('On Trip') }}' : '{{ __('Available') }}'"></span>
                                </td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.location"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.current_ride || '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.ride_ends_in_minutes !== null ? row.ride_ends_in_minutes + ' {{ __('min') }}' : '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.distance_from_office_km !== null ? row.distance_from_office_km + ' km' : '—'"></td>
                                <td class="px-6 py-3 text-luxury-muted" x-text="row.next_pickup || '—'"></td>
                                <td class="px-6 py-3 text-luxury-gold" x-text="row.eta_minutes !== null ? row.eta_minutes + ' {{ __('min') }}' : '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="8" class="px-6 py-10 text-center text-luxury-muted">{{ __('No drivers online right now.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function adminFleet(initialRows, office, dataUrl) {
            return {
                rows: initialRows,
                office,
                dataUrl,

                init() {
                    this.renderMap();
                    setInterval(() => this.poll(), 20000);
                },

                renderMap() {
                    window.renderDispatchMap('admin-fleet-map', {
                        office: this.office,
                        driver: null,
                        pickup: this.office || (this.rows[0] ? { lat: this.rows[0].lat, lng: this.rows[0].lng } : null),
                        showRoute: false,
                        routeFrom: null,
                    });

                    this.rows.forEach((row) => {
                        if (row.lat && row.lng && window.google) {
                            new google.maps.Marker({
                                position: { lat: row.lat, lng: row.lng },
                                map: window.__dispatchMapInstances['admin-fleet-map']?.map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: 7,
                                    fillColor: row.status === 'busy' ? '#f97316' : '#3b82f6',
                                    fillOpacity: 1,
                                    strokeColor: '#ffffff',
                                    strokeWeight: 2,
                                },
                                title: row.name,
                            });
                        }
                    });
                },

                async poll() {
                    try {
                        const response = await fetch(this.dataUrl, { headers: { Accept: 'application/json' } });
                        if (! response.ok) return;
                        const json = await response.json();
                        this.rows = json.rows;
                        this.office = json.office;
                        this.renderMap();
                    } catch (e) {
                        // Next tick will retry.
                    }
                },
            };
        }
    </script>
</x-admin.layouts.app>
