@props(['title' => null, 'description' => null, 'currentSlug' => null, 'ogImage' => null, 'ogType' => 'website', 'publishedTime' => null])

@php
    $navPages = $navPages ?? \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    $pageTitle = $title ? $title.' — '.setting('company_name', config('app.name', 'Limo Schedule')) : setting('company_name', config('app.name', 'Limo Schedule'));
    $metaDescription = $description ?: setting('meta_description') ?: setting('tagline');
    $resolvedOgImage = $ogImage ?: setting('og_image_url') ?: setting('logo_url');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}"
    data-theme="{{ setting('theme_mode', 'dark') }}" class="{{ setting('theme_mode', 'dark') === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if (setting('meta_keywords'))
        <meta name="keywords" content="{{ setting('meta_keywords') }}">
    @endif
    @if (setting('google_site_verification'))
        <meta name="google-site-verification" content="{{ setting('google_site_verification') }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($resolvedOgImage)
        <meta property="og:image" content="{{ $resolvedOgImage }}">
    @endif
    @if ($ogType === 'article' && $publishedTime)
        <meta property="article:published_time" content="{{ $publishedTime }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if ($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    @if ($resolvedOgImage)
        <meta name="twitter:image" content="{{ $resolvedOgImage }}">
    @endif

    <meta name="theme-color" content="{{ setting('primary_color', '#c9a24b') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    @if (setting('favicon_url'))
        <link rel="icon" href="{{ setting('favicon_url') }}">
        <link rel="apple-touch-icon" href="{{ setting('favicon_url') }}">
    @endif

    {{-- Organization structured data --}}
    <script type="application/ld+json">{!! json_encode(organization_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('admin.partials.theme-vars')

    {{-- Alpine plugins must load before the core Alpine script. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-luxury-black font-sans text-luxury-white antialiased">
    <x-page-progress />

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-luxury-gold focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-luxury-black">
        {{ __('Skip to content') }}
    </a>

    <div x-data="{ sidebarOpen: false, searchOpen: false }" @keydown.escape.window="sidebarOpen = false">
        <x-notifications />

        <x-header :nav-pages="$navPages" :current-slug="$currentSlug" />

        <x-sidebar :nav-pages="$navPages" :current-slug="$currentSlug" />

        <main id="main-content">
            {{ $slot }}
        </main>

        <x-footer :nav-pages="$navPages" />

        <x-sticky-booking-button />

        <x-bottom-nav :current-slug="$currentSlug" />

        <x-search-modal />
    </div>
</body>
</html>
