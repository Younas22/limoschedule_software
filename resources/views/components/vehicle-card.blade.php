@props(['vehicle'])

@php
    $pricingRule = \App\Models\PricingRule::resolveForVehicle($vehicle);
@endphp

<div {{ $attributes->merge(['class' => '']) }}
    class="flex h-full flex-col overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal transition hover:border-luxury-gold/40">
    {{-- Image --}}
    <div class="relative aspect-[16/10] w-full shrink-0 overflow-hidden bg-luxury-graphite">
        @if ($vehicle->image_url)
            <x-lazy-image :src="$vehicle->image_url" :alt="$vehicle->name" />
        @else
            <div class="flex h-full w-full items-center justify-center">
                <x-icon name="car" class="h-10 w-10 text-luxury-muted" />
            </div>
        @endif

        @if ($vehicle->category)
            <span class="absolute start-3 top-3 rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-luxury-gold backdrop-blur">
                {{ $vehicle->category->name }}
            </span>
        @endif

        <div class="absolute end-3 top-3 flex flex-col items-end gap-2">
            @auth('customer')
                @php $isFavorited = auth('customer')->user()->hasFavorited($vehicle); @endphp
                <form method="POST" action="{{ route('customer.favorites.toggle', $vehicle) }}">
                    @csrf
                    <button type="submit" aria-label="{{ __('Toggle favorite') }}"
                        class="tap-scale flex h-8 w-8 items-center justify-center rounded-full bg-luxury-black/70 backdrop-blur transition {{ $isFavorited ? 'text-luxury-gold' : 'text-luxury-white hover:text-luxury-gold' }}">
                        <x-icon name="heart" class="h-4 w-4" />
                    </button>
                </form>
            @endauth

            @if ($vehicle->average_rating)
                <span class="flex items-center gap-1 rounded-full bg-luxury-black/70 px-2.5 py-1 text-[11px] font-medium text-luxury-white backdrop-blur">
                    <x-icon name="star" class="h-3 w-3 text-luxury-gold" />
                    {{ $vehicle->average_rating }}
                </span>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="flex flex-1 flex-col p-5">
        <p class="font-semibold text-luxury-white">{{ $vehicle->name }}</p>
        <p class="text-xs text-luxury-muted">{{ $vehicle->brand }} {{ $vehicle->model }} &middot; {{ $vehicle->year }}</p>

        {{-- Seats / Luggage --}}
        <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs text-luxury-muted">
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="users" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-white">{{ $vehicle->seats }}</span> {{ __('Seats') }}
            </div>
            <div class="flex items-center justify-center gap-1.5 rounded-lg bg-luxury-graphite py-2">
                <x-icon name="briefcase" class="h-3.5 w-3.5" />
                <span class="font-semibold text-luxury-white">{{ $vehicle->luggage }}</span> {{ __('Bags') }}
            </div>
        </div>

        {{-- Features --}}
        @if (! empty($vehicle->feature_list))
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($vehicle->feature_list as $feature)
                    <span class="rounded-full bg-luxury-graphite px-2.5 py-1 text-[11px] text-luxury-muted">{{ $feature }}</span>
                @endforeach
            </div>
        @endif

        {{-- Pricing --}}
        <div class="mt-4 flex items-center justify-between border-t border-luxury-border pt-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Starting from') }}</p>
                <p class="text-lg font-semibold text-luxury-gold">{{ currency($pricingRule->base_fare) }}</p>
            </div>

            {{-- Book button: smooth-scrolls to the booking widget (if present on this page)
                 and pre-selects this vehicle's category via a shared browser event. --}}
            <a href="#booking-widget"
                @click="window.dispatchEvent(new CustomEvent('select-vehicle-category', { detail: '{{ $vehicle->vehicle_category_id }}' }))"
                class="inline-flex items-center gap-1.5 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]">
                <x-icon name="calendar" class="h-4 w-4" />
                {{ __('Book') }}
            </a>
        </div>
    </div>
</div>
