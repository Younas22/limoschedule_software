@php $driver = auth()->guard('driver')->user(); @endphp

<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-luxury-border bg-luxury-charcoal/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="tap-scale text-luxury-muted hover:text-luxury-white lg:hidden" aria-label="{{ __('Menu') }}">
        <x-icon name="menu" class="h-6 w-6" />
    </button>

    <div class="hidden flex-1 sm:block">
        <h1 class="text-sm font-medium text-luxury-muted">{{ $title ?? __('Overview') }}</h1>
    </div>

    <div class="ms-auto flex items-center gap-3">
        <form method="POST" action="{{ route('driver.status.toggle') }}">
            @csrf
            <button type="submit"
                class="tap-scale inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $driver->is_online ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $driver->is_online ? 'bg-emerald-400' : 'bg-luxury-muted' }}"></span>
                {{ $driver->is_online ? __('Online') : __('Offline') }}
            </button>
        </form>

        @include('driver.partials.profile-menu')
    </div>
</header>
