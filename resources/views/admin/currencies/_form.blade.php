@php $currency = $currency ?? null; @endphp

<div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-admin.input-label for="name" value="Currency Name" />
            <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $currency?->name) }}" placeholder="e.g. US Dollar" required autofocus />
            <x-admin.input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-admin.input-label for="code" value="Currency Code" />
            <x-admin.text-input id="code" name="code" type="text" value="{{ old('code', $currency?->code) }}" placeholder="e.g. USD" maxlength="3" class="uppercase" required />
            <x-admin.input-error :messages="$errors->get('code')" />
        </div>

        <div>
            <x-admin.input-label for="symbol" value="Symbol" />
            <x-admin.text-input id="symbol" name="symbol" type="text" value="{{ old('symbol', $currency?->symbol) }}" placeholder="e.g. $" maxlength="10" required />
            <x-admin.input-error :messages="$errors->get('symbol')" />
        </div>

        <div>
            <x-admin.input-label for="exchange_rate" value="Exchange Rate" />
            <x-admin.text-input id="exchange_rate" name="exchange_rate" type="number" step="0.000001" min="0.000001"
                value="{{ old('exchange_rate', $currency?->exchange_rate ?? 1) }}"
                @if ($currency?->is_default) readonly @endif required />
            <x-admin.input-error :messages="$errors->get('exchange_rate')" />
            @if ($currency?->is_default)
                <p class="mt-2 text-xs text-luxury-muted">The default currency is always the base rate (1.000000). Set another currency as default to change the base.</p>
            @else
                <p class="mt-2 text-xs text-luxury-muted">Rate relative to the default currency ({{ \App\Models\Currency::default()?->code ?? '—' }} = 1.000000).</p>
            @endif
        </div>
    </div>
</div>
