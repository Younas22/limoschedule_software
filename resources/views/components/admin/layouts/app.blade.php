@php $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}" data-theme="{{ setting('theme_mode', 'dark') }}" class="{{ setting('theme_mode', 'dark') === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ setting('company_name', config('app.name', 'Limo Schedule')) }}</title>
    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-luxury-charcoal font-sans text-luxury-white antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/70 lg:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '{{ $direction === 'rtl' ? 'translate-x-full' : '-translate-x-full' }} lg:translate-x-0'"
            class="fixed inset-y-0 start-0 z-40 flex w-64 flex-col border-e border-luxury-border bg-luxury-charcoal transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            @include('admin.partials.sidebar')
        </aside>

        {{-- Main column --}}
        <div class="flex min-h-screen flex-1 flex-col">
            @include('admin.partials.topbar')

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-luxury-gold/30 bg-luxury-gold/10 px-4 py-3 text-sm text-luxury-gold">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            @include('admin.partials.footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
