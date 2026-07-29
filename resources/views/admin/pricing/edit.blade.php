@php
    $isGlobal = is_null($category);
    $title = $isGlobal ? 'Global Default Pricing' : "Pricing — {$category->name}";
    $action = $isGlobal ? route('admin.pricing.global.update') : route('admin.pricing.category.update', $category);
    $formatTime = fn ($value, $default) => $value ? \Illuminate\Support\Carbon::parse($value)->format('H:i') : $default;
@endphp

<x-admin.layouts.app :title="$title">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ $title }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">
            @if ($isGlobal)
                Applied to any fleet category that doesn't have its own custom pricing.
            @else
                Custom pricing rule for the <span class="text-luxury-gold">{{ $category->name }}</span> category. Saving here overrides the global default for this category.
            @endif
        </p>
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Base Pricing --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Base Pricing</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="base_fare" value="Base Fare" />
                        <x-admin.text-input id="base_fare" name="base_fare" type="number" step="0.01" min="0" value="{{ old('base_fare', $rule->base_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('base_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="km_fare" value="KM Fare (per km)" />
                        <x-admin.text-input id="km_fare" name="km_fare" type="number" step="0.01" min="0" value="{{ old('km_fare', $rule->km_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('km_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="hour_fare" value="Hour Fare (hourly bookings)" />
                        <x-admin.text-input id="hour_fare" name="hour_fare" type="number" step="0.01" min="0" value="{{ old('hour_fare', $rule->hour_fare ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('hour_fare')" />
                    </div>
                </div>
            </div>

            {{-- Waiting Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Waiting Charges</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="waiting_charge_per_minute" value="Charge per Minute" />
                        <x-admin.text-input id="waiting_charge_per_minute" name="waiting_charge_per_minute" type="number" step="0.01" min="0" value="{{ old('waiting_charge_per_minute', $rule->waiting_charge_per_minute ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('waiting_charge_per_minute')" />
                    </div>
                    <div>
                        <x-admin.input-label for="free_waiting_minutes" value="Free Waiting Minutes" />
                        <x-admin.text-input id="free_waiting_minutes" name="free_waiting_minutes" type="number" min="0" value="{{ old('free_waiting_minutes', $rule->free_waiting_minutes ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('free_waiting_minutes')" />
                    </div>
                </div>
            </div>

            {{-- Night Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Night Charges</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="night_charge" value="Night Charge" />
                        <x-admin.text-input id="night_charge" name="night_charge" type="number" step="0.01" min="0" value="{{ old('night_charge', $rule->night_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="night_start_time" value="Night Window Start" />
                        <x-admin.text-input id="night_start_time" name="night_start_time" type="time" value="{{ old('night_start_time', $formatTime($rule->night_start_time ?? null, '22:00')) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_start_time')" />
                    </div>
                    <div>
                        <x-admin.input-label for="night_end_time" value="Night Window End" />
                        <x-admin.text-input id="night_end_time" name="night_end_time" type="time" value="{{ old('night_end_time', $formatTime($rule->night_end_time ?? null, '06:00')) }}" required />
                        <x-admin.input-error :messages="$errors->get('night_end_time')" />
                    </div>
                </div>
            </div>

            {{-- Weekend Charges --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Weekend Charges</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-admin.input-label for="weekend_charge" value="Weekend Charge" />
                        <x-admin.text-input id="weekend_charge" name="weekend_charge" type="number" step="0.01" min="0" value="{{ old('weekend_charge', $rule->weekend_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('weekend_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label value="Weekend Days" />
                        @php $weekendDays = old('weekend_days', $rule->weekend_days ?? ['Saturday', 'Sunday']); @endphp
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Models\PricingRule::WEEKDAYS as $day)
                                <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                                    <input type="checkbox" name="weekend_days[]" value="{{ $day }}" @checked(in_array($day, $weekendDays, true))
                                        class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                                    {{ substr($day, 0, 3) }}
                                </label>
                            @endforeach
                        </div>
                        <x-admin.input-error :messages="$errors->get('weekend_days')" />
                    </div>
                </div>
            </div>

            {{-- Included Allowances & Minimum Fare --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Included Allowances &amp; Minimum Fare</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="minimum_fare" value="Minimum Fare" />
                        <x-admin.text-input id="minimum_fare" name="minimum_fare" type="number" step="0.01" min="0" value="{{ old('minimum_fare', $rule->minimum_fare ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">Total never drops below this, if set above 0.</p>
                        <x-admin.input-error :messages="$errors->get('minimum_fare')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_km" value="Included KM" />
                        <x-admin.text-input id="included_km" name="included_km" type="number" step="0.01" min="0" value="{{ old('included_km', $rule->included_km ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">Distance covered by the base fare before per-km charges apply.</p>
                        <x-admin.input-error :messages="$errors->get('included_km')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_hours" value="Included Hours" />
                        <x-admin.text-input id="included_hours" name="included_hours" type="number" step="0.01" min="0" value="{{ old('included_hours', $rule->included_hours ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">Hours covered by the base fare on hourly bookings.</p>
                        <x-admin.input-error :messages="$errors->get('included_hours')" />
                    </div>
                    <div>
                        <x-admin.input-label for="included_passengers" value="Included Passengers" />
                        <x-admin.text-input id="included_passengers" name="included_passengers" type="number" min="0" value="{{ old('included_passengers', $rule->included_passengers ?? 4) }}" required />
                        <x-admin.input-error :messages="$errors->get('included_passengers')" />
                    </div>
                    <div>
                        <x-admin.input-label for="extra_passenger_charge" value="Extra Passenger Charge" />
                        <x-admin.text-input id="extra_passenger_charge" name="extra_passenger_charge" type="number" step="0.01" min="0" value="{{ old('extra_passenger_charge', $rule->extra_passenger_charge ?? 0) }}" required />
                        <p class="mt-1 text-xs text-luxury-muted">Charged per passenger beyond the included count.</p>
                        <x-admin.input-error :messages="$errors->get('extra_passenger_charge')" />
                    </div>
                </div>
            </div>

            {{-- Surcharges & Fees --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Surcharges &amp; Fees</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-admin.input-label for="toll_charge" value="Toll Charge" />
                        <x-admin.text-input id="toll_charge" name="toll_charge" type="number" step="0.01" min="0" value="{{ old('toll_charge', $rule->toll_charge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('toll_charge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="airport_surcharge" value="Airport Surcharge" />
                        <x-admin.text-input id="airport_surcharge" name="airport_surcharge" type="number" step="0.01" min="0" value="{{ old('airport_surcharge', $rule->airport_surcharge ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('airport_surcharge')" />
                    </div>
                    <div>
                        <x-admin.input-label for="service_fee" value="Service Fee" />
                        <x-admin.text-input id="service_fee" name="service_fee" type="number" step="0.01" min="0" value="{{ old('service_fee', $rule->service_fee ?? 0) }}" required />
                        <x-admin.input-error :messages="$errors->get('service_fee')" />
                    </div>
                </div>

                @unless ($isGlobal)
                    <label class="flex items-center gap-2 text-sm text-luxury-muted">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))
                            class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                        Use this custom pricing for {{ $category->name }} (uncheck to fall back to the global default)
                    </label>
                @endunless
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.pricing.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                Cancel
            </a>
            <x-admin.button type="submit" variant="primary">Save Pricing</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
