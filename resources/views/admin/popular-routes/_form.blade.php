@php $route = $route ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="type" value="Route Type" />
        <select id="type" name="type" required
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition sm:max-w-sm">
            @foreach (\App\Models\PopularRoute::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $route?->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-admin.input-error :messages="$errors->get('type')" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="pickup" value="Pickup Location" />
            <x-admin.text-input id="pickup" name="pickup" type="text" value="{{ old('pickup', $route?->pickup) }}" placeholder="e.g. JFK Airport" required autofocus />
            <x-admin.input-error :messages="$errors->get('pickup')" />
        </div>

        <div>
            <x-admin.input-label for="dropoff" value="Dropoff Location" />
            <x-admin.text-input id="dropoff" name="dropoff" type="text" value="{{ old('dropoff', $route?->dropoff) }}" placeholder="e.g. Manhattan, NYC" required />
            <x-admin.input-error :messages="$errors->get('dropoff')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <x-admin.input-label for="distance" value="Estimated Distance" />
            <x-admin.text-input id="distance" name="distance" type="number" step="0.01" min="0" value="{{ old('distance', $route?->distance) }}" placeholder="e.g. 25" />
            <x-admin.input-error :messages="$errors->get('distance')" />
        </div>

        <div>
            <x-admin.input-label for="distance_unit" value="Unit" />
            <select id="distance_unit" name="distance_unit"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="km" @selected(old('distance_unit', $route?->distance_unit ?? 'km') === 'km')>Kilometers (km)</option>
                <option value="mi" @selected(old('distance_unit', $route?->distance_unit ?? 'km') === 'mi')>Miles (mi)</option>
            </select>
            <x-admin.input-error :messages="$errors->get('distance_unit')" />
        </div>

        <div>
            <x-admin.input-label for="estimated_price" value="Estimated Price" />
            <x-admin.text-input id="estimated_price" name="estimated_price" type="number" step="0.01" min="0" value="{{ old('estimated_price', $route?->estimated_price) }}" placeholder="e.g. 75.00" />
            <x-admin.input-error :messages="$errors->get('estimated_price')" />
        </div>
    </div>
</div>
