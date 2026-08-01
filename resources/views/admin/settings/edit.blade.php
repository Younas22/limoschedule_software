<x-admin.layouts.app :title="__('Global Settings')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Global Settings') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ __('Control your company profile, branding and regional preferences.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Company Information --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Company Information') }}</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="company_name" value="{{ __('Company Name') }}" />
                    <x-admin.text-input id="company_name" name="company_name" type="text" value="{{ old('company_name', $settings->company_name) }}" required autofocus />
                    <x-admin.input-error :messages="$errors->get('company_name')" />
                </div>

                <div>
                    <x-admin.input-label for="tagline" value="{{ __('Tagline') }}" />
                    <x-admin.text-input id="tagline" name="tagline" type="text" value="{{ old('tagline', $settings->tagline) }}" />
                    <x-admin.input-error :messages="$errors->get('tagline')" />
                </div>
            </div>
        </div>

        {{-- Branding --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Branding') }}</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div x-data="{ preview: '{{ $settings->logo_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                    <x-admin.input-label value="{{ __('Logo (White)') }}" />
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-luxury-black">
                            <template x-if="preview">
                                <img :src="preview" alt="{{ __('White logo preview') }}" class="h-full w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-luxury-muted">{{ __('No logo') }}</span>
                            </template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <span>{{ __('Click to upload logo') }}</span>
                            <input type="file" name="logo" accept="image/*" class="hidden"
                                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-luxury-muted">{{ __('For dark backgrounds — site header, admin panel. PNG or SVG recommended. Max 2MB.') }}</p>
                    <x-admin.input-error :messages="$errors->get('logo')" />
                </div>

                <div x-data="{ preview: '{{ $settings->logo_dark_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                    <x-admin.input-label value="{{ __('Logo (Black)') }}" />
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-white">
                            <template x-if="preview">
                                <img :src="preview" alt="{{ __('Black logo preview') }}" class="h-full w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-luxury-muted">{{ __('No logo') }}</span>
                            </template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <span>{{ __('Click to upload logo') }}</span>
                            <input type="file" name="logo_dark" accept="image/*" class="hidden"
                                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-luxury-muted">{{ __('For light backgrounds — invoices, printed documents. PNG or SVG recommended. Max 2MB.') }}</p>
                    <x-admin.input-error :messages="$errors->get('logo_dark')" />
                </div>

                <div x-data="{ preview: '{{ $settings->favicon_url }}' }" class="rounded-xl border border-luxury-border bg-luxury-slate p-4">
                    <x-admin.input-label value="{{ __('Favicon') }}" />
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-luxury-border bg-luxury-graphite">
                            <template x-if="preview">
                                <img :src="preview" alt="{{ __('Favicon preview') }}" class="h-8 w-8 object-contain">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-luxury-muted">{{ __('No favicon') }}</span>
                            </template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <span>{{ __('Click to upload favicon') }}</span>
                            <input type="file" name="favicon" accept="image/*" class="hidden"
                                @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-luxury-muted">{{ __('Square PNG or ICO recommended. Max 512KB.') }}</p>
                    <x-admin.input-error :messages="$errors->get('favicon')" />
                </div>
            </div>

            <div class="mt-6">
                <x-admin.toggle name="invoice_logo_dark" :checked="old('invoice_logo_dark', $settings->invoice_logo_dark)"
                    label="{{ __('Use Black Logo on Invoices') }}" description="{{ __('Off = White logo shown on invoices. On = Black logo shown instead.') }}" />
            </div>
        </div>

        {{-- Contact Details --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Contact Details') }}</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <x-admin.input-label for="email" value="{{ __('Email Address') }}" />
                    <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}" />
                    <x-admin.input-error :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-admin.input-label for="phone" value="{{ __('Phone') }}" />
                    <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $settings->phone) }}" />
                    <x-admin.input-error :messages="$errors->get('phone')" />
                </div>

                <div>
                    <x-admin.input-label for="whatsapp" value="{{ __('WhatsApp Number') }}" />
                    <x-admin.text-input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $settings->whatsapp) }}" />
                    <x-admin.input-error :messages="$errors->get('whatsapp')" />
                </div>
            </div>

            <div class="mt-5">
                <x-admin.input-label for="address" value="{{ __('Address') }}" />
                <textarea id="address" name="address" rows="3"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('address', $settings->address) }}</textarea>
                <x-admin.input-error :messages="$errors->get('address')" />
            </div>

            <div class="mt-5">
                <x-admin.input-label for="google_maps_embed_url" value="{{ __('Google Maps Embed URL (optional)') }}" />
                <x-admin.text-input id="google_maps_embed_url" name="google_maps_embed_url" type="url" value="{{ old('google_maps_embed_url', $settings->google_maps_embed_url) }}" placeholder="https://www.google.com/maps/embed?..." />
                <p class="mt-1 text-xs text-luxury-muted">{{ __('From Google Maps: Share → Embed a map → copy the "src" URL (or paste the whole embed code). Shown on the Contact page. Leave blank to show a placeholder.') }}</p>
                <x-admin.input-error :messages="$errors->get('google_maps_embed_url')" />
            </div>
        </div>

        {{-- Business Hours --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Business Hours') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('Set your opening hours for each day, shown on the Contact page.') }}</p>

            <div class="space-y-2">
                @php $businessHours = old('hours', $settings->business_hours_list); @endphp
                @foreach (\App\Models\Setting::DAYS as $day)
                    @php $dayHours = $businessHours[$day] ?? ['open' => '09:00', 'close' => '18:00', 'closed' => false]; @endphp
                    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/30 px-4 py-3"
                        x-data="{ closed: {{ \Illuminate\Support\Js::from((bool) ($dayHours['closed'] ?? false)) }} }">
                        <p class="w-28 shrink-0 text-sm font-medium text-luxury-white">{{ __(ucfirst($day)) }}</p>

                        <label class="flex shrink-0 items-center gap-2 text-xs text-luxury-muted">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" x-model="closed"
                                class="rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-luxury-gold">
                            {{ __('Closed') }}
                        </label>

                        <div class="flex flex-1 items-center gap-2" x-show="!closed">
                            <input type="time" name="hours[{{ $day }}][open]" value="{{ $dayHours['open'] ?? '09:00' }}" :disabled="closed"
                                class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                            <span class="text-xs text-luxury-muted">{{ __('to') }}</span>
                            <input type="time" name="hours[{{ $day }}][close]" value="{{ $dayHours['close'] ?? '18:00' }}" :disabled="closed"
                                class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        </div>
                        <p class="flex-1 text-xs text-luxury-muted" x-show="closed" x-cloak>{{ __('Closed all day') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Regional --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-4 text-sm font-semibold text-luxury-white">{{ __('Regional') }}</h3>

            <div class="mb-5">
                <x-admin.input-label for="timezone" value="{{ __('Timezone') }}" />
                <select id="timezone" name="timezone" required
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
                <x-admin.input-error :messages="$errors->get('timezone')" />
            </div>

            <div>
                <x-admin.input-label value="{{ __('Date Format') }}" />
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($dateFormats as $format => $example)
                        <label class="cursor-pointer rounded-lg border px-3 py-2.5 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('date_format', $settings->date_format) === $format ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                            <input type="radio" name="date_format" value="{{ $format }}" class="hidden" @checked(old('date_format', $settings->date_format) === $format)>
                            {{ $example }}
                        </label>
                    @endforeach
                </div>
                <x-admin.input-error :messages="$errors->get('date_format')" />
            </div>
        </div>

        {{-- Invoice & Tax --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Invoice & Tax') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('Applied as a tax-inclusive breakdown on booking invoices and payment receipts.') }}</p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="tax_label" value="{{ __('Tax Label') }}" />
                    <x-admin.text-input id="tax_label" name="tax_label" type="text" class="w-full" :value="old('tax_label', $settings->tax_label)" required />
                    <x-admin.input-error :messages="$errors->get('tax_label')" />
                </div>
                <div>
                    <x-admin.input-label for="tax_rate" value="{{ __('Tax Rate (%)') }}" />
                    <x-admin.text-input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="w-full" :value="old('tax_rate', $settings->tax_rate)" required />
                    <x-admin.input-error :messages="$errors->get('tax_rate')" />
                </div>
            </div>
        </div>

        {{-- Theme --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Theme') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('Colors apply to the public website immediately after saving — no rebuild required. The admin panel and customer portal keep their own fixed colors and are not affected.') }}</p>

            <div class="mb-6">
                <x-admin.input-label value="{{ __('Appearance Mode') }}" />
                <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                    <label class="cursor-pointer rounded-lg border px-4 py-3 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('theme_mode', $settings->theme_mode) === 'dark' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                        <input type="radio" name="theme_mode" value="dark" class="hidden" @checked(old('theme_mode', $settings->theme_mode) === 'dark')>
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            {{ __('Dark Mode') }}
                        </span>
                    </label>

                    <label class="cursor-pointer rounded-lg border px-4 py-3 text-center text-sm transition has-[:checked]:border-luxury-gold has-[:checked]:bg-luxury-gold/10 has-[:checked]:text-luxury-gold {{ old('theme_mode', $settings->theme_mode) === 'light' ? 'border-luxury-gold bg-luxury-gold/10 text-luxury-gold' : 'border-luxury-border text-luxury-muted' }}">
                        <input type="radio" name="theme_mode" value="light" class="hidden" @checked(old('theme_mode', $settings->theme_mode) === 'light')>
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            {{ __('Light Mode') }}
                        </span>
                    </label>
                </div>
                <x-admin.input-error :messages="$errors->get('theme_mode')" />
                <p class="mt-2 text-xs text-luxury-muted">{{ __('Tip: pick matching Background & Text colors below for best contrast when switching modes.') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-admin.color-input name="primary_color" label="{{ __('Primary Color') }}" :value="old('primary_color', $settings->primary_color)" />
                <x-admin.color-input name="secondary_color" label="{{ __('Secondary Color') }}" :value="old('secondary_color', $settings->secondary_color)" />
                <x-admin.color-input name="accent_color" label="{{ __('Accent Color') }}" :value="old('accent_color', $settings->accent_color)" />
                <x-admin.color-input name="text_color" label="{{ __('Text Color') }}" :value="old('text_color', $settings->text_color)" />
                <x-admin.color-input name="background_color" label="{{ __('Background Color') }}" :value="old('background_color', $settings->background_color)" />
            </div>
            <x-admin.input-error :messages="$errors->get('primary_color')" class="mt-3" />
            <x-admin.input-error :messages="$errors->get('secondary_color')" class="mt-1" />
            <x-admin.input-error :messages="$errors->get('accent_color')" class="mt-1" />
            <x-admin.input-error :messages="$errors->get('text_color')" class="mt-1" />
            <x-admin.input-error :messages="$errors->get('background_color')" class="mt-1" />
        </div>

        {{-- SEO --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('SEO') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __("Default meta tags used site-wide when an individual page doesn't set its own.") }}</p>

            <div class="mb-5">
                <x-admin.input-label for="meta_title" value="{{ __('Default Meta Title') }}" />
                <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $settings->meta_title) }}" placeholder="{{ __('e.g. Premium Chauffeur & Limousine Service') }}" />
                <p class="mt-1 text-xs text-luxury-muted">{{ __("Used for the homepage and any page that doesn't set its own Meta Title.") }}</p>
                <x-admin.input-error :messages="$errors->get('meta_title')" />
            </div>

            <div class="mb-5">
                <x-admin.input-label for="meta_description" value="{{ __('Default Meta Description') }}" />
                <textarea id="meta_description" name="meta_description" rows="2" maxlength="500"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $settings->meta_description) }}</textarea>
                <x-admin.input-error :messages="$errors->get('meta_description')" />
            </div>

            <div class="mb-5">
                <x-admin.input-label for="meta_keywords" value="{{ __('Meta Keywords (optional, comma-separated)') }}" />
                <x-admin.text-input id="meta_keywords" name="meta_keywords" type="text" value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="chauffeur service, limousine rental, airport transfer" />
                <x-admin.input-error :messages="$errors->get('meta_keywords')" />
            </div>

            <div class="mb-5" x-data="{ preview: '{{ $settings->og_image_url }}' }">
                <x-admin.input-label value="{{ __('Default Social Share Image (Open Graph)') }}" />
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                        <template x-if="preview">
                            <img :src="preview" alt="{{ __('OG image preview') }}" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <span class="text-[10px] text-luxury-muted">{{ __('No image') }}</span>
                        </template>
                    </div>
                    <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                        <span>{{ __('Click to upload image') }}</span>
                        <input type="file" name="og_image" accept="image/*" class="hidden"
                            @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                    </label>
                </div>
                <p class="mt-2 text-xs text-luxury-muted">{{ __('Shown when pages are shared on social media. Recommended 1200×630px. Falls back to your logo if left blank.') }}</p>
                <x-admin.input-error :messages="$errors->get('og_image')" />
            </div>

            <div class="mb-5">
                <x-admin.input-label for="google_site_verification" value="{{ __('Google Search Console Verification Code (optional)') }}" />
                <x-admin.text-input id="google_site_verification" name="google_site_verification" type="text" value="{{ old('google_site_verification', $settings->google_site_verification) }}" placeholder="e.g. abc123XYZ..." />
                <p class="mt-1 text-xs text-luxury-muted">{{ __("Paste just the content value from Google's verification meta tag.") }}</p>
                <x-admin.input-error :messages="$errors->get('google_site_verification')" />
            </div>

            <div>
                <x-admin.input-label for="google_analytics_id" value="{{ __('Google Analytics Measurement ID (optional)') }}" />
                <x-admin.text-input id="google_analytics_id" name="google_analytics_id" type="text" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}" placeholder="e.g. G-XXXXXXXXXX" />
                <p class="mt-1 text-xs text-luxury-muted">{{ __('Your GA4 Measurement ID. The tracking code is added to every page automatically once saved.') }}</p>
                <x-admin.input-error :messages="$errors->get('google_analytics_id')" />
            </div>
        </div>

        {{-- Social Links --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="mb-1 text-sm font-semibold text-luxury-white">{{ __('Social Links') }}</h3>
            <p class="mb-4 text-xs text-luxury-muted">{{ __('Used in structured data and footer social links.') }}</p>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="facebook_url" value="{{ __('Facebook') }}" />
                    <x-admin.text-input id="facebook_url" name="facebook_url" type="url" value="{{ old('facebook_url', $settings->facebook_url) }}" placeholder="https://facebook.com/..." />
                    <x-admin.input-error :messages="$errors->get('facebook_url')" />
                </div>
                <div>
                    <x-admin.input-label for="instagram_url" value="{{ __('Instagram') }}" />
                    <x-admin.text-input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $settings->instagram_url) }}" placeholder="https://instagram.com/..." />
                    <x-admin.input-error :messages="$errors->get('instagram_url')" />
                </div>
                <div>
                    <x-admin.input-label for="twitter_url" value="{{ __('X / Twitter') }}" />
                    <x-admin.text-input id="twitter_url" name="twitter_url" type="url" value="{{ old('twitter_url', $settings->twitter_url) }}" placeholder="https://x.com/..." />
                    <x-admin.input-error :messages="$errors->get('twitter_url')" />
                </div>
                <div>
                    <x-admin.input-label for="linkedin_url" value="{{ __('LinkedIn') }}" />
                    <x-admin.text-input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $settings->linkedin_url) }}" placeholder="https://linkedin.com/..." />
                    <x-admin.input-error :messages="$errors->get('linkedin_url')" />
                </div>
                <div>
                    <x-admin.input-label for="youtube_url" value="{{ __('YouTube') }}" />
                    <x-admin.text-input id="youtube_url" name="youtube_url" type="url" value="{{ old('youtube_url', $settings->youtube_url) }}" placeholder="https://youtube.com/..." />
                    <x-admin.input-error :messages="$errors->get('youtube_url')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Save Settings') }}</x-admin.button>
        </div>
    </form>
</x-admin.layouts.app>
