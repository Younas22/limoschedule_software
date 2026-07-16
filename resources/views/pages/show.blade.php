<x-layouts.public :title="$page->meta_title ?: $page->name" :description="$page->meta_description" :current-slug="$page->slug" :nav-pages="$navPages">
    @forelse ($sections as $section)
        @include('pages.sections.'.str_replace('_', '-', $section->type), ['section' => $section])
    @empty
        <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-luxury-white">{{ $page->name }}</h1>
            <p class="mt-3 text-luxury-muted">This page doesn't have any content yet.</p>
        </div>
    @endforelse
</x-layouts.public>
