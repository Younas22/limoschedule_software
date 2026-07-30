<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-luxury-border bg-luxury-charcoal/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="text-luxury-muted hover:text-luxury-white lg:hidden">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
        </svg>
    </button>

    <div class="hidden flex-1 sm:block">
        <h1 class="text-sm font-medium text-luxury-muted">
            {{ $title ?? __('Overview') }}
        </h1>
    </div>

    <div class="ms-auto flex items-center gap-3">
        @include('admin.partials.currency-switcher')
        @include('admin.partials.locale-switcher')
        @include('admin.partials.notifications')
        @include('admin.partials.profile-menu')
    </div>
</header>
