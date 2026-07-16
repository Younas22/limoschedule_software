@php
    $search = $search ?? null;
    $heading = $search ? 'Search Results' : (isset($activeCategory) ? $activeCategory->name : (isset($activeTag) ? '#'.$activeTag->name : 'Blog'));
    $description = isset($activeCategory) ? $activeCategory->description : null;
@endphp

<x-layouts.public :title="$heading" :description="$description">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold text-luxury-white sm:text-4xl">{{ $heading }}</h1>
            @if ($search)
                <p class="mx-auto mt-3 max-w-2xl text-luxury-muted">{{ __('Showing results for') }} &ldquo;{{ $search }}&rdquo;</p>
            @elseif ($description)
                <p class="mx-auto mt-3 max-w-2xl text-luxury-muted">{{ $description }}</p>
            @endif
            @if (isset($activeCategory) || isset($activeTag) || $search)
                <a href="{{ route('blog.index') }}" class="mt-3 inline-block text-sm text-luxury-gold hover:text-luxury-gold-light">&larr; Back to all posts</a>
            @endif
        </div>

        <form method="GET" action="{{ route('blog.index') }}" class="mx-auto mb-10 max-w-xl">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-luxury-muted" />
                <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search articles...') }}"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal py-3 ps-11 pe-4 text-sm text-luxury-white placeholder:text-luxury-muted/70 focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            </div>
        </form>

        @if ($featuredPosts->isNotEmpty())
            <div class="mb-12">
                <h2 class="mb-5 text-lg font-semibold text-luxury-white">Featured</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredPosts as $post)
                        <x-blog.post-card :post="$post" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @forelse ($posts as $post)
                        <x-blog.post-card :post="$post" />
                    @empty
                        <p class="col-span-2 py-10 text-center text-luxury-muted">
                            {{ $search ? __('No articles match your search.') : __('No posts found.') }}
                        </p>
                    @endforelse
                </div>

                @if ($posts->hasPages())
                    <div class="mt-8 flex items-center justify-between text-sm text-luxury-muted">
                        <div>
                            @if ($posts->onFirstPage())
                                <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                            @endif
                        </div>
                        <p>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</p>
                        <div>
                            @if ($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                            @else
                                <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <x-blog.sidebar :categories="$categories" :popular-posts="$popularPosts" :tags="$tags" />
        </div>
    </div>
</x-layouts.public>
