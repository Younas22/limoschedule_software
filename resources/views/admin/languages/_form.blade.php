@php $language = $language ?? null; @endphp

<div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="name" value="Language Name" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $language?->name) }}" placeholder="e.g. English" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-admin.input-label for="native_name" value="Native Name" />
            <x-admin.text-input id="native_name" name="native_name" type="text" value="{{ old('native_name', $language?->native_name) }}" placeholder="e.g. العربية" required />
            <x-admin.input-error :messages="$errors->get('native_name')" />
        </div>

        <div>
            <x-admin.input-label for="code" value="Language Code" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $language?->code) }}" placeholder="e.g. en, ar, ur, de" maxlength="10" required />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>

        <div>
            <x-admin.input-label for="direction" value="Text Direction" />
            <select id="direction" name="direction" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                <option value="ltr" @selected(old('direction', $language?->direction ?? 'ltr') === 'ltr')>Left to Right (LTR)</option>
                <option value="rtl" @selected(old('direction', $language?->direction) === 'rtl')>Right to Left (RTL)</option>
            </select>
            <x-admin.input-error :messages="$errors->get('direction')" />
        </div>
    </div>

    <div class="mt-5" x-data="{ preview: '{{ $language?->flag_url }}' }">
        <x-admin.input-label value="Flag Icon" />
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite">
                <template x-if="preview">
                    <img :src="preview" alt="Flag preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <span class="text-xs text-luxury-muted">None</span>
                </template>
            </div>
            <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                <span>Click to upload flag icon</span>
                <input type="file" name="flag" accept="image/*" class="hidden"
                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
            </label>
        </div>
        <p class="mt-2 text-xs text-luxury-muted">Optional. Square image recommended. Max 512KB.</p>
        <x-admin.input-error :messages="$errors->get('flag')" />
    </div>
</div>
