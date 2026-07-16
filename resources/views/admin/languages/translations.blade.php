<x-admin.layouts.app :title="'Translate — '.$language->name">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">Translation Editor</h2>
            <p class="mt-1 text-sm text-luxury-muted">
                Editing <span class="text-luxury-white">{{ $language->name }}</span> ({{ $language->native_name }}) &mdash;
                changes apply across the panel immediately after saving.
            </p>
        </div>
        <a href="{{ route('admin.languages.index') }}" class="flex items-center gap-1.5 text-sm text-luxury-muted hover:text-luxury-white">
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Languages
        </a>
    </div>

    <form method="POST" action="{{ route('admin.languages.translations.update', $language) }}" x-data="translationEditor()" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-sm font-semibold text-luxury-white">Existing Strings</h3>
                <input type="search" x-model="search" placeholder="Search keys..."
                    class="w-full max-w-xs rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            </div>

            <div class="scrollbar-luxury max-h-[32rem] space-y-3 overflow-y-auto pe-1">
                @forelse ($keys as $key)
                    <div x-show="matches({{ \Illuminate\Support\Js::from($key) }})" class="grid grid-cols-1 gap-2 rounded-lg border border-luxury-border/60 p-3 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-luxury-muted">Source (English)</p>
                            <p class="mt-1 text-sm text-luxury-white">{{ $key }}</p>
                        </div>
                        <div>
                            <label class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $language->name }}</label>
                            <textarea name="translations[{{ $key }}]" rows="2"
                                class="mt-1 w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"
                                dir="{{ $language->direction }}">{{ old("translations.$key", $values[$key] ?? '') }}</textarea>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">No translation strings yet. Add one below.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">Add New String</h3>
                <button type="button" @click="addRow()" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    + Add Row
                </button>
            </div>

            <template x-if="rows.length === 0">
                <p class="text-sm text-luxury-muted">No new strings queued. Click "Add Row" to create one.</p>
            </template>

            <div class="space-y-3">
                <template x-for="(row, index) in rows" :key="row.id">
                    <div class="grid grid-cols-1 gap-2 rounded-lg border border-luxury-border/60 p-3 sm:grid-cols-[1fr_1fr_auto] sm:gap-3">
                        <input type="text" :name="'new_translations['+index+'][key]'" x-model="row.key" placeholder="Source key (English text)"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="text" :name="'new_translations['+index+'][value]'" x-model="row.value" placeholder="Translation"
                            dir="{{ $language->direction }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <button type="button" @click="removeRow(index)" class="rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                            Remove
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Translations</x-admin.button>
        </div>
    </form>

    <script>
        function translationEditor() {
            return {
                search: '',
                rows: [],
                nextId: 1,
                addRow() {
                    this.rows.push({ id: this.nextId++, key: '', value: '' });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
                matches(key) {
                    return this.search === '' || key.toLowerCase().includes(this.search.toLowerCase());
                },
            };
        }
    </script>
</x-admin.layouts.app>
