@php
    $customer = auth()->guard('customer')->user();

    $navItems = [
        ['label' => __('Dashboard'), 'route' => 'customer.dashboard', 'icon' => 'home'],
        ['label' => __('My Bookings'), 'route' => 'customer.bookings.index', 'icon' => 'car'],
        ['label' => __('Upcoming Trips'), 'route' => 'customer.bookings.upcoming', 'icon' => 'clock'],
        ['label' => __('Completed Trips'), 'route' => 'customer.bookings.completed', 'icon' => 'check-circle'],
        ['label' => __('Cancelled Trips'), 'route' => 'customer.bookings.cancelled', 'icon' => 'close'],
        ['label' => __('Favorites'), 'route' => 'customer.favorites.index', 'icon' => 'heart'],
        ['label' => __('Saved Addresses'), 'route' => 'customer.addresses.index', 'icon' => 'map-pin'],
        // Temporarily hidden from navigation (not ready to launch yet) —
        // routes/controllers/views are untouched, just unlinked from here.
        // ['label' => __('Wallet'), 'route' => 'customer.wallet.index', 'icon' => 'cash'],
        // ['label' => __('Payment Methods'), 'route' => 'customer.payment-methods.index', 'icon' => 'credit-card'],
        ['label' => __('Invoices'), 'route' => 'customer.invoices.index', 'icon' => 'download'],
        // ['label' => __('Notifications'), 'route' => 'customer.notifications.index', 'icon' => 'bell'],
        // ['label' => __('Reviews'), 'route' => 'customer.reviews.index', 'icon' => 'star'],
        ['label' => __('Profile Settings'), 'route' => 'customer.profile.edit', 'icon' => 'user'],
        ['label' => __('Preferences'), 'route' => 'customer.settings.edit', 'icon' => 'settings'],
        ['label' => __('Security'), 'route' => 'customer.security.edit', 'icon' => 'lock'],
        ['label' => __('Support'), 'route' => 'customer.support.index', 'icon' => 'chat'],
    ];
@endphp

<div class="flex h-16 shrink-0 items-center gap-3 border-b border-luxury-border px-6">
    <a href="{{ route('pages.home') }}" class="flex items-center gap-3">
        @if (setting('logo_url'))
            <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" class="h-9 w-9 rounded-lg object-contain">
        @else
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-luxury-gold text-luxury-black font-bold">
                {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold tracking-wide text-luxury-white">{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</p>
            <p class="text-[11px] uppercase tracking-widest text-luxury-muted">{{ __('My Account') }}</p>
        </div>
    </a>
</div>

<nav class="scrollbar-luxury flex-1 space-y-1 overflow-y-auto px-3 py-6">
    @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}"
            class="tap-scale flex items-center gap-3 rounded-lg border-s-2 px-4 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-transparent text-luxury-muted hover:bg-luxury-graphite hover:text-luxury-white' }}">
            <x-icon name="{{ $item['icon'] }}" class="h-5 w-5 shrink-0" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

<div class="space-y-3 border-t border-luxury-border p-4">
    <a href="{{ route('pages.home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-luxury-muted transition hover:text-luxury-white">
        <x-icon name="chevron-left" class="h-3.5 w-3.5 rtl:rotate-180" />
        {{ __('Back to Website') }}
    </a>
    <form method="POST" action="{{ route('customer.logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-2 rounded-lg border border-luxury-border px-3 py-2.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
            {{ __('Sign Out') }}
        </button>
    </form>
</div>
