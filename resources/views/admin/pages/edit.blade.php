<x-admin.layouts.app :title="$page->name">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $page->name }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">/{{ $page->slug === 'home' ? '' : $page->slug }}</p>
        </div>
        <a href="{{ $page->slug === 'home' ? route('pages.home') : route('pages.show', $page->slug) }}" target="_blank" rel="noopener" class="text-sm text-luxury-gold hover:text-luxury-gold-light">
            View Live &rarr;
        </a>
    </div>

    <div class="space-y-6">
        {{-- Page Settings --}}
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            @csrf
            @method('PUT')

            <h3 class="text-sm font-semibold text-luxury-white">Page Settings</h3>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="name" value="Page Name" />
                    <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $page->name) }}" required />
                    <x-admin.input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-admin.input-label for="meta_title" value="Meta Title" />
                    <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $page->meta_title) }}" />
                    <x-admin.input-error :messages="$errors->get('meta_title')" />
                </div>

                <div class="sm:col-span-2">
                    <x-admin.input-label for="meta_description" value="Meta Description" />
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <x-admin.input-error :messages="$errors->get('meta_description')" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-luxury-muted">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active))
                    class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                Page is published
            </label>

            <div class="flex justify-end">
                <x-admin.button type="submit" variant="primary">Save Page Settings</x-admin.button>
            </div>
        </form>

        {{-- Sections --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">Sections</h3>
                @permission('content.create')
                    <a href="{{ route('admin.pages.sections.create', $page) }}">
                        <x-admin.button type="button" variant="primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Section
                        </x-admin.button>
                    </a>
                @endpermission
            </div>

            <div class="space-y-3">
                @forelse ($page->sections as $section)
                    <div class="flex flex-col gap-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            @permission('content.edit')
                                <div class="flex flex-col">
                                    <form method="POST" action="{{ route('admin.pages.sections.move-up', [$page, $section]) }}">
                                        @csrf
                                        <button type="submit" @if ($loop->first) disabled @endif class="flex h-5 w-5 items-center justify-center text-luxury-muted hover:text-luxury-gold disabled:opacity-20">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pages.sections.move-down', [$page, $section]) }}">
                                        @csrf
                                        <button type="submit" @if ($loop->last) disabled @endif class="flex h-5 w-5 items-center justify-center text-luxury-muted hover:text-luxury-gold disabled:opacity-20">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </form>
                                </div>
                            @endpermission

                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-luxury-white">{{ $section->heading ?: $section->type_label }}</p>
                                    <span class="rounded-full bg-luxury-slate px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-muted">{{ $section->type_label }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-luxury-muted">{{ $section->subheading ?: ($section->body ? \Illuminate\Support\Str::limit(strip_tags($section->body), 80) : '') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :active="$section->is_active" />
                            @permission('content.edit')
                                <form method="POST" action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ $section->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    Edit
                                </a>
                            @endpermission
                            @permission('content.delete')
                                <form method="POST" action="{{ route('admin.pages.sections.destroy', [$page, $section]) }}" onsubmit="return confirm('Remove this section?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                        Delete
                                    </button>
                                </form>
                            @endpermission
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">No sections yet — add one to start building this page.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin.layouts.app>
