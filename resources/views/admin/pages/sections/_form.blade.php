@php
    $section = $section ?? null;
    $contact = $section?->contact_fields ?? [];
    $initialItems = old('items', $section?->content ?? []);

    if (($section?->type ?? old('type', 'hero')) === 'team' && ! empty($initialItems) && is_array($initialItems)) {
        $initialItems = collect($initialItems)->map(function ($item) {
            $existingPhoto = $item['existing_photo'] ?? ($item['photo'] ?? '');

            return [
                'existing_photo' => $existingPhoto,
                'photo_preview' => $existingPhoto ? asset('public/uploads/team/'.$existingPhoto) : '',
                'name' => $item['name'] ?? '',
                'role' => $item['role'] ?? '',
                'bio' => $item['bio'] ?? '',
            ];
        })->values()->all();
    }
@endphp

<div class="space-y-6"
    x-data="pageSectionForm({
        type: {{ \Illuminate\Support\Js::from(old('type', $section?->type ?? 'hero')) }},
        items: {{ \Illuminate\Support\Js::from(!empty($initialItems) && is_array($initialItems) && array_is_list($initialItems) ? $initialItems : []) }},
    })">

    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div>
            <x-admin.input-label for="type" value="Section Type" />
            <select id="type" name="type" x-model="type" required
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition sm:max-w-sm">
                @foreach (\App\Models\PageSection::TYPES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <x-admin.input-error :messages="$errors->get('type')" />
        </div>
    </div>

    {{-- Heading / Subheading (all types except faq subheading) --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="text-sm font-semibold text-luxury-white">Content</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="heading" value="Heading" />
                <x-admin.text-input id="heading" name="heading" type="text" value="{{ old('heading', $section?->heading) }}" />
                <x-admin.input-error :messages="$errors->get('heading')" />
            </div>

            <div x-show="type !== 'rich_text' && type !== 'faq'" x-cloak>
                <x-admin.input-label for="subheading" value="Subheading" />
                <x-admin.text-input id="subheading" name="subheading" type="text" value="{{ old('subheading', $section?->subheading) }}" />
                <x-admin.input-error :messages="$errors->get('subheading')" />
            </div>
        </div>

        {{-- Rich text body --}}
        <div x-show="type === 'rich_text'" x-cloak>
            <x-admin.input-label value="Body" />
            <x-admin.richtext-editor name="body" :value="old('body', $section?->body)" />
            <x-admin.input-error :messages="$errors->get('body')" />
        </div>

        {{-- Image (hero / cta) --}}
        <div x-show="type === 'hero' || type === 'cta'" x-cloak>
            <x-admin.input-label for="image" value="Image" />
            @if ($section?->image_url)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ $section->image_url }}" alt="" class="h-16 w-28 rounded-lg border border-luxury-border object-cover">
                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_image" value="1" class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                        Remove current image
                    </label>
                </div>
            @endif
            <input id="image" name="image" type="file" accept="image/*"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-2.5 text-sm text-luxury-white file:mr-3 file:rounded-md file:border-0 file:bg-luxury-slate file:px-3 file:py-1.5 file:text-xs file:text-luxury-white">
            <x-admin.input-error :messages="$errors->get('image')" />
            <p class="mt-1 text-xs text-luxury-muted" x-show="type === 'hero'">Used as the background poster, and as the fallback if no video is uploaded below.</p>
        </div>

        {{-- Video (hero only, full-bleed background) --}}
        <div x-show="type === 'hero'" x-cloak>
            <x-admin.input-label for="video" value="Background Video (optional)" />
            @if ($section?->video_url)
                <div class="mb-2 flex items-center gap-3">
                    <video src="{{ $section->video_url }}" class="h-16 w-28 rounded-lg border border-luxury-border object-cover" muted></video>
                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_video" value="1" class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                        Remove current video
                    </label>
                </div>
            @endif
            <input id="video" name="video" type="file" accept="video/mp4,video/webm,video/quicktime"
                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-2.5 text-sm text-luxury-white file:mr-3 file:rounded-md file:border-0 file:bg-luxury-slate file:px-3 file:py-1.5 file:text-xs file:text-luxury-white">
            <x-admin.input-error :messages="$errors->get('video')" />
            <p class="mt-1 text-xs text-luxury-muted">MP4 or WebM, up to 25MB. Takes priority over the image above when present.</p>
        </div>

        {{-- Primary button (hero / cta) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-show="type === 'hero' || type === 'cta'" x-cloak>
            <div>
                <x-admin.input-label for="button_text" value="Primary Button Text" />
                <x-admin.text-input id="button_text" name="button_text" type="text" value="{{ old('button_text', $section?->button_text) }}" />
                <x-admin.input-error :messages="$errors->get('button_text')" />
            </div>
            <div>
                <x-admin.input-label for="button_url" value="Primary Button URL" />
                <x-admin.text-input id="button_url" name="button_url" type="text" placeholder="/contact" value="{{ old('button_url', $section?->button_url) }}" />
                <x-admin.input-error :messages="$errors->get('button_url')" />
            </div>
        </div>

        {{-- Secondary button (hero / cta) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-show="type === 'hero' || type === 'cta'" x-cloak>
            <div>
                <x-admin.input-label for="button_text_2" value="Secondary Button Text (optional)" />
                <x-admin.text-input id="button_text_2" name="button_text_2" type="text" value="{{ old('button_text_2', $section?->button_text_2) }}" />
                <x-admin.input-error :messages="$errors->get('button_text_2')" />
            </div>
            <div>
                <x-admin.input-label for="button_url_2" value="Secondary Button URL" />
                <x-admin.text-input id="button_url_2" name="button_url_2" type="text" placeholder="/about" value="{{ old('button_url_2', $section?->button_url_2) }}" />
                <x-admin.input-error :messages="$errors->get('button_url_2')" />
            </div>
        </div>
    </div>

    {{-- Contact info fields --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'contact_info'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">Contact Details</h3>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="address" value="Address" />
                <x-admin.text-input id="address" name="address" type="text" value="{{ old('address', $contact['address'] ?? '') }}" />
            </div>
            <div>
                <x-admin.input-label for="phone" value="Phone" />
                <x-admin.text-input id="phone" name="phone" type="text" value="{{ old('phone', $contact['phone'] ?? '') }}" />
            </div>
            <div>
                <x-admin.input-label for="email" value="Email" />
                <x-admin.text-input id="email" name="email" type="email" value="{{ old('email', $contact['email'] ?? '') }}" />
            </div>
            <div>
                <x-admin.input-label for="hours" value="Hours" />
                <x-admin.text-input id="hours" name="hours" type="text" value="{{ old('hours', $contact['hours'] ?? '') }}" placeholder="e.g. Available 24/7" />
            </div>
            <div>
                <x-admin.input-label for="whatsapp" value="WhatsApp Number" />
                <x-admin.text-input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $contact['whatsapp'] ?? '') }}" placeholder="e.g. +1 555 123 4567" />
            </div>
            <div class="sm:col-span-2">
                <x-admin.input-label for="google_maps_embed_url" value="Google Maps Embed URL (optional)" />
                <x-admin.text-input id="google_maps_embed_url" name="google_maps_embed_url" type="url" value="{{ old('google_maps_embed_url', $contact['google_maps_embed_url'] ?? '') }}" placeholder="https://www.google.com/maps/embed?..." />
                <p class="mt-1 text-xs text-luxury-muted">From Google Maps: Share &rarr; Embed a map &rarr; copy the "src" URL. Leave blank to show a placeholder.</p>
            </div>
        </div>
    </div>

    {{-- Vision & Mission fields --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'vision_mission'" x-cloak>
        <h3 class="text-sm font-semibold text-luxury-white">Vision &amp; Mission</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-4 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-luxury-gold">Vision</p>
                <div>
                    <x-admin.input-label for="vision_icon" value="Icon" />
                    <select id="vision_icon" name="vision_icon"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        @foreach (array_keys(config('icons')) as $iconName)
                            <option value="{{ $iconName }}" @selected(old('vision_icon', $section?->vision_mission['vision_icon'] ?? 'eye') === $iconName)>{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-admin.input-label for="vision_title" value="Title" />
                    <x-admin.text-input id="vision_title" name="vision_title" type="text" value="{{ old('vision_title', $section?->vision_mission['vision_title'] ?? 'Our Vision') }}" />
                </div>
                <div>
                    <x-admin.input-label for="vision_body" value="Body" />
                    <textarea id="vision_body" name="vision_body" rows="4"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('vision_body', $section?->vision_mission['vision_body'] ?? '') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('vision_body')" />
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-luxury-gold">Mission</p>
                <div>
                    <x-admin.input-label for="mission_icon" value="Icon" />
                    <select id="mission_icon" name="mission_icon"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        @foreach (array_keys(config('icons')) as $iconName)
                            <option value="{{ $iconName }}" @selected(old('mission_icon', $section?->vision_mission['mission_icon'] ?? 'trending-up') === $iconName)>{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-admin.input-label for="mission_title" value="Title" />
                    <x-admin.text-input id="mission_title" name="mission_title" type="text" value="{{ old('mission_title', $section?->vision_mission['mission_title'] ?? 'Our Mission') }}" />
                </div>
                <div>
                    <x-admin.input-label for="mission_body" value="Body" />
                    <textarea id="mission_body" name="mission_body" rows="4"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('mission_body', $section?->vision_mission['mission_body'] ?? '') }}</textarea>
                    <x-admin.input-error :messages="$errors->get('mission_body')" />
                </div>
            </div>
        </div>
    </div>

    {{-- Testimonial settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'testimonials'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">Testimonial Settings</h3>
            <p class="mt-1 text-xs text-luxury-muted">Pulls live from approved customer reviews — no manual content to enter here.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="testimonial_limit" value="Number to Show" />
                <x-admin.text-input id="testimonial_limit" name="testimonial_limit" type="number" min="1" max="24"
                    value="{{ old('testimonial_limit', $section?->testimonial_settings['limit'] ?? 6) }}" />
                <x-admin.input-error :messages="$errors->get('testimonial_limit')" />
            </div>
            <div>
                <x-admin.input-label for="testimonial_min_rating" value="Minimum Rating" />
                <select id="testimonial_min_rating" name="testimonial_min_rating"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    @php $minRating = old('testimonial_min_rating', $section?->testimonial_settings['min_rating'] ?? 4); @endphp
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((string) $minRating === (string) $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }} &amp; Up</option>
                    @endfor
                </select>
                <x-admin.input-error :messages="$errors->get('testimonial_min_rating')" />
            </div>
        </div>
    </div>

    {{-- Fleet settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'fleet'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">Fleet Settings</h3>
            <p class="mt-1 text-xs text-luxury-muted">Pulls live from your active vehicles and categories — no manual content to enter here.</p>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-admin.input-label for="fleet_limit" value="Maximum Vehicles to Show" />
                <x-admin.text-input id="fleet_limit" name="fleet_limit" type="number" min="1" max="48"
                    value="{{ old('fleet_limit', $section?->fleet_settings['limit'] ?? 12) }}" />
                <x-admin.input-error :messages="$errors->get('fleet_limit')" />
            </div>
            <div>
                <x-admin.input-label for="fleet_category_id" value="Featured Category (optional)" />
                <select id="fleet_category_id" name="fleet_category_id"
                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <option value="">All Categories</option>
                    @foreach (\App\Models\VehicleCategory::active()->ordered()->get() as $category)
                        <option value="{{ $category->id }}" @selected((string) old('fleet_category_id', $section?->fleet_settings['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-luxury-muted">Limit this section to a single fleet category, e.g. "Luxury" for a VIP page.</p>
                <x-admin.input-error :messages="$errors->get('fleet_category_id')" />
            </div>
        </div>
    </div>

    {{-- Popular routes settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'routes'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">Popular Routes Settings</h3>
            <p class="mt-1 text-xs text-luxury-muted">Pulls live from your Popular Routes list (Admin &rarr; Popular Routes) — no manual content to enter here.</p>
        </div>
        <div>
            <x-admin.input-label for="route_limit" value="Maximum Routes per Group" />
            <x-admin.text-input id="route_limit" name="route_limit" type="number" min="1" max="24" class="sm:max-w-xs"
                value="{{ old('route_limit', $section?->route_settings['limit'] ?? 6) }}" />
            <x-admin.input-error :messages="$errors->get('route_limit')" />
        </div>
    </div>

    {{-- Blog settings --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'blog'" x-cloak>
        <div>
            <h3 class="text-sm font-semibold text-luxury-white">Blog Settings</h3>
            <p class="mt-1 text-xs text-luxury-muted">Pulls live from your published blog posts (Admin &rarr; Blog) — no manual content to enter here.</p>
        </div>
        <div>
            <x-admin.input-label for="blog_limit" value="Maximum Latest Posts to Show" />
            <x-admin.text-input id="blog_limit" name="blog_limit" type="number" min="1" max="24" class="sm:max-w-xs"
                value="{{ old('blog_limit', $section?->blog_settings['limit'] ?? 6) }}" />
            <x-admin.input-error :messages="$errors->get('blog_limit')" />
        </div>
    </div>

    {{-- Repeatable items (items / faq / stats / team / process) --}}
    <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-show="type === 'items' || type === 'faq' || type === 'stats' || type === 'team' || type === 'process'" x-cloak>
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-luxury-white" x-text="type === 'faq' ? 'Questions' : (type === 'stats' ? 'Stats' : (type === 'team' ? 'Team Members' : (type === 'process' ? 'Steps' : 'Items')))"></h3>
            <button type="button" @click="items.push({ icon: 'star', title: '', description: '', link: '', question: '', answer: '', category: '', label: '', value: '', suffix: '', name: '', role: '', bio: '', existing_photo: '', photo_preview: '' })" class="text-xs font-medium text-luxury-gold hover:text-luxury-gold-light">
                + Add <span x-text="type === 'faq' ? 'Question' : (type === 'stats' ? 'Stat' : (type === 'team' ? 'Team Member' : (type === 'process' ? 'Step' : 'Item')))"></span>
            </button>
        </div>

        <template x-for="(item, index) in items" :key="index">
            <div class="space-y-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wide text-luxury-muted" x-text="type === 'faq' ? 'Question ' + (index + 1) : (type === 'stats' ? 'Stat ' + (index + 1) : (type === 'team' ? 'Team Member ' + (index + 1) : (type === 'process' ? 'Step ' + (index + 1) : 'Item ' + (index + 1))))"></span>
                    <button type="button" @click="items.splice(index, 1)" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                </div>

                {{-- items / process fields (process has no link) --}}
                <template x-if="type === 'items' || type === 'process'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Icon</label>
                            <select :name="'items[' + index + '][icon]'" x-model="item.icon"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                @foreach (array_keys(config('icons')) as $iconName)
                                    <option value="{{ $iconName }}">{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Title</label>
                            <input type="text" :name="'items[' + index + '][title]'" x-model="item.title"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs text-luxury-muted">Description</label>
                            <textarea :name="'items[' + index + '][description]'" x-model="item.description" rows="2"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                        </div>
                        <div class="sm:col-span-2" x-show="type === 'items'">
                            <label class="mb-1 block text-xs text-luxury-muted">Link (optional)</label>
                            <input type="text" :name="'items[' + index + '][link]'" x-model="item.link" placeholder="/services"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                    </div>
                </template>

                {{-- faq fields --}}
                <template x-if="type === 'faq'">
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">Question</label>
                                <input type="text" :name="'items[' + index + '][question]'" x-model="item.question"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">Category (optional)</label>
                                <input type="text" :name="'items[' + index + '][category]'" x-model="item.category" placeholder="e.g. Booking, Payments"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Answer</label>
                            <textarea :name="'items[' + index + '][answer]'" x-model="item.answer" rows="2"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                        </div>
                    </div>
                </template>

                {{-- stats fields --}}
                <template x-if="type === 'stats'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Icon</label>
                            <select :name="'items[' + index + '][icon]'" x-model="item.icon"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                @foreach (array_keys(config('icons')) as $iconName)
                                    <option value="{{ $iconName }}">{{ ucfirst(str_replace('-', ' ', $iconName)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Label</label>
                            <input type="text" :name="'items[' + index + '][label]'" x-model="item.label" placeholder="e.g. Total Rides"
                                class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-luxury-muted">Value</label>
                            <div class="flex gap-2">
                                <input type="number" min="0" :name="'items[' + index + '][value]'" x-model="item.value" placeholder="e.g. 50000"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                                <input type="text" :name="'items[' + index + '][suffix]'" x-model="item.suffix" placeholder="+" maxlength="10"
                                    class="w-16 shrink-0 rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                        </div>
                    </div>
                </template>

                {{-- team fields --}}
                <template x-if="type === 'team'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[5rem_1fr]">
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-luxury-border bg-luxury-graphite">
                                <template x-if="item.photo_preview">
                                    <img :src="item.photo_preview" alt="" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!item.photo_preview">
                                    <span class="text-[10px] text-luxury-muted">No photo</span>
                                </template>
                            </div>
                            <label class="cursor-pointer text-[11px] text-luxury-gold hover:text-luxury-gold-light">
                                <span>Upload</span>
                                <input type="file" :name="'items[' + index + '][photo]'" accept="image/*" class="hidden"
                                    @change="item.photo_preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : item.photo_preview">
                            </label>
                            <input type="hidden" :name="'items[' + index + '][existing_photo]'" x-model="item.existing_photo">
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">Name</label>
                                <input type="text" :name="'items[' + index + '][name]'" x-model="item.name"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-luxury-muted">Role</label>
                                <input type="text" :name="'items[' + index + '][role]'" x-model="item.role" placeholder="e.g. Chief Executive Officer"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs text-luxury-muted">Bio (optional)</label>
                                <textarea :name="'items[' + index + '][bio]'" x-model="item.bio" rows="2"
                                    class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
        <x-admin.input-error :messages="$errors->get('items')" />
    </div>
</div>

<script>
    function pageSectionForm(config) {
        return {
            type: config.type,
            items: config.items.length ? config.items.map((item) => ({
                icon: item.icon || 'star',
                title: item.title || '',
                description: item.description || '',
                link: item.link || '',
                question: item.question || '',
                answer: item.answer || '',
                category: item.category || '',
                label: item.label || '',
                value: item.value ?? '',
                suffix: item.suffix || '',
                name: item.name || '',
                role: item.role || '',
                bio: item.bio || '',
                existing_photo: item.existing_photo || '',
                photo_preview: item.photo_preview || '',
            })) : [],
        };
    }
</script>
