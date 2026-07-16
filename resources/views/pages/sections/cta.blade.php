@props(['section'])

<section class="relative overflow-hidden border-b border-luxury-border">
    @if ($section->image_url)
        <div class="absolute inset-0">
            <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover opacity-20">
        </div>
    @endif

    <div class="relative mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 lg:px-8">
        @if ($section->heading)
            <h2 class="text-2xl font-semibold text-luxury-white sm:text-3xl">{{ $section->heading }}</h2>
        @endif
        @if ($section->subheading)
            <p class="mt-3 text-luxury-muted">{{ $section->subheading }}</p>
        @endif
        @if ($section->button_text && $section->button_url)
            <div class="mt-7">
                <a href="{{ $section->button_url }}" class="inline-flex items-center justify-center rounded-lg bg-luxury-gold px-6 py-3.5 text-sm font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                    {{ $section->button_text }}
                </a>
            </div>
        @endif
    </div>
</section>
