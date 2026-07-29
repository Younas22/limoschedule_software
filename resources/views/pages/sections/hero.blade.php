@props(['section'])

<section class="relative flex min-h-[100svh] flex-col justify-center overflow-hidden border-b border-luxury-border">
    {{-- Background media --}}
    <div class="absolute inset-0">
        @if ($section->video_url)
            <video class="h-full w-full object-cover animate-ken-burns" autoplay muted loop playsinline
                @if ($section->image_url) poster="{{ $section->image_url }}" @endif>
                <source src="{{ $section->video_url }}" type="video/mp4">
            </video>
        @elseif ($section->image_url)
            <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover animate-ken-burns">
        @else
            <div class="h-full w-full bg-gradient-to-br from-luxury-charcoal via-luxury-black to-luxury-black"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-luxury-black via-luxury-black/75 to-luxury-black/30"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-luxury-black/50 via-transparent to-luxury-black/50"></div>
    </div>

    {{-- Content --}}
    <div class="relative mx-auto flex w-full max-w-5xl flex-1 flex-col items-center justify-center px-4 py-28 text-center sm:px-6 lg:px-8">
        @if ($section->heading)
            <h1 class="animate-fade-up text-4xl font-semibold leading-tight tracking-tight text-luxury-white sm:text-6xl lg:text-7xl">
                {{ __($section->heading) }}
            </h1>
        @endif

        @if ($section->subheading)
            <p class="animate-fade-up delay-1 mx-auto mt-6 max-w-2xl text-base text-luxury-muted sm:text-lg">
                {{ __($section->subheading) }}
            </p>
        @endif

        @if (($section->button_text && $section->button_url) || ($section->button_text_2 && $section->button_url_2) || setting('phone'))
            <div class="animate-fade-up delay-2 mt-9 flex flex-row items-center justify-center gap-3">
                @if ($section->button_text && $section->button_url)
                    <a href="{{ str_starts_with($section->button_url, 'http') ? $section->button_url : url($section->button_url) }}" @if (str_starts_with($section->button_url, 'http')) target="_blank" rel="noopener" @endif
                        class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-luxury-gold px-4 py-3 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98] sm:flex-none sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm">
                        <x-icon name="calendar" class="h-4 w-4 shrink-0" />
                        {{ __($section->button_text) }}
                    </a>
                @endif
                @if ($section->button_text_2 && $section->button_url_2)
                    <a href="{{ str_starts_with($section->button_url_2, 'http') ? $section->button_url_2 : url($section->button_url_2) }}" @if (str_starts_with($section->button_url_2, 'http')) target="_blank" rel="noopener" @endif
                        class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-white/30 bg-white/5 px-4 py-3 text-xs font-semibold text-luxury-white backdrop-blur transition hover:border-luxury-white/60 hover:bg-white/10 sm:flex-none sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm">
                        {{ __($section->button_text_2) }}
                    </a>
                @endif
                @if (setting('phone'))
                    <a href="tel:{{ setting('phone') }}"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-luxury-white/30 bg-white/5 px-4 py-3 text-xs font-semibold text-luxury-white backdrop-blur transition hover:border-luxury-white/60 hover:bg-white/10 sm:flex-none sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm">
                        <x-icon name="phone" class="h-4 w-4 shrink-0" />
                        {{ __('Call Now') }}
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Booking search box (renders nothing when website booking is off) --}}
    @if (booking_setting('website_booking_enabled') && booking_setting('guest_booking_enabled'))
        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 pb-12 sm:px-6 lg:px-8">
            <x-booking-search-box />
        </div>
    @endif
</section>
