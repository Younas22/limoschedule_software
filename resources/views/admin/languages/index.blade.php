<x-admin.layouts.app :title="__('Languages')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Languages') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">Manage the languages available across the admin panel.</p>
        </div>

        @permission('languages.create')
            <a href="{{ route('admin.languages.create') }}">
                <x-admin.button type="button" variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Language
                </x-admin.button>
            </a>
        @endpermission
    </div>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">Language</th>
                        <th class="px-6 py-3 font-medium">Code</th>
                        <th class="px-6 py-3 font-medium">Direction</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Strings</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($languages as $language)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite text-xs font-semibold uppercase text-luxury-muted">
                                        @if ($language->flag_url)
                                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $language->code }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-luxury-white">{{ $language->name }}</span>
                                            @if ($language->is_default)
                                                <span class="rounded-full bg-luxury-gold/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-gold">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-luxury-muted">{{ $language->native_name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ strtoupper($language->code) }}</td>
                            <td class="px-6 py-3 text-luxury-muted uppercase">{{ $language->direction }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $language->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
                                    {{ $language->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $language->translations_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @permission('languages.edit')
                                        <a href="{{ route('admin.languages.translations.edit', $language) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            Translate
                                        </a>

                                        <a href="{{ route('admin.languages.edit', $language) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                            Edit
                                        </a>

                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.default', $language) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-secondary hover:text-luxury-secondary">
                                                    Set Default
                                                </button>
                                            </form>
                                        @endunless

                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.toggle', $language) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                                    {{ $language->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission

                                    @permission('languages.delete')
                                        @unless ($language->is_default)
                                            <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" onsubmit="return confirm('Delete this language and all of its translations?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                    Delete
                                                </button>
                                            </form>
                                        @endunless
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-luxury-muted">No languages configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layouts.app>
