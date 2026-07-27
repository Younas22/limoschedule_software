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
        <button type="button" @mousedown.prevent="triggerImagePick()" :disabled="uploading" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white disabled:opacity-50">
            <span x-show="!uploading">Image</span>
            <span x-show="uploading" x-cloak>Uploading…</span>
        </button>
        <button type="button" @mousedown.prevent="exec('removeFormat')" class="rounded px-2 py-1 text-xs text-luxury-muted hover:bg-luxury-slate hover:text-luxury-white">Clear</button>
        <input type="file" x-ref="imageInput" accept="image/*" class="hidden" @change="uploadImage($event)">
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
    .richtext-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem 0; }
</style>

<script>
    function richTextEditor(initialHtml) {
        return {
            html: initialHtml,
            uploading: false,
            savedRange: null,
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
            triggerImagePick() {
                // The editor loses text-cursor focus once the native file
                // picker opens, so the insertion point has to be captured
                // now and restored right before inserting the image.
                const selection = window.getSelection();
                this.savedRange = selection.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
                this.$refs.imageInput.click();
            },
            uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploading = true;

                const formData = new FormData();
                formData.append('image', file);

                axios.post({{ \Illuminate\Support\Js::from(route('admin.rich-text.upload-image')) }}, formData)
                    .then((response) => {
                        const editor = this.$refs.editor;
                        editor.focus();

                        // Fall back to inserting at the end of the content
                        // when there's no valid saved cursor position inside
                        // the editor (e.g. the toolbar button was clicked
                        // without focusing the editor first).
                        let range = this.savedRange;
                        if (!range || !editor.contains(range.commonAncestorContainer)) {
                            range = document.createRange();
                            range.selectNodeContents(editor);
                            range.collapse(false);
                        }

                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);

                        document.execCommand('insertImage', false, response.data.url);
                        this.sync();
                    })
                    .catch(() => {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { type: 'error', message: 'Image upload failed — please try again.' },
                        }));
                    })
                    .finally(() => {
                        this.uploading = false;
                        event.target.value = '';
                    });
            },
        };
    }
</script>
