import './bootstrap';

/**
 * "Enable browser notifications on this device" Alpine component — shared by
 * the full settings card (components/push-notification-toggle.blade.php) and
 * the compact topbar badge (components/push-notification-badge.blade.php) on
 * the admin, customer, and driver sides, so there's exactly one copy of this
 * logic instead of one inline <script> per usage site. Lives here (rather
 * than inline in Blade) because both usages can render on the very same page
 * (e.g. a settings page also has the topbar), and Alpine resolves x-data
 * expressions against the global scope — hence the explicit `window.` assignment
 * rather than a plain module-scoped function declaration.
 *
 * Route URLs can't be resolved with Blade's route() helper here since this
 * file is a static, pre-built asset — callers pass them in instead.
 */
window.pushNotificationToggle = function (vapidPublicKey, serviceWorkerUrl, subscribeUrl, unsubscribeUrl, messages) {
    messages = messages || {};
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

                await this.sendToServer(subscribeUrl, subscription.toJSON());

                this.subscription = subscription;
                this.state = 'enabled';
            } catch (e) {
                this.state = 'error';
                this.errorMessage = messages.enableError || 'Something went wrong enabling notifications. Please try again.';
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
                await this.sendToServer(unsubscribeUrl, { endpoint });
                this.subscription = null;
                this.state = 'default';
            } catch (e) {
                this.errorMessage = messages.disableError || 'Could not disable notifications. Please try again.';
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
};
