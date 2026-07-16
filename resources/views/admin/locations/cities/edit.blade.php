<x-admin.layouts.app :title="'Edit City'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit City</h2>
        <p class="mt-1 text-sm text-luxury-muted">Update the details for {{ $model->name }}.</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.cities.update', $model) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.locations.cities._form', ['countries' => $countries, 'states' => $states])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Changes</x-admin.button>
            <a href="{{ route('admin.locations.cities.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
