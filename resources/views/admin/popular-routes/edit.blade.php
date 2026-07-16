<x-admin.layouts.app :title="'Edit Popular Route'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Popular Route</h2>
        <p class="mt-1 text-sm text-luxury-muted">Update the details for {{ $route->pickup }} &rarr; {{ $route->dropoff }}.</p>
    </div>

    <form method="POST" action="{{ route('admin.popular-routes.update', $route) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.popular-routes._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Changes</x-admin.button>
            <a href="{{ route('admin.popular-routes.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
