@php $route = $route ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="route_type_id" :value="__('Route Type')" />
        <select id="route_type_id" name="route_type_id" required
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition sm:max-w-sm">
            @foreach ($routeTypes as $routeTypeOption)
                <option value="{{ $routeTypeOption->id }}" @selected((int) old('route_type_id', $route?->route_type_id) === $routeTypeOption->id)>{{ $routeTypeOption->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-luxury-muted">
            {{ __('Need a different type?') }} <a href="{{ route('admin.popular-routes.route-types.create') }}" class="text-luxury-gold hover:text-luxury-gold-light">{{ __('Add a route type') }}</a>.
        </p>
        <x-admin.input-error :messages="$errors->get('route_type_id')" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="pickup" :value="__('Pickup Location')" />
            <x-admin.text-input id="pickup" name="pickup" type="text" value="{{ old('pickup', $route?->pickup) }}" :placeholder="__('e.g. JFK Airport')" required autofocus />
            <x-admin.input-error :messages="$errors->get('pickup')" />
        </div>

        <div>
            <x-admin.input-label for="dropoff" :value="__('Dropoff Location')" />
            <x-admin.text-input id="dropoff" name="dropoff" type="text" value="{{ old('dropoff', $route?->dropoff) }}" :placeholder="__('e.g. Manhattan, NYC')" required />
            <x-admin.input-error :messages="$errors->get('dropoff')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="distance" :value="__('Estimated Distance')" />
            <x-admin.text-input id="distance" name="distance" type="number" step="0.01" min="0" value="{{ old('distance', $route?->distance) }}" :placeholder="__('e.g. 25')" />
            <x-admin.input-error :messages="$errors->get('distance')" />
        </div>

        <div>
            <x-admin.input-label for="distance_unit" :value="__('Unit')" />
            <select id="distance_unit" name="distance_unit"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="km" @selected(old('distance_unit', $route?->distance_unit ?? 'km') === 'km')>{{ __('Kilometers (km)') }}</option>
                <option value="mi" @selected(old('distance_unit', $route?->distance_unit ?? 'km') === 'mi')>{{ __('Miles (mi)') }}</option>
            </select>
            <x-admin.input-error :messages="$errors->get('distance_unit')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="estimated_price" :value="__('Estimated Price')" />
            <x-admin.text-input id="estimated_price" name="estimated_price" type="number" step="0.01" min="0" value="{{ old('estimated_price', $route?->estimated_price) }}" :placeholder="__('e.g. 75.00')" />
            <p class="mt-1 text-xs text-luxury-muted">{{ __('The price customers actually pay — shown in gold.') }}</p>
            <x-admin.input-error :messages="$errors->get('estimated_price')" />
        </div>

        <div>
            <x-admin.input-label for="original_price" :value="__('Original Price (optional)')" />
            <x-admin.text-input id="original_price" name="original_price" type="number" step="0.01" min="0" value="{{ old('original_price', $route?->original_price) }}" :placeholder="__('e.g. 95.00')" />
            <p class="mt-1 text-xs text-luxury-muted">{{ __('Only set this to show a discount — it displays with a strikethrough next to the Estimated Price. Leave blank for a normal single price.') }}</p>
            <x-admin.input-error :messages="$errors->get('original_price')" />
        </div>
    </div>
</div>
