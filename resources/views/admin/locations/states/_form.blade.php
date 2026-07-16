@php $model = $model ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="country_id" value="Country" />
            <select id="country_id" name="country_id" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="">Select a country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected(old('country_id', $model?->country_id) == $country->id)>{{ $country->name }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('country_id')" />
        </div>

        <div>
            <x-admin.input-label for="code" value="State Code" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $model?->code) }}" placeholder="e.g. CA" maxlength="10" />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>

        <div class="sm:col-span-2">
            <x-admin.input-label for="name" value="State Name" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $model?->name) }}" placeholder="e.g. California" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>
    </div>

    <x-admin.google-fields :model="$model" />
</div>
