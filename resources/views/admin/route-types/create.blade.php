<x-admin.layouts.app :title="'Add Route Type'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Route Type</h2>
        <p class="mt-1 text-sm text-luxury-muted">Create a new route type for Popular Routes.</p>
    </div>

    <form method="POST" action="{{ route('admin.popular-routes.route-types.store') }}" class="space-y-6">
        @csrf
        @include('admin.route-types._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Add Route Type</x-admin.button>
            <a href="{{ route('admin.popular-routes.route-types.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
