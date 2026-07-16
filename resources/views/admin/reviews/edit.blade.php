<x-admin.layouts.app :title="'Edit Review'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Review</h2>
        <p class="mt-1 text-sm text-luxury-muted">Update the review from {{ $review->customer?->name ?? 'this customer' }}.</p>
    </div>

    <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.reviews._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Changes</x-admin.button>
            <a href="{{ route('admin.reviews.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
