@php
    $breadcrumbItems = null;

    if ($page->slug !== 'home') {
        $breadcrumbItems = [['label' => __('Home'), 'url' => route('pages.home')]];

        if (in_array($page->slug, \App\Models\Page::SERVICE_PAGES, true) && \Illuminate\Support\Facades\Route::has('pages.show')) {
            $breadcrumbItems[] = ['label' => __('Services'), 'url' => route('pages.show', 'services')];
        }

        $breadcrumbItems[] = ['label' => __($page->name), 'url' => null];
    }
@endphp

<x-layouts.public
    :title="$page->meta_title ?: $page->name"
    :description="$page->meta_description"
    :current-slug="$page->slug"
    :nav-pages="$navPages"
    :og-image="$page->og_image_url"
    :canonical-override="$page->canonical_override"
    :robots-index="$page->robots_index"
    :robots-follow="$page->robots_follow"
>
    @if ($page->custom_schema)
        <x-slot:head>{!! $page->custom_schema !!}</x-slot:head>
    @endif

    @if ($breadcrumbItems)
        <x-breadcrumbs :items="$breadcrumbItems" />
    @endif

    @forelse ($sections as $section)
        @include('pages.sections.'.str_replace('_', '-', $section->type), ['section' => $section, 'page' => $page])
    @empty
        <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-luxury-white">{{ $page->name }}</h1>
            <p class="mt-3 text-luxury-muted">This page doesn't have any content yet.</p>
        </div>
    @endforelse
</x-layouts.public>
