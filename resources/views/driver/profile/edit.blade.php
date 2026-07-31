<x-driver.layouts.app :title="__('Profile Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Profile Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Update your contact information and photo.') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('driver.profile.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ preview: '{{ $driver->photo_url }}' }">
            <x-admin.input-label value="{{ __('Photo') }}" />
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="{{ __('Photo preview') }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <x-icon name="user" class="h-8 w-8 text-luxury-muted" />
                    </template>
                </div>
                <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>{{ __('Click to upload photo') }}</span>
                    <input type="file" name="photo" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
            </div>
            <x-admin.input-error :messages="$errors->get('photo')" />
        </div>

        <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div>
                <x-admin.input-label for="name" value="{{ __('Full Name') }}" />
                <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $driver->name) }}" required />
                <x-admin.input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-admin.input-label value="{{ __('Email Address') }}" />
                <p class="w-full rounded-lg border border-luxury-border bg-luxury-graphite/40 px-4 py-3 text-sm text-luxury-muted">{{ $driver->email }}</p>
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Contact the administrator to change your email address.') }}</p>
            </div>

            <div>
                <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $driver->phone) }}" />
                <x-admin.input-error :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-admin.input-label for="address" value="{{ __('Address') }}" />
                <textarea id="address" name="address" rows="2"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address', $driver->address) }}</textarea>
                <x-admin.input-error :messages="$errors->get('address')" />
            </div>
        </div>

        <x-admin.button type="submit" variant="primary">
            {{ __('Save Changes') }}
        </x-admin.button>
    </form>
</x-driver.layouts.app>
