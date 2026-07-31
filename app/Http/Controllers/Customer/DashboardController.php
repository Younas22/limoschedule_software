<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $stats = [
            'total' => $customer->bookings()->count(),
            'completed' => $customer->bookings()->where('status', 'completed')->count(),
            'upcoming' => $customer->bookings()
                ->whereIn('status', ['pending', 'confirmed', 'assigned'])
                ->where('pickup_datetime', '>=', now())
                ->count(),
            'totalSpent' => $customer->bookings()->where('status', 'completed')->sum('fare_amount'),
        ];

        $nextRide = $customer->bookings()
            ->with(['vehicle.category', 'driver'])
            ->whereIn('status', ['pending', 'confirmed', 'assigned'])
            ->where('pickup_datetime', '>=', now())
            ->orderBy('pickup_datetime')
            ->first();

        $recentBookings = $customer->bookings()
            ->with(['vehicle.category', 'driver'])
            ->latest()
            ->limit(5)
            ->get();

        $favoriteVehicles = $customer->favoriteVehicles()
            ->with('category')
            ->orderByPivot('created_at', 'desc')
            ->limit(4)
            ->get();

        // Promotions/coupons temporarily hidden from the dashboard — shown
        // in their place instead, matching the sidebar's Wallet/Reviews hide.
        $recommendedVehicles = Vehicle::active()->with('category')->inRandomOrder()->limit(6)->get();

        return view('customer.dashboard', compact(
            'stats',
            'nextRide',
            'recentBookings',
            'favoriteVehicles',
            'recommendedVehicles'
        ));
    }
}
