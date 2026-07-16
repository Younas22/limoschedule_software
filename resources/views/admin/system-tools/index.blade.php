<x-admin.layouts.app :title="'System Tools'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">System Tools</h2>
            <p class="mt-1 text-sm text-luxury-muted">Maintenance, caching, backups, and diagnostics for this installation.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @permission('system.view')
                <a href="{{ route('admin.system-tools.activity-logs') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    Activity Logs
                </a>
                <a href="{{ route('admin.system-tools.error-logs') }}" class="rounded-lg border border-luxury-border px-4 py-2.5 text-sm font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    Error Logs
                </a>
            @endpermission
        </div>
    </div>

    @if ($isDownForMaintenance)
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4">
            <p class="text-sm font-semibold text-red-400">Maintenance mode is currently ON — the public site is unavailable to visitors.</p>
            @if ($maintenanceSecret)
                <p class="mt-1 text-xs text-red-300">Bypass link: <code class="rounded bg-black/30 px-1.5 py-0.5">{{ url('/') }}/{{ $maintenanceSecret }}</code></p>
            @endif
        </div>
    @endif

    {{-- System Health --}}
    <div class="mb-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <h3 class="mb-4 text-sm font-semibold text-luxury-white">System Health</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($checks as $check)
                <div class="flex items-start gap-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ match ($check['status']) {
                        'ok' => 'bg-emerald-500/10 text-emerald-400',
                        'warning' => 'bg-luxury-gold/10 text-luxury-gold',
                        default => 'bg-red-500/10 text-red-400',
                    } }}">
                        @if ($check['status'] === 'ok')
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        @elseif ($check['status'] === 'warning')
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-luxury-white">{{ $check['label'] }}</p>
                        <p class="truncate text-xs text-luxury-muted">{{ $check['detail'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-luxury-border pt-5 sm:grid-cols-5">
            @foreach ($systemInfo as $label => $value)
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $label }}</p>
                    <p class="mt-1 text-sm font-medium text-luxury-white">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Maintenance Mode --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">Maintenance Mode</h3>
            <p class="mt-1 text-xs text-luxury-muted">Take the public site offline while you make changes. The admin panel stays accessible.</p>

            <form method="POST" action="{{ route('admin.system-tools.maintenance.toggle') }}" class="mt-4">
                @csrf
                @permission('system.edit')
                    <x-admin.button type="submit" :variant="$isDownForMaintenance ? 'primary' : 'danger'">
                        {{ $isDownForMaintenance ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode' }}
                    </x-admin.button>
                @endpermission
            </form>
        </div>

        {{-- Cache --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">Cache</h3>
            <p class="mt-1 text-xs text-luxury-muted">Clear cached config, routes, views, or application data.</p>

            @permission('system.edit')
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['all' => 'Clear All', 'config' => 'Config', 'route' => 'Routes', 'view' => 'Views', 'application' => 'App Cache'] as $target => $label)
                        <form method="POST" action="{{ route('admin.system-tools.cache.clear') }}">
                            @csrf
                            <input type="hidden" name="target" value="{{ $target }}">
                            <button type="submit" class="rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>
            @endpermission
        </div>

        {{-- Queue Status --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <h3 class="text-sm font-semibold text-luxury-white">Queue Status</h3>
            <p class="mt-1 text-xs text-luxury-muted">Driver: <span class="font-medium text-luxury-white">{{ ucfirst($queue['driver']) }}</span></p>

            @if ($queue['pending'] === null)
                <p class="mt-4 text-sm text-luxury-muted">This driver processes jobs immediately — there is no queue to monitor.</p>
            @else
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 text-center">
                        <p class="text-2xl font-semibold text-luxury-white">{{ number_format($queue['pending']) }}</p>
                        <p class="text-xs text-luxury-muted">Pending Jobs</p>
                    </div>
                    <div class="rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 text-center">
                        <p class="text-2xl font-semibold {{ $queue['failed'] > 0 ? 'text-red-400' : 'text-luxury-white' }}">{{ number_format($queue['failed']) }}</p>
                        <p class="text-xs text-luxury-muted">Failed Jobs</p>
                    </div>
                </div>

                @if ($queue['failed'] > 0)
                    @permission('system.edit')
                        <form method="POST" action="{{ route('admin.system-tools.queue.failed.clear') }}" class="mt-3" onsubmit="return confirm('Clear all failed jobs?');">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                Clear Failed Jobs
                            </button>
                        </form>
                    @endpermission
                @endif
            @endif
        </div>

        {{-- Backups --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">Database Backups</h3>
                @permission('system.edit')
                    <form method="POST" action="{{ route('admin.system-tools.backups.create') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-luxury-gold px-3 py-1.5 text-xs font-semibold text-luxury-black transition hover:bg-luxury-gold-light">
                            + New Backup
                        </button>
                    </form>
                @endpermission
            </div>

            <div class="mt-4 max-h-64 space-y-2 overflow-y-auto">
                @forelse ($backups as $backup)
                    <div class="flex items-center justify-between rounded-lg border border-luxury-border/60 bg-luxury-graphite/40 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-medium text-luxury-white">{{ $backup['name'] }}</p>
                            <p class="text-[11px] text-luxury-muted">{{ $backup['size'] }} &middot; {{ $backup['created_at']->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            @permission('system.export')
                                <a href="{{ route('admin.system-tools.backups.download', $backup['name']) }}" class="rounded-lg border border-luxury-border px-2.5 py-1.5 text-[11px] font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    Download
                                </a>
                            @endpermission
                            @permission('system.delete')
                                <form method="POST" action="{{ route('admin.system-tools.backups.destroy', $backup['name']) }}" onsubmit="return confirm('Delete this backup?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-500/30 px-2.5 py-1.5 text-[11px] font-medium text-red-400 transition hover:bg-red-500/10">
                                        Delete
                                    </button>
                                </form>
                            @endpermission
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">No backups yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin.layouts.app>
