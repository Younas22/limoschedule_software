@props(['button', 'size' => 'full'])

@php
    $kind = $button['kind'] ?? 'custom';

    $label = $button['label'] ?: match ($kind) {
        'book_now' => __('Book Now'),
        'call_now' => __('Call Now'),
        'phone_number' => setting('phone'),
        'price' => __('Pricing'),
        default => __('Learn More'),
    };

    $icon = match ($kind) {
        'book_now' => 'calendar',
        'call_now', 'phone_number' => 'phone',
        'price' => 'cash',
        default => null,
    };

    $isPrimary = $kind === 'book_now';

    // Per-button, per-device visibility — an admin-set pair of toggles
    // rather than a single show/hide, so e.g. a phone-number button can be
    // mobile-only while Book Now stays on both.
    $showMobile = $button['show_mobile'] ?? true;
    $showDesktop = $button['show_desktop'] ?? true;
    $visibilityClass = ($showMobile ? 'inline-flex' : 'hidden').' '.($showDesktop ? 'sm:inline-flex' : 'sm:hidden');

    $sizeClass = $size === 'compact'
        ? 'gap-1.5 whitespace-nowrap rounded-lg px-3.5 py-2.5 text-xs font-semibold sm:px-6 sm:py-3 sm:text-sm'
        : 'flex-1 gap-1.5 whitespace-nowrap rounded-lg px-4 py-3 text-xs font-semibold sm:gap-2 sm:px-7 sm:py-3.5 sm:text-sm';

    $colorClass = $isPrimary
        ? 'bg-luxury-gold text-luxury-black transition hover:bg-luxury-gold-light active:scale-[0.98]'
        : 'border border-luxury-white/30 bg-white/5 text-luxury-white backdrop-blur transition hover:border-luxury-white/60 hover:bg-white/10';

    $classes = $visibilityClass.' items-center justify-center '.$sizeClass.' '.$colorClass;

    $url = $button['url'] ?? null;
    $isExternal = $url && str_starts_with($url, 'http');
@endphp

@if ($kind === 'price')
    {{-- Opens the same pricing modal as the top navbar's Pricing button —
         see components/pricing-modal.blade.php and its `pricingOpen` state
         on the shared public-site Alpine shell. --}}
    <button type="button" @click="pricingOpen = true" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
        @endif
        {{ $label }}
    </button>
@elseif (in_array($kind, ['call_now', 'phone_number'], true))
    @if (setting('phone'))
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}" {{ $attributes->merge(['class' => $classes]) }}>
            @if ($icon)
                <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
            @endif
            {{ $label }}
        </a>
    @endif
@elseif ($url)
    <a href="{{ $isExternal ? $url : url($url) }}" @if ($isExternal) target="_blank" rel="noopener" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon name="{{ $icon }}" class="h-4 w-4 shrink-0" />
        @endif
        {{ $label }}
    </a>
@endif
