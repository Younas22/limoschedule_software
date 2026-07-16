<x-admin.layouts.app :title="'Add Review'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Review</h2>
        <p class="mt-1 text-sm text-luxury-muted">Manually add a customer review to feature as a testimonial.</p>
    </div>

    <form method="POST" action="{{ route('admin.reviews.store') }}" class="space-y-6">
        @csrf
        @include('admin.reviews._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Add Review</x-admin.button>
            <a href="{{ route('admin.reviews.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
