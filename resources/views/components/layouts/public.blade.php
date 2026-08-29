@props([
    'title' => null,
    'description' => null,
    'currentSlug' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'publishedTime' => null,
    // Per-page SEO overrides — every call site defaults to the
    // site-wide/indexable behavior that was already in place before these
    // existed, so nothing currently indexed changes unless a page opts in.
    'canonicalOverride' => null,
    'robotsIndex' => null,
    'robotsFollow' => null,
])

@php
    $navPages = $navPages ?? \App\Models\Page::where('is_active', true)->get()->keyBy('slug');
    $direction = \App\Models\Language::findActiveByCode(app()->getLocale())?->direction ?? 'ltr';
    $pageTitle = $title ? seo_title($title) : (setting('meta_title') ?: setting('company_name', config('app.name', 'Limo Schedule')));
    $metaDescription = $description ?: setting('meta_description') ?: setting('tagline');
    $resolvedOgImage = $ogImage ?: setting('og_image_url') ?: setting('logo_url');
    $canonicalUrl = $canonicalOverride ?: url()->current();
    $robotsIndex = $robotsIndex ?? setting('default_robots_index', true);
    $robotsFollow = $robotsFollow ?? setting('default_robots_follow', true);
    $robotsContent = ($robotsIndex ? 'index' : 'noindex').', '.($robotsFollow ? 'follow' : 'nofollow');
    $schemaBuilder = app(\App\Services\SchemaBuilder::class);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}"
    {{-- The public marketing site always stays on the luxury dark theme —
         Admin Settings → Light Mode is an admin/customer-panel-only
         preference (Setting::theme_mode) and must never bleed into the
         public site's branding. --}}
    data-theme="dark" class="dark">
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
    <meta name="robots" content="{{ $robotsContent }}">
    @if (setting('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', {{ \Illuminate\Support\Js::from(setting('google_analytics_id')) }});
        </script>
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
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

    <link rel="icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ setting('favicon_url') ?: asset('favicon.ico') }}">

    {{-- Site-wide structured data — LocalBusiness/TaxiService (per the
         configured business type) plus WebSite. Page-specific schema
         (FAQPage, BreadcrumbList) is emitted alongside the content it
         describes instead of threaded through this layout — see
         pages/sections/faq.blade.php and components/breadcrumbs.blade.php. --}}
    <script type="application/ld+json">{!! json_encode($schemaBuilder->organization(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($schemaBuilder->website(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('admin.partials.theme-vars')

    {{-- Windows doesn't render flag emoji, so currency/language flags use this CSS sprite library instead. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

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
        <x-sticky-whatsapp-button />

        <x-bottom-nav :current-slug="$currentSlug" />

        <x-search-modal />
    </div>
</body>
</html>
