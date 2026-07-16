<x-customer.layouts.app :title="__('Saved Addresses')">
    <div x-data="{ showForm: false }">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Saved Addresses') }}</h2>
                <p class="mt-1 text-sm text-luxury-muted">{{ __('Manage your frequent pickup and drop-off locations.') }}</p>
            </div>
            <button type="button" @click="showForm = !showForm" class="tap-scale inline-flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                <x-icon name="calendar" class="h-4 w-4" />
                {{ __('Add Address') }}
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition class="mb-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <form method="POST" action="{{ route('customer.addresses.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <x-admin.input-label for="label" value="Label" />
                <x-admin.text-input id="label" name="label" type="text" placeholder="e.g. Home, Office" value="{{ old('label') }}" required />
                <x-admin.input-error :messages="$errors->get('label')" />
            </div>
            <div>
                <x-admin.input-label for="city_id" value="City" />
                <select id="city_id" name="city_id" class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">{{ __('Select city') }}</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <x-admin.input-label for="address_line" value="Address" />
                <textarea id="address_line" name="address_line" rows="2" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address_line') }}</textarea>
                <x-admin.input-error :messages="$errors->get('address_line')" />
            </div>
            <label class="flex items-center gap-2 text-sm text-luxury-muted">
                <input type="checkbox" name="is_default" value="1" class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold">
                {{ __('Set as default') }}
            </label>
            <div class="sm:col-span-2">
                <x-admin.button type="submit" variant="primary">{{ __('Save Address') }}</x-admin.button>
            </div>
        </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($addresses as $address)
            <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold/10 text-luxury-gold">
                            <x-icon name="map-pin" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-luxury-white">{{ $address->label }}</p>
                            @if ($address->is_default)
                                <span class="text-[11px] font-medium text-luxury-gold">{{ __('Default') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-sm text-luxury-muted">{{ $address->address_line }}</p>
                @if ($address->city)
                    <p class="mt-1 text-xs text-luxury-muted">{{ $address->city->name }}</p>
                @endif

                <div class="mt-4 flex items-center gap-2 border-t border-luxury-border pt-4">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('customer.addresses.default', $address) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">{{ __('Set Default') }}</button>
                        </form>
                        <span class="text-luxury-border">|</span>
                    @endunless
                    <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('{{ __('Remove this address?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-red-400 hover:text-red-300">{{ __('Remove') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-luxury-border bg-luxury-charcoal p-12 text-center">
                <x-icon name="map-pin" class="mx-auto h-10 w-10 text-luxury-muted" />
                <p class="mt-4 text-sm text-luxury-muted">{{ __("You haven't saved any addresses yet.") }}</p>
            </div>
        @endforelse
    </div>
</x-customer.layouts.app>
