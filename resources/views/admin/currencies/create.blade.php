<x-admin.layouts.app :title="'Add Currency'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Currency</h2>
        <p class="mt-1 text-sm text-luxury-muted">Register a new currency for the admin panel.</p>
    </div>

    <form method="POST" action="{{ route('admin.currencies.store') }}" class="space-y-6">
        @csrf
        @include('admin.currencies._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Add Currency</x-admin.button>
            <a href="{{ route('admin.currencies.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
