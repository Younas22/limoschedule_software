@props(['class' => ''])

@php
    $settings = booking_setting();
    $websiteBookingEnabled = (bool) ($settings->website_booking_enabled && $settings->guest_booking_enabled);
@endphp

@if ($websiteBookingEnabled)
@php
    $categories = \App\Models\VehicleCategory::active()->ordered()->get(['id', 'name']);
    $voiceSearchEnabled = (bool) $settings->voice_search_enabled;
    $initialStops = old('stops', []);
    $serviceTypes = collect(\App\Models\Booking::TYPES)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values();
    $googleMapsKey = config('services.google_maps.key');
@endphp

@once
    @if ($googleMapsKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=__initBookingWidgetMaps"></script>
        <script>
            window.__initBookingWidgetMaps = function () {
                const attach = () => {
                    document.querySelectorAll('[data-booking-widget]').forEach((el) => {
                        Alpine.$data(el).initAutocomplete();
                    });
                };

                // Google's script is async and may finish loading before or
                // after Alpine has processed this element's x-data — handle
                // both orderings rather than assuming Alpine won the race.
                if (window.Alpine) {
                    attach();
                } else {
                    document.addEventListener('alpine:initialized', attach);
                }
            };
        </script>
    @endif
@endonce

<div id="booking-widget" data-booking-widget
    @select-vehicle-category.window="vehicleCategory = $event.detail"
    @select-route.window="pickup = $event.detail.pickup; dropoff = $event.detail.dropoff"
    x-data="bookingSearchBox({
        categories: {{ \Illuminate\Support\Js::from($categories) }},
        quoteUrl: {{ \Illuminate\Support\Js::from(route('booking.quote')) }},
        initial: {
            {{-- "Rebook" links (from a past trip) prefill via query string; normal
                 validation-error redisplay prefills via old() — old() wins if both
                 are somehow present, since it reflects the user's own last input. --}}
            pickup: {{ \Illuminate\Support\Js::from(old('pickup_location', request()->query('pickup'))) }},
            dropoff: {{ \Illuminate\Support\Js::from(old('dropoff_location', request()->query('dropoff'))) }},
            date: {{ \Illuminate\Support\Js::from(old('pickup_date')) }},
            time: {{ \Illuminate\Support\Js::from(old('pickup_time')) }},
            passengers: {{ \Illuminate\Support\Js::from((int) old('passengers', 1)) }},
            luggage: {{ \Illuminate\Support\Js::from((int) old('luggage', 0)) }},
            vehicleCategory: {{ \Illuminate\Support\Js::from(old('vehicle_category_id', request()->query('vehicle_category', ''))) }},
            type: {{ \Illuminate\Support\Js::from(old('type', 'one_way')) }},
            hours: {{ \Illuminate\Support\Js::from((int) old('hours', 2)) }},
            returnDate: {{ \Illuminate\Support\Js::from(old('return_date')) }},
            returnTime: {{ \Illuminate\Support\Js::from(old('return_time')) }},
            stops: {{ \Illuminate\Support\Js::from(!empty($initialStops) ? array_values($initialStops) : []) }},
            name: {{ \Illuminate\Support\Js::from(old('name')) }},
            email: {{ \Illuminate\Support\Js::from(old('email')) }},
            phone: {{ \Illuminate\Support\Js::from(old('phone')) }},
        },
    })"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-luxury-border bg-luxury-black p-4 shadow-2xl shadow-black/40 sm:p-5 '.$class]) }}>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('booking.store') }}" @submit="handleSubmit($event)" class="space-y-3">
        @csrf

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
            {{-- Pickup --}}
            <div class="lg:col-span-3">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" />
                    {{ __('Pickup Location') }}
                </label>
                <div class="flex items-center gap-1.5 rounded-lg border border-luxury-border bg-luxury-black/40 pe-1.5 focus-within:border-luxury-gold focus-within:ring-1 focus-within:ring-luxury-gold">
                    <input type="text" name="pickup_location" x-ref="pickupInput" x-model="pickup" required placeholder="{{ __('Airport, hotel, address...') }}"
                        @input="pickupLat = null; pickupLng = null; pickupPlaceId = null"
                        class="w-full border-0 bg-transparent px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:outline-none focus:ring-0">
                    @if ($voiceSearchEnabled)
                        <button type="button" @click="startVoiceSearch" aria-label="{{ __('Voice search') }}"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-gold">
                            <x-icon name="mic" class="h-4 w-4" />
                        </button>
                    @endif
                </div>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_location') }}</p>
            </div>

            {{-- Dropoff --}}
            <div class="lg:col-span-3">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" />
                    {{ __('Drop-off Location') }}
                </label>
                <input type="text" name="dropoff_location" x-ref="dropoffInput" x-model="dropoff" :required="type !== 'hourly'" placeholder="{{ __('Destination address') }}"
                    @input="dropoffLat = null; dropoffLng = null; dropoffPlaceId = null"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('dropoff_location') }}</p>
            </div>

            {{-- Vehicle Category --}}
            <div class="lg:col-span-3">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="car" class="h-3.5 w-3.5" />
                    {{ __('Vehicle Category') }}
                </label>
                <select name="vehicle_category_id" x-model="vehicleCategory"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('vehicle_category_id') }}</p>
            </div>

            {{-- Type --}}
            <div class="lg:col-span-3">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="car" class="h-3.5 w-3.5" />
                    {{ __('Type') }}
                </label>
                <select name="type" x-model="type"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    @foreach ($serviceTypes as $serviceType)
                        <option value="{{ $serviceType['value'] }}">{{ $serviceType['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('type') }}</p>
            </div>
        </div>

        {{-- Extra details for Hourly / Round Trip --}}
        <div x-show="type === 'hourly' || type === 'round_trip'" x-cloak class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div x-show="type === 'hourly'" x-cloak>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    {{ __('Number of Hours') }}
                </label>
                <input type="number" name="hours" x-model.number="hours" min="1" max="24"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('hours') }}</p>
            </div>

            <div x-show="type === 'round_trip'" x-cloak>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    {{ __('Return Date') }}
                </label>
                <input type="date" name="return_date" x-model="returnDate" :required="type === 'round_trip'" :min="date || today"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold [color-scheme:dark]">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_date') }}</p>
            </div>

            <div x-show="type === 'round_trip'" x-cloak>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    {{ __('Return Time') }}
                </label>
                <input type="time" name="return_time" x-model="returnTime" :required="type === 'round_trip'"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold [color-scheme:dark]">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('return_time') }}</p>
            </div>
        </div>

        {{-- Stops --}}
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label class="flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" />
                    {{ __('Stops (optional)') }}
                </label>
                <button type="button" @click="stops.push('')" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                    + {{ __('Add Stop') }}
                </button>
            </div>
            <template x-for="(stop, index) in stops" :key="index">
                <div class="mb-2 flex items-center gap-2">
                    <input type="text" :name="'stops[' + index + ']'" x-model="stops[index]" placeholder="{{ __('Stop address') }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-2.5 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <button type="button" @click="stops.splice(index, 1)" aria-label="{{ __('Remove stop') }}"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10">
                        <x-icon name="close" class="h-4 w-4" />
                    </button>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            {{-- Date --}}
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    {{ __('Date') }}
                </label>
                <input type="date" name="pickup_date" x-model="date" required :min="today"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold [color-scheme:dark]">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_date') }}</p>
            </div>

            {{-- Time --}}
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    {{ __('Time') }}
                </label>
                <input type="time" name="pickup_time" x-model="time" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold [color-scheme:dark]">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('pickup_time') }}</p>
            </div>

            {{-- Passengers --}}
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="users" class="h-3.5 w-3.5" />
                    {{ __('Passengers') }}
                </label>
                <div class="flex items-center justify-between rounded-lg border border-luxury-border bg-luxury-black/40 px-2 py-1.5">
                    <button type="button" @click="incrementPassengers(-1)" aria-label="{{ __('Decrease passengers') }}"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">&minus;</button>
                    <span class="text-sm font-medium text-luxury-white" x-text="passengers"></span>
                    <input type="hidden" name="passengers" :value="passengers">
                    <button type="button" @click="incrementPassengers(1)" aria-label="{{ __('Increase passengers') }}"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">+</button>
                </div>
            </div>

            {{-- Luggage --}}
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="briefcase" class="h-3.5 w-3.5" />
                    {{ __('Luggage') }}
                </label>
                <div class="flex items-center justify-between rounded-lg border border-luxury-border bg-luxury-black/40 px-2 py-1.5">
                    <button type="button" @click="incrementLuggage(-1)" aria-label="{{ __('Decrease luggage') }}"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">&minus;</button>
                    <span class="text-sm font-medium text-luxury-white" x-text="luggage"></span>
                    <input type="hidden" name="luggage" :value="luggage">
                    <button type="button" @click="incrementLuggage(1)" aria-label="{{ __('Increase luggage') }}"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-luxury-muted transition hover:bg-luxury-graphite hover:text-luxury-white">+</button>
                </div>
            </div>
        </div>

        {{-- Hidden fields populated by Google Places / the live quote --}}
        <input type="hidden" name="pickup_lat" :value="pickupLat ?? ''">
        <input type="hidden" name="pickup_lng" :value="pickupLng ?? ''">
        <input type="hidden" name="pickup_place_id" :value="pickupPlaceId ?? ''">
        <input type="hidden" name="dropoff_lat" :value="dropoffLat ?? ''">
        <input type="hidden" name="dropoff_lng" :value="dropoffLng ?? ''">
        <input type="hidden" name="dropoff_place_id" :value="dropoffPlaceId ?? ''">
        <input type="hidden" name="distance_km" :value="distanceKm ?? ''">

        {{-- Live Quote --}}
        <div x-show="calculating" x-cloak class="rounded-lg border border-luxury-border bg-luxury-black/40 px-4 py-3 text-center text-sm text-luxury-muted">
            {{ __('Calculating...') }}
        </div>

        <div x-show="quoteError" x-cloak class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-center text-sm text-red-400" x-text="quoteError"></div>

        <div x-show="quote && !calculating" x-cloak class="space-y-3 rounded-lg border border-luxury-border bg-luxury-black/40 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-luxury-muted">{{ __('Estimated Fare') }}</p>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Distance') }}</dt>
                    <dd class="text-luxury-white" x-text="distanceKm ? distanceKm + ' km' : '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Duration') }}</dt>
                    <dd class="text-luxury-white" x-text="durationMinutes ? durationMinutes + ' min' : '—'"></dd>
                </div>
                <div>
                    <dt class="text-xs text-luxury-muted">{{ __('Vehicle') }}</dt>
                    <dd class="truncate text-luxury-white" x-text="vehicleName || '—'"></dd>
                </div>
                <div x-show="quote && quote.base_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Base Fare') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.base_fare) : ''"></dd>
                </div>
                <div x-show="quote && quote.distance_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Distance Cost') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.distance_fare) : ''"></dd>
                </div>
                <div x-show="quote && quote.hour_fare > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Hourly Cost') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(quote.hour_fare) : ''"></dd>
                </div>
                <div x-show="quote && extraCharges() > 0">
                    <dt class="text-xs text-luxury-muted">{{ __('Extra Charges') }}</dt>
                    <dd class="text-luxury-white" x-text="quote ? money(extraCharges()) : ''"></dd>
                </div>
            </dl>

            <div class="border-t border-luxury-border/60 pt-3 text-center">
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Estimated Total') }}</p>
                <p class="text-3xl font-bold text-luxury-gold" x-text="quote ? money(quote.total) : ''"></p>
            </div>

            <p class="text-center text-[11px] leading-snug text-luxury-muted">
                {{ __('Final fare may vary depending on traffic, waiting time, tolls and additional services.') }}
            </p>
        </div>

        {{-- Guest contact details --}}
        <div class="grid grid-cols-1 gap-3 border-t border-luxury-border/60 pt-3 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-luxury-muted">{{ __('Full Name') }}</label>
                <input type="text" name="name" x-model="name" required placeholder="{{ __('Your name') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('name') }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-luxury-muted">{{ __('Email') }}</label>
                <input type="email" name="email" x-model="email" required placeholder="{{ __('you@example.com') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('email') }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-luxury-muted">{{ __('Phone (optional)') }}</label>
                <input type="tel" name="phone" x-model="phone" placeholder="{{ __('+1 555 123 4567') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-1">
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98] sm:w-auto sm:flex-1">
                <x-icon name="search" class="h-4 w-4" />
                {{ __('Book Now') }}
            </button>
        </div>
    </form>
</div>

<script>
    function bookingSearchBox(config) {
        return {
            pickup: config.initial.pickup || '',
            dropoff: config.initial.dropoff || '',
            date: config.initial.date || '',
            time: config.initial.time || '',
            passengers: config.initial.passengers || 1,
            luggage: config.initial.luggage || 0,
            vehicleCategory: config.initial.vehicleCategory || (config.categories[0]?.id ?? ''),
            type: config.initial.type || 'one_way',
            hours: config.initial.hours || 2,
            returnDate: config.initial.returnDate || '',
            returnTime: config.initial.returnTime || '',
            stops: config.initial.stops.length ? config.initial.stops : [],
            name: config.initial.name || '',
            email: config.initial.email || '',
            phone: config.initial.phone || '',
            today: new Date().toISOString().split('T')[0],

            // Populated by Google Places Autocomplete (see initAutocomplete()).
            pickupLat: null,
            pickupLng: null,
            pickupPlaceId: null,
            dropoffLat: null,
            dropoffLng: null,
            dropoffPlaceId: null,

            // Live quote state.
            distanceKm: null,
            durationMinutes: null,
            vehicleName: null,
            quote: null,
            calculating: false,
            quoteError: null,
            quoteDebounce: null,
            quoteSignature: null,
            quoteRequestId: 0,

            init() {
                this.$watch('pickup', () => this.scheduleQuote());
                this.$watch('dropoff', () => this.scheduleQuote());
                this.$watch('pickupLat', () => this.scheduleQuote());
                this.$watch('dropoffLat', () => this.scheduleQuote());
                this.$watch('vehicleCategory', () => this.scheduleQuote());
                this.$watch('type', () => this.scheduleQuote());
                this.$watch('hours', () => this.scheduleQuote());
                this.$watch('passengers', () => this.scheduleQuote());
                this.$watch('date', () => this.scheduleQuote());
                this.$watch('time', () => this.scheduleQuote());

                // The Google Maps script may already be loaded (e.g. back-forward
                // cache) by the time this component initializes.
                if (window.google?.maps?.places) {
                    this.initAutocomplete();
                }
            },

            initAutocomplete() {
                if (!window.google?.maps?.places || this.autocompleteReady) return;
                this.autocompleteReady = true;

                const pickupAutocomplete = new google.maps.places.Autocomplete(this.$refs.pickupInput, { fields: ['place_id', 'geometry', 'formatted_address'] });
                pickupAutocomplete.addListener('place_changed', () => {
                    const place = pickupAutocomplete.getPlace();
                    if (!place.geometry) return;
                    this.pickup = place.formatted_address || this.pickup;
                    this.pickupLat = place.geometry.location.lat();
                    this.pickupLng = place.geometry.location.lng();
                    this.pickupPlaceId = place.place_id || null;
                });

                const dropoffAutocomplete = new google.maps.places.Autocomplete(this.$refs.dropoffInput, { fields: ['place_id', 'geometry', 'formatted_address'] });
                dropoffAutocomplete.addListener('place_changed', () => {
                    const place = dropoffAutocomplete.getPlace();
                    if (!place.geometry) return;
                    this.dropoff = place.formatted_address || this.dropoff;
                    this.dropoffLat = place.geometry.location.lat();
                    this.dropoffLng = place.geometry.location.lng();
                    this.dropoffPlaceId = place.place_id || null;
                });
            },

            incrementPassengers(delta) {
                this.passengers = Math.min(20, Math.max(1, this.passengers + delta));
            },
            incrementLuggage(delta) {
                this.luggage = Math.min(20, Math.max(0, this.luggage + delta));
            },

            notify(type, message) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type, message } }));
            },

            hasRequiredFields() {
                const hasDestination = this.type === 'hourly' || this.dropoff;
                const hasReturnDetails = this.type !== 'round_trip' || (this.returnDate && this.returnTime);

                return this.pickup && hasDestination && this.date && this.time && hasReturnDetails;
            },

            startVoiceSearch() {
                this.notify('success', @json(__('Voice search is coming soon.')));
            },

            handleSubmit(event) {
                if (!this.hasRequiredFields()) {
                    event.preventDefault();
                    this.notify('error', @json(__('Please fill in pickup, drop-off, date and time.')));
                }
            },

            canQuote() {
                if (!this.vehicleCategory || !this.type) return false;
                if (this.type === 'hourly') return !!this.hours;

                return !!(this.pickupLat && this.pickupLng && this.dropoffLat && this.dropoffLng);
            },

            money(value) {
                return '$' + Number(value).toFixed(2);
            },

            extraCharges() {
                if (!this.quote) return 0;

                return (this.quote.waiting_charge || 0) + (this.quote.night_charge || 0) + (this.quote.weekend_charge || 0)
                    + (this.quote.toll_charge || 0) + (this.quote.airport_surcharge || 0) + (this.quote.service_fee || 0)
                    + (this.quote.extra_passenger_charge || 0);
            },

            scheduleQuote() {
                clearTimeout(this.quoteDebounce);

                if (!this.canQuote()) {
                    this.quote = null;
                    this.quoteError = null;
                    this.calculating = false;
                    return;
                }

                this.quoteDebounce = setTimeout(() => this.fetchQuote(), 500);
            },

            fetchQuote() {
                const payload = {
                    vehicle_category_id: this.vehicleCategory,
                    type: this.type,
                    pickup_location: this.pickup,
                    pickup_lat: this.pickupLat,
                    pickup_lng: this.pickupLng,
                    pickup_place_id: this.pickupPlaceId,
                    dropoff_location: this.dropoff,
                    dropoff_lat: this.dropoffLat,
                    dropoff_lng: this.dropoffLng,
                    dropoff_place_id: this.dropoffPlaceId,
                    hours: this.type === 'hourly' ? this.hours : null,
                    passengers: this.passengers,
                    pickup_date: this.date,
                    pickup_time: this.time,
                };

                const signature = JSON.stringify(payload);
                if (signature === this.quoteSignature && (this.quote || this.calculating)) return;
                this.quoteSignature = signature;

                const requestId = ++this.quoteRequestId;
                this.calculating = true;
                this.quoteError = null;

                const token = this.$el.querySelector('input[name="_token"]')?.value;

                fetch(config.quoteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(async (response) => {
                        const json = await response.json().catch(() => null);
                        if (requestId !== this.quoteRequestId) return; // a newer request has since superseded this one

                        if (!response.ok) {
                            this.quote = null;
                            this.quoteError = json?.message || @json(__('Unable to calculate distance.'));
                            return;
                        }

                        this.distanceKm = json.distance_km;
                        this.durationMinutes = json.duration_minutes;
                        this.vehicleName = json.vehicle_name;
                        this.quote = json.breakdown;
                        this.quoteError = null;
                    })
                    .catch(() => {
                        if (requestId !== this.quoteRequestId) return;
                        this.quote = null;
                        this.quoteError = @json(__('Unable to calculate distance.'));
                    })
                    .finally(() => {
                        if (requestId === this.quoteRequestId) this.calculating = false;
                    });
            },
        };
    }
</script>
@endif
