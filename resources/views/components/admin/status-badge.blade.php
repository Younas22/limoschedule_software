@props(['active'])

<span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-luxury-slate text-luxury-muted' }}">
    {{ $active ? 'Active' : 'Disabled' }}
</span>
