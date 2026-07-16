<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\BookingCreationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * "Website booking" mode of the public booking widget — a lightweight guest
 * checkout that creates a real pending Booking (the same one admins manage),
 * as the website-native alternative to the WhatsApp booking mode.
 */
class BookingRequestController extends Controller
{
    public function store(Request $request, BookingCreationService $bookingCreation): RedirectResponse
    {
        $settings = BookingSetting::current();

        if (! $settings->website_booking_enabled || ! $settings->guest_booking_enabled) {
            return back()->withInput()->with('error', 'Website booking is currently unavailable. Please contact us via WhatsApp instead.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'vehicle_category_id' => ['required', 'exists:vehicle_categories,id'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'dropoff_location' => ['required', 'string', 'max:255'],
            'stops' => ['nullable', 'array'],
            'stops.*' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'passengers' => ['required', 'integer', 'min:1', 'max:20'],
            'luggage' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $vehicle = Vehicle::where('vehicle_category_id', $data['vehicle_category_id'])
            ->where('status', true)
            ->inRandomOrder()
            ->first();

        if (! $vehicle) {
            return back()->withInput()->with('error', 'That vehicle category is not currently available. Please choose another, or contact us via WhatsApp.');
        }

        $customer = Customer::firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'phone' => $data['phone'] ?? null, 'status' => true]
        );

        $bookingData = [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => null,
            'type' => 'one_way',
            'pickup_location' => $data['pickup_location'],
            'dropoff_location' => $data['dropoff_location'],
            'stops' => collect($data['stops'] ?? [])->filter(fn ($stop) => filled($stop))->values()->all(),
            'pickup_datetime' => Carbon::parse($data['pickup_date'].' '.$data['pickup_time']),
            'return_datetime' => null,
            'hours' => null,
            'distance_km' => null,
            'passengers' => $data['passengers'],
            'luggage' => $data['luggage'],
            'waiting_minutes' => 0,
            'has_toll' => false,
            'notes' => null,
            'status' => 'pending',
            'payment_status' => 'pending',
        ];

        $bookingData = $bookingCreation->attachFareBreakdown($bookingData);
        $bookingData = $bookingCreation->applyPolicies($bookingData);

        $booking = Booking::create($bookingData);

        $bookingCreation->notifyAdminsOfCreation($booking);
        $bookingCreation->notifyCustomerOfCreation($booking);

        return redirect()->route('booking.confirmation', $booking->booking_number);
    }

    public function confirmation(string $bookingNumber): View
    {
        $booking = Booking::with(['vehicle.category'])->where('booking_number', $bookingNumber)->firstOrFail();

        return view('booking.confirmation', compact('booking'));
    }

    public function invoice(string $bookingNumber): View
    {
        $booking = Booking::with(['vehicle.category', 'driver', 'customer'])->where('booking_number', $bookingNumber)->firstOrFail();

        return view('booking.invoice', compact('booking'));
    }

    public function downloadInvoice(string $bookingNumber)
    {
        $booking = Booking::with(['vehicle.category', 'driver', 'customer'])->where('booking_number', $bookingNumber)->firstOrFail();

        $filename = ($booking->payment_status === 'paid' ? 'receipt-' : 'invoice-').$booking->invoice_number.'.pdf';

        return Pdf::loadView('booking.invoice-pdf', compact('booking'))
            ->setPaper('a4')
            ->download($filename);
    }
}
