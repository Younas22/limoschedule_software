<x-admin.layouts.app :title="'Edit Customer'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Customer</h2>
        <p class="mt-1 text-sm text-luxury-muted">Update the details for {{ $customer->name }}.</p>
    </div>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.customers._form', ['customer' => $customer])

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Save Changes</x-admin.button>
            <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
