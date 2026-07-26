@php
    $whatsappDigits = setting('whatsapp') ? preg_replace('/\D+/', '', setting('whatsapp')) : null;
@endphp

@if ($whatsappDigits)
    <a href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode(__("Hi! I'd like to inquire about booking a ride.")) }}" target="_blank" rel="noopener" aria-label="{{ __('Chat on WhatsApp') }}"
        class="tap-scale fixed bottom-24 start-4 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-black/30 transition hover:brightness-105 hover:shadow-2xl lg:bottom-8 lg:start-8">
        <x-whatsapp-icon class="h-7 w-7" />
    </a>
@endif
