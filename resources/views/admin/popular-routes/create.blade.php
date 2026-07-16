<x-admin.layouts.app :title="'Add Popular Route'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Popular Route</h2>
        <p class="mt-1 text-sm text-luxury-muted">Create a new popular route shown on the public website.</p>
    </div>

    <form method="POST" action="{{ route('admin.popular-routes.store') }}" class="space-y-6">
        @csrf
        @include('admin.popular-routes._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Add Route</x-admin.button>
            <a href="{{ route('admin.popular-routes.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
