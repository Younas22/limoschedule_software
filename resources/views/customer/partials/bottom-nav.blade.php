@php
    $customer = auth()->guard('customer')->user();
    $unreadCount = $customer?->unreadNotifications()->count() ?? 0;

    $tabs = [
        ['label' => __('Home'), 'route' => 'customer.dashboard', 'match' => 'customer.dashboard', 'icon' => 'home'],
        ['label' => __('Bookings'), 'route' => 'customer.bookings.index', 'match' => 'customer.bookings.*', 'icon' => 'car'],
        ['label' => __('Wallet'), 'route' => 'customer.wallet.index', 'match' => 'customer.wallet.*', 'icon' => 'wallet'],
        ['label' => __('Alerts'), 'route' => 'customer.notifications.index', 'match' => 'customer.notifications.*', 'icon' => 'bell'],
    ];
@endphp

<nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-luxury-border bg-luxury-charcoal/95 backdrop-blur lg:hidden">
    <div class="grid grid-cols-5">
        @foreach ($tabs as $tab)
            @php $isActive = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}"
                class="tap-scale relative flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ $isActive ? 'text-luxury-gold' : 'text-luxury-muted' }}">
                <span class="relative">
                    <x-icon name="{{ $tab['icon'] }}" class="h-5 w-5" />
                    @if ($tab['icon'] === 'bell' && $unreadCount > 0)
                        <span class="absolute -end-1.5 -top-1.5 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-luxury-gold px-1 text-[9px] font-bold text-luxury-black">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </span>
                {{ $tab['label'] }}
            </a>
        @endforeach

        <button type="button" @click="sidebarOpen = true"
            class="tap-scale flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium text-luxury-muted transition">
            <x-icon name="menu" class="h-5 w-5" />
            {{ __('Menu') }}
        </button>
    </div>
</nav>
