@props(['name', 'value' => ''])

<div x-data="richTextEditor({{ \Illuminate\Support\Js::from($value ?? '') }})" class="overflow-hidden rounded-lg border border-luxury-border bg-luxury-charcoal">
    <div class="flex flex-wrap items-center gap-1 border-b border-luxury-border bg-luxury-graphite/40 px-2 py-1.5">
        <button type="button" @mousedown.prevent="exec('bold')" class="rounded px-2 py-1 text-xs font-bold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">B</button>
        <button type="button" @mousedown.prevent="exec('italic')" class="rounded px-2 py-1 text-xs italic text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">I</button>
        <button type="button" @mousedown.prevent="exec('underline')" class="rounded px-2 py-1 text-xs underline text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">U</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'h2')" class="rounded px-2 py-1 text-xs font-semibold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">H2</button>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'h3')" class="rounded px-2 py-1 text-xs font-semibold text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">H3</button>
        <button type="button" @mousedown.prevent="exec('formatBlock', 'p')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">P</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="exec('insertUnorderedList')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">&bull; List</button>
        <button type="button" @mousedown.prevent="exec('insertOrderedList')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">1. List</button>
        <span class="mx-1 h-4 w-px bg-luxury-border"></span>
        <button type="button" @mousedown.prevent="addLink()" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">Link</button>
        <button type="button" @mousedown.prevent="exec('removeFormat')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">Clear</button>
    </div>

    <div x-ref="editor" contenteditable="true" @input="sync()" x-init="$refs.editor.innerHTML = html"
        class="richtext-content min-h-[200px] px-4 py-3 text-sm text-luxury-white focus:outline-none"></div>

    <input type="hidden" name="{{ $name }}" x-model="html">
</div>

<style>
    .richtext-content h2 { font-size: 1.25rem; font-weight: 600; margin: 0.75rem 0 0.5rem; color: inherit; }
    .richtext-content h3 { font-size: 1.1rem; font-weight: 600; margin: 0.75rem 0 0.5rem; color: inherit; }
    .richtext-content p { margin: 0.5rem 0; line-height: 1.6; }
    .richtext-content ul { list-style: disc; padding-inline-start: 1.5rem; margin: 0.5rem 0; }
    .richtext-content ol { list-style: decimal; padding-inline-start: 1.5rem; margin: 0.5rem 0; }
    .richtext-content a { color: #c9a227; text-decoration: underline; }
</style>

<script>
    function richTextEditor(initialHtml) {
        return {
            html: initialHtml,
            sync() {
                this.html = this.$refs.editor.innerHTML;
            },
            exec(command, value = null) {
                document.execCommand(command, false, value);
                this.sync();
                this.$refs.editor.focus();
            },
            addLink() {
                const url = window.prompt('Enter a URL');
                if (url) {
                    this.exec('createLink', url);
                }
            },
        };
    }
</script>
