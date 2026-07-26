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
@endphp

<div id="booking-widget"
    @select-vehicle-category.window="vehicleCategory = $event.detail"
    @select-route.window="pickup = $event.detail.pickup; dropoff = $event.detail.dropoff"
    x-data="bookingSearchBox({
        categories: {{ \Illuminate\Support\Js::from($categories) }},
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
            <div class="lg:col-span-4">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" />
                    {{ __('Pickup Location') }}
                </label>
                <div class="flex items-center gap-1.5 rounded-lg border border-luxury-border bg-luxury-black/40 pe-1.5 focus-within:border-luxury-gold focus-within:ring-1 focus-within:ring-luxury-gold">
                    <input type="text" name="pickup_location" x-model="pickup" required placeholder="{{ __('Airport, hotel, address...') }}"
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
            <div class="lg:col-span-4">
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-luxury-muted">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" />
                    {{ __('Drop-off Location') }}
                </label>
                <input type="text" name="dropoff_location" x-model="dropoff" required placeholder="{{ __('Destination address') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-black/40 px-3.5 py-3 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                <p class="mt-1 text-xs text-red-400">{{ $errors->first('dropoff_location') }}</p>
            </div>

            {{-- Vehicle Category --}}
            <div class="lg:col-span-4">
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
            stops: config.initial.stops.length ? config.initial.stops : [],
            name: config.initial.name || '',
            email: config.initial.email || '',
            phone: config.initial.phone || '',
            today: new Date().toISOString().split('T')[0],

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
                return this.pickup && this.dropoff && this.date && this.time;
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
        };
    }
</script>
@endif
