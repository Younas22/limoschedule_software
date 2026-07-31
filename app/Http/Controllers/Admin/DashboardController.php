<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'bookings' => Booking::count(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('fare_amount'),
            'vehicles' => Vehicle::count(),
            'drivers' => Driver::count(),
            'customers' => Customer::count(),
        ];

        $recentBookings = Booking::with(['customer', 'driver', 'vehicle'])
            ->latest()
            ->take(5)
            ->get();

        $onlineDrivers = Driver::where('is_online', true)->get();

        $fleetSummary = [
            'online' => $onlineDrivers->count(),
            'busy' => $onlineDrivers->filter(fn (Driver $d) => (bool) $d->activeBooking())->count(),
        ];

        return view('admin.dashboard', compact('stats', 'recentBookings', 'fleetSummary'));
    }
}
