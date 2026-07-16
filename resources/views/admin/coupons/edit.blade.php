<x-admin.layouts.app :title="'Edit Coupon'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Coupon</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ $coupon->code }} &middot; Used {{ $coupon->used_count }} time(s).</p>
    </div>

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.coupons._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Update Coupon</x-admin.button>
            <a href="{{ route('admin.coupons.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
