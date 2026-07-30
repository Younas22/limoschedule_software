@php
    $channels = [
        'email_enabled' => __('Email'),
        'admin_panel_enabled' => __('Admin Panel'),
        'sms_enabled' => __('SMS'),
        'push_enabled' => __('Push'),
    ];
    $futureChannels = ['sms_enabled', 'push_enabled'];
@endphp

<x-admin.layouts.app :title="__('Notification Preferences')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Notification Preferences') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Choose which channels deliver each booking event. SMS and push are wired into the architecture but not yet active.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.notification-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                            <th class="px-6 py-3 font-medium">{{ __('Event') }}</th>
                            @foreach ($channels as $field => $label)
                                <th class="px-6 py-3 text-center font-medium">
                                    {{ $label }}
                                    @if (in_array($field, $futureChannels, true))
                                        <span class="ms-1 rounded-full bg-luxury-slate px-1.5 py-0.5 text-[9px] normal-case text-luxury-muted">{{ __('Soon') }}</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-luxury-border/60">
                        @foreach ($settings as $setting)
                            <tr class="hover:bg-luxury-graphite">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-luxury-white">{{ $setting->label }}</p>
                                </td>
                                @foreach ($channels as $field => $label)
                                    @php $isFuture = in_array($field, $futureChannels, true); @endphp
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center {{ $isFuture ? 'cursor-not-allowed opacity-40' : 'cursor-pointer' }}">
                                            <input type="checkbox"
                                                name="settings[{{ $setting->event_type }}][{{ $field }}]"
                                                value="1"
                                                @checked($setting->{$field})
                                                @disabled($isFuture)
                                                class="peer sr-only">
                                            <span class="h-6 w-11 rounded-full bg-luxury-slate transition peer-checked:bg-luxury-gold"></span>
                                            <span class="absolute start-1 top-1 h-4 w-4 rounded-full bg-luxury-white transition-transform peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                {{ __('Cancel') }}
            </a>
            <x-admin.button type="submit" variant="primary">{{ __('Save Preferences') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
