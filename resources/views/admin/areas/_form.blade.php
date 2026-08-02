@php $area = $area ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="name" :value="__('Area Name')" />
        <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $area?->name) }}" :placeholder="__('e.g. Hasselt')" required autofocus />
        <x-admin.input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-admin.input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" placeholder="{{ __('Optional — shown on this area\'s own detail page. Leave blank to use a default sentence.') }}"
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('description', $area?->description) }}</textarea>
        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('Also shown as a short snippet in the areas list above.') }}</p>
        <x-admin.input-error :messages="$errors->get('description')" />
    </div>
</div>
