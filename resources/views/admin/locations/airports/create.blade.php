<x-admin.layouts.app :title="'Add Airport'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Airport</h2>
        <p class="mt-1 text-sm text-luxury-muted">Register a new airport pickup point.</p>
    </div>

    <form method="POST" action="{{ route('admin.locations.airports.store') }}" class="space-y-6">
        @csrf
        @include('admin.locations.airports._form', ['cities' => $cities])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Add Airport</x-admin.button>
            <a href="{{ route('admin.locations.airports.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
