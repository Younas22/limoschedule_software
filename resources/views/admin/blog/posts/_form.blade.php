@php
    $post = $post ?? null;
    $existingTags = $post?->tags->pluck('name')->implode(', ');
@endphp

<div class="space-y-6" x-data="{
    title: {{ \Illuminate\Support\Js::from(old('title', $post?->title ?? '')) }},
    slug: {{ \Illuminate\Support\Js::from(old('slug', $post?->slug ?? '')) }},
    slugTouched: false,
    status: {{ \Illuminate\Support\Js::from(old('status', $post?->status ?? 'draft')) }},
    slugify(value) {
        return value.toString().toLowerCase().trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)+/g, '');
    },
}">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Main content --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div>
                    <x-admin.input-label for="title" value="Title" />
                    <input id="title" name="title" type="text" x-model="title" @input="if (!slugTouched) slug = slugify(title)" required autofocus
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <x-admin.input-error :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-admin.input-label for="slug" value="Slug" />
                    <input id="slug" name="slug" type="text" x-model="slug" @input="slugTouched = true"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <p class="mt-1 text-xs text-luxury-muted">Auto-generated from the title. Edit to customize the URL.</p>
                    <x-admin.input-error :messages="$errors->get('slug')" />
                </div>

                <div>
                    <x-admin.input-label for="excerpt" value="Excerpt" />
                    <textarea id="excerpt" name="excerpt" rows="2" placeholder="Short summary shown in listings"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('excerpt', $post?->excerpt) }}</textarea>
                    <x-admin.input-error :messages="$errors->get('excerpt')" />
                </div>

                <div>
                    <x-admin.input-label value="Content" />
                    <x-admin.richtext-editor name="body" :value="old('body', $post?->body)" />
                    <x-admin.input-error :messages="$errors->get('body')" />
                </div>
            </div>

            {{-- SEO --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">SEO</h3>

                <div>
                    <x-admin.input-label for="meta_title" value="Meta Title" />
                    <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $post?->meta_title) }}" />
                    <x-admin.input-error :messages="$errors->get('meta_title')" />
                </div>

                <div>
                    <x-admin.input-label for="meta_description" value="Meta Description" />
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $post?->meta_description) }}</textarea>
                    <x-admin.input-error :messages="$errors->get('meta_description')" />
                </div>

                <div>
                    <x-admin.input-label for="meta_keywords" value="Meta Keywords" />
                    <x-admin.text-input id="meta_keywords" name="meta_keywords" type="text" placeholder="comma, separated, keywords" value="{{ old('meta_keywords', $post?->meta_keywords) }}" />
                    <x-admin.input-error :messages="$errors->get('meta_keywords')" />
                </div>

                <div>
                    <x-admin.input-label for="custom_schema" value="Schema Markup (optional)" />
                    <textarea id="custom_schema" name="custom_schema" rows="6" spellcheck="false"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-xs text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"
                        placeholder="&lt;script type=&quot;application/ld+json&quot;&gt;{ ... }&lt;/script&gt;">{{ old('custom_schema', $post?->custom_schema) }}</textarea>
                    <p class="mt-1 text-xs text-luxury-muted">Paste the full &lt;script&gt; tag with your JSON-LD/structured data — it's added to this post's &lt;head&gt; exactly as pasted, alongside the automatic article schema.</p>
                    <x-admin.input-error :messages="$errors->get('custom_schema')" />
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Publishing --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <h3 class="text-sm font-semibold text-luxury-white">Publishing</h3>

                <div>
                    <x-admin.input-label for="status" value="Status" />
                    <select id="status" name="status" x-model="status" required
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        @foreach (\App\Models\BlogPost::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-admin.input-error :messages="$errors->get('status')" />
                </div>

                <div x-show="status === 'published'" x-cloak>
                    <x-admin.input-label for="published_at" value="Publish Date" />
                    <input id="published_at" name="published_at" type="datetime-local"
                        value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <p class="mt-1 text-xs text-luxury-muted">Leave blank to publish immediately.</p>
                </div>

                <x-admin.toggle
                    name="is_featured"
                    :checked="old('is_featured', $post?->is_featured ?? false)"
                    label="Featured"
                    description="Highlight this post in the featured section." />
            </div>

            {{-- Category & Tags --}}
            <div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
                <div>
                    <x-admin.input-label for="blog_category_id" value="Category" />
                    <select id="blog_category_id" name="blog_category_id" required
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('blog_category_id', $post?->blog_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-admin.input-error :messages="$errors->get('blog_category_id')" />
                </div>

                <div>
                    <x-admin.input-label for="tags" value="Tags" />
                    <input id="tags" name="tags" type="text" list="existing-tags" placeholder="e.g. travel, airport, luxury"
                        value="{{ old('tags', $existingTags) }}"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
                    <datalist id="existing-tags">
                        @foreach ($tags as $tagName)
                            <option value="{{ $tagName }}"></option>
                        @endforeach
                    </datalist>
                    <p class="mt-1 text-xs text-luxury-muted">Comma-separated. New tags are created automatically.</p>
                </div>
            </div>

            {{-- Featured Image --}}
            <div class="space-y-4 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6" x-data="{ preview: '{{ $post?->featured_image_url }}' }">
                <x-admin.input-label value="Featured Image" />
                <div class="flex h-36 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                    <template x-if="preview">
                        <img :src="preview" alt="Preview" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-luxury-muted">No image</span>
                    </template>
                </div>
                <label class="block cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    <span>Click to upload image</span>
                    <input type="file" name="featured_image" accept="image/*" class="hidden"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                </label>
                @if ($post?->featured_image_url)
                    <label class="flex items-center gap-1.5 text-xs text-luxury-muted">
                        <input type="checkbox" name="remove_featured_image" value="1" class="h-3.5 w-3.5 rounded border-luxury-border bg-luxury-charcoal text-red-400 focus:ring-1 focus:ring-red-400">
                        Remove current image
                    </label>
                @endif
                <x-admin.input-error :messages="$errors->get('featured_image')" />
            </div>
        </div>
    </div>
</div>
