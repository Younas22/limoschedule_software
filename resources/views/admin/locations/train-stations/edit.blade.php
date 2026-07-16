<x-admin.layouts.app :title="'Edit Train Station'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Train Station</h2>
        <p class="mt-1 text-sm text-luxury-muted">Update the details for {{ $model->name }}.</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.train-stations.update', $model) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.locations.train-stations._form', ['cities' => $cities])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Changes</x-admin.button>
            <a href="{{ route('admin.locations.train-stations.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
