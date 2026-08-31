@props(['title' => null, 'description' => null])

{{--
    Reusable "enable browser notifications on this device" control — used
    as-is on the admin, customer, and driver sides (see
    admin/notification-settings/edit.blade.php, customer/notifications/
    index.blade.php, driver/settings/edit.blade.php). Handles every state
    the brief calls for: unsupported browser, permission denied/default/
    granted, already-subscribed, and subscribe/unsubscribe round-trips to
    PushSubscriptionController — all client-side, no page reload.
--}}
<div x-data="pushNotificationToggle(@js(config('webpush.public_key')), @js(asset('sw.js')))" x-init="init()"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-luxury-border bg-luxury-charcoal p-6']) }}>
    <div class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-luxury-border bg-luxury-graphite text-luxury-gold">
            <x-icon name="bell" class="h-5 w-5" />
        </span>
        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-luxury-white">{{ $title ?? __('Browser Notifications') }}</h3>
            <p class="mt-1 text-sm text-luxury-muted">{{ $description ?? __('Get instant browser alerts about your bookings — even when this tab is closed.') }}</p>

            <template x-if="state === 'unsupported'">
                <p class="mt-3 text-xs text-luxury-muted">{{ __('Browser notifications are not supported on this browser.') }}</p>
            </template>

            <template x-if="state === 'denied'">
                <p class="mt-3 text-xs text-red-400">{{ __('Notifications are blocked for this site. Allow them from your browser\'s site settings, then reload this page.') }}</p>
            </template>

            <template x-if="state === 'enabled'">
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-400">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        {{ __('Notifications Enabled') }}
                    </span>
                    <button type="button" @click="disable()" :disabled="busy"
                        class="tap-scale rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-red-400/40 hover:text-red-400 disabled:opacity-50">
                        <span x-show="!busy">{{ __('Disable') }}</span>
                        <span x-show="busy" x-cloak>{{ __('Please wait…') }}</span>
                    </button>
                </div>
            </template>

            <template x-if="state === 'default' || state === 'error'">
                <div class="mt-3">
                    <button type="button" @click="enable()" :disabled="busy"
                        class="tap-scale inline-flex items-center gap-2 rounded-lg bg-luxury-gold px-4 py-2 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light disabled:opacity-60">
                        <x-icon name="bell" class="h-4 w-4" />
                        <span x-show="!busy">{{ __('Enable Notifications') }}</span>
                        <span x-show="busy" x-cloak>{{ __('Enabling…') }}</span>
                    </button>
                    <p x-show="state === 'error'" x-cloak class="mt-2 text-xs text-red-400" x-text="errorMessage"></p>
                </div>
            </template>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function pushNotificationToggle(vapidPublicKey, serviceWorkerUrl) {
                return {
                    // states: 'checking' | 'unsupported' | 'denied' | 'default' | 'enabled' | 'error'
                    state: 'checking',
                    busy: false,
                    errorMessage: '',
                    subscription: null,

                    async init() {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                            this.state = 'unsupported';
                            return;
                        }

                        if (Notification.permission === 'denied') {
                            this.state = 'denied';
                            return;
                        }

                        try {
                            const registration = await navigator.serviceWorker.register(serviceWorkerUrl);
                            const existing = await registration.pushManager.getSubscription();

                            if (existing && Notification.permission === 'granted') {
                                this.subscription = existing;
                                this.state = 'enabled';
                            } else {
                                this.state = 'default';
                            }
                        } catch (e) {
                            // Registration itself failing (blocked SW, non-secure
                            // context, etc.) shouldn't break the rest of the page —
                            // just fall back to the "enable" prompt.
                            this.state = 'default';
                        }
                    },

                    async enable() {
                        if (this.busy) return;
                        this.busy = true;
                        this.errorMessage = '';

                        try {
                            const permission = await Notification.requestPermission();

                            if (permission !== 'granted') {
                                this.state = permission === 'denied' ? 'denied' : 'default';
                                return;
                            }

                            const registration = await navigator.serviceWorker.ready;

                            let subscription = await registration.pushManager.getSubscription();

                            if (!subscription) {
                                subscription = await registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey),
                                });
                            }

                            await this.sendToServer('{{ route('push.subscribe') }}', subscription.toJSON());

                            this.subscription = subscription;
                            this.state = 'enabled';
                        } catch (e) {
                            this.state = 'error';
                            this.errorMessage = '{{ __('Something went wrong enabling notifications. Please try again.') }}';
                        } finally {
                            this.busy = false;
                        }
                    },

                    async disable() {
                        if (this.busy || !this.subscription) return;
                        this.busy = true;

                        try {
                            const endpoint = this.subscription.endpoint;
                            await this.subscription.unsubscribe();
                            await this.sendToServer('{{ route('push.unsubscribe') }}', { endpoint });
                            this.subscription = null;
                            this.state = 'default';
                        } catch (e) {
                            this.errorMessage = '{{ __('Could not disable notifications. Please try again.') }}';
                            this.state = 'error';
                        } finally {
                            this.busy = false;
                        }
                    },

                    async sendToServer(url, body) {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            },
                            body: JSON.stringify(body),
                        });

                        if (!response.ok) {
                            throw new Error('Push subscription request failed');
                        }

                        return response.json();
                    },

                    urlBase64ToUint8Array(base64String) {
                        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                        const rawData = window.atob(base64);
                        const outputArray = new Uint8Array(rawData.length);

                        for (let i = 0; i < rawData.length; ++i) {
                            outputArray[i] = rawData.charCodeAt(i);
                        }

                        return outputArray;
                    },
                };
            }
        </script>
    @endpush
@endonce
