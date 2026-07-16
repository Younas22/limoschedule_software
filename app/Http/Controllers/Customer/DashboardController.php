<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Promotion;
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
            'wallet' => $customer->wallet_balance,
            'totalSpent' => $customer->bookings()->where('status', 'completed')->sum('fare_amount'),
            'loyaltyPoints' => $customer->loyalty_points,
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

        $promotions = Promotion::active()->limit(5)->get();
        $coupons = Coupon::active()->latest()->limit(4)->get();

        return view('customer.dashboard', compact(
            'stats',
            'nextRide',
            'recentBookings',
            'favoriteVehicles',
            'promotions',
            'coupons'
        ));
    }
}
