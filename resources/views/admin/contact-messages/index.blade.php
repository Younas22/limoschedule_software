<x-admin.layouts.app :title="'Contact Messages'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Contact Messages</h2>
        <p class="mt-1 text-sm text-luxury-muted">Submissions from the public Contact page form.</p>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" onchange="this.form.submit()"
            class="rounded-lg border border-luxury-border bg-luxury-charcoal px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
            <option value="">All Messages</option>
            <option value="unread" @selected(request('status') === 'unread')>Unread</option>
            <option value="read" @selected(request('status') === 'read')>Read</option>
        </select>

        @if (request()->filled('status'))
            <a href="{{ route('admin.contact-messages.index') }}" class="text-xs text-luxury-muted hover:text-luxury-gold">Clear filter</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-luxury-border bg-luxury-charcoal">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-luxury-border text-xs uppercase tracking-wider text-luxury-muted">
                        <th class="px-6 py-3 font-medium">From</th>
                        <th class="px-6 py-3 font-medium">Subject</th>
                        <th class="px-6 py-3 font-medium">Received</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-end font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-border/60">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-luxury-graphite">
                            <td class="px-6 py-3">
                                <p class="font-medium text-luxury-white">{{ $message->name }}</p>
                                <p class="text-xs text-luxury-muted">{{ $message->email }}</p>
                            </td>
                            <td class="max-w-xs px-6 py-3 text-luxury-muted">
                                <p class="truncate">{{ $message->subject ?: '—' }}</p>
                            </td>
                            <td class="px-6 py-3 text-luxury-muted">{{ $message->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-3">
                                @if ($message->is_read)
                                    <span class="rounded-full bg-luxury-graphite px-2.5 py-1 text-xs font-medium text-luxury-muted">Read</span>
                                @else
                                    <span class="rounded-full bg-luxury-gold/10 px-2.5 py-1 text-xs font-medium text-luxury-gold">Unread</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        View
                                    </a>
                                    @permission('messages.delete')
                                        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                                Delete
                                            </button>
                                        </form>
                                    @endpermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-luxury-muted">No messages received yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($messages->hasPages())
        <div class="mt-6 flex items-center justify-between text-sm text-luxury-muted">
            <div>
                @if ($messages->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Previous</span>
                @else
                    <a href="{{ $messages->previousPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Previous</a>
                @endif
            </div>
            <p>Page {{ $messages->currentPage() }} of {{ $messages->lastPage() }}</p>
            <div>
                @if ($messages->hasMorePages())
                    <a href="{{ $messages->nextPageUrl() }}" class="rounded-lg border border-luxury-border px-3 py-1.5 hover:border-luxury-gold/40 hover:text-luxury-gold">Next</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-luxury-border px-3 py-1.5 opacity-40">Next</span>
                @endif
            </div>
        </div>
    @endif
</x-admin.layouts.app>
