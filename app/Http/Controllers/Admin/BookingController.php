<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PricingRule;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingNotification;
use App\Notifications\Customer\BookingCancelledNotification as CustomerBookingCancelledNotification;
use App\Notifications\Customer\BookingConfirmedNotification as CustomerBookingConfirmedNotification;
use App\Notifications\Customer\DriverAssignedNotification as CustomerDriverAssignedNotification;
use App\Notifications\Customer\PaymentCompletedNotification as CustomerPaymentCompletedNotification;
use App\Notifications\PaymentSuccessfulNotification;
use App\Services\BookingCreationService;
use App\Services\WhatsAppBookingLinkGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request, WhatsAppBookingLinkGenerator $whatsapp): View
    {
        $bookings = Booking::with(['customer', 'driver', 'vehicle'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->latest('pickup_datetime')
            ->get();

        return view('admin.bookings.index', compact('bookings', 'whatsapp'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'driver', 'vehicle.category']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function create(): View|RedirectResponse
    {
        if (! booking_setting('manual_booking_enabled', true)) {
            return redirect()->route('admin.bookings.index')->with('error', 'Manual booking is currently disabled in Booking Settings.');
        }

        return view('admin.bookings.create', $this->formOptions());
    }

    public function store(Request $request, BookingCreationService $bookingCreation): RedirectResponse
    {
        if (! booking_setting('manual_booking_enabled', true)) {
            return redirect()->route('admin.bookings.index')->with('error', 'Manual booking is currently disabled in Booking Settings.');
        }

        $data = $bookingCreation->attachFareBreakdown($this->prepareData($this->validateBooking($request)));
        $data = $bookingCreation->applyPolicies($data);

        $booking = Booking::create($data);

        $bookingCreation->notifyAdminsOfCreation($booking);
        $bookingCreation->notifyCustomerOfCreation($booking);

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking \"{$booking->booking_number}\" created successfully.");
    }

    public function edit(Booking $booking, WhatsAppBookingLinkGenerator $whatsapp): View
    {
        return view('admin.bookings.edit', $this->formOptions() + ['booking' => $booking, 'whatsapp' => $whatsapp]);
    }

    public function update(Request $request, Booking $booking, BookingCreationService $bookingCreation): RedirectResponse
    {
        $previousStatus = $booking->status;
        $previousDriverId = $booking->driver_id;
        $data = $bookingCreation->attachFareBreakdown($this->prepareData($this->validateBooking($request)));

        $booking->update($data);

        $this->notifyStatusTransition($booking, $previousStatus, $booking->status);
        $this->notifyDriverAssignment($booking, $previousDriverId);

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking \"{$booking->booking_number}\" updated successfully.");
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return back()->with('status', 'Booking deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_ids' => ['required', 'array', 'min:1'],
            'booking_ids.*' => ['integer', 'exists:bookings,id'],
        ]);

        $count = Booking::whereIn('id', $data['booking_ids'])->delete();

        return back()->with('status', $count === 1 ? '1 booking deleted successfully.' : "{$count} bookings deleted successfully.");
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
        ]);

        $previousStatus = $booking->status;
        $booking->update($data);

        $this->notifyStatusTransition($booking, $previousStatus, $booking->status);

        return back()->with('status', "Booking \"{$booking->booking_number}\" marked as {$booking->status_label}.");
    }

    public function markAsPaid(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->payment_status === 'paid') {
            return back()->with('error', "Booking \"{$booking->booking_number}\" is already marked as paid.");
        }

        $data = $request->validate([
            'payment_gateway' => ['required', Rule::in(array_keys(Booking::PAYMENT_GATEWAYS))],
        ]);

        $booking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_gateway' => $data['payment_gateway'],
            'transaction_id' => $this->generateTransactionId($data['payment_gateway']),
        ]);

        $this->notifyAdmins(new PaymentSuccessfulNotification($booking));
        $booking->customer?->notify(new CustomerPaymentCompletedNotification($booking));

        return back()->with('status', "Payment recorded for booking \"{$booking->booking_number}\".");
    }

    public function processRefund(Booking $booking): RedirectResponse
    {
        if ($booking->refund_status !== 'pending') {
            return back()->with('error', "Booking \"{$booking->booking_number}\" has no pending refund.");
        }

        $booking->update(['refund_status' => 'refunded', 'payment_status' => 'refunded']);

        if ($booking->customer) {
            $booking->customer->creditWallet(
                (float) $booking->fare_amount,
                "Refund for booking #{$booking->booking_number}",
                auth('admin')->id()
            );
        }

        return back()->with('status', 'Refund of '.currency($booking->fare_amount)." credited to wallet for booking \"{$booking->booking_number}\".");
    }

    private function generateTransactionId(string $gateway): string
    {
        return match ($gateway) {
            'stripe' => 'pi_'.Str::lower(Str::random(24)),
            'paypal' => 'PAYID-'.Str::upper(Str::random(17)),
            'wallet' => 'WALLET-'.Str::upper(Str::random(10)),
            default => 'TXN-'.Str::upper(Str::random(10)),
        };
    }

    private function notifyStatusTransition(Booking $booking, string $previousStatus, string $newStatus): void
    {
        if ($newStatus === $previousStatus) {
            return;
        }

        if ($newStatus === 'confirmed') {
            $this->notifyAdmins(new BookingConfirmedNotification($booking));
            $booking->customer?->notify(new CustomerBookingConfirmedNotification($booking));
        } elseif ($newStatus === 'cancelled') {
            $this->notifyAdmins(new BookingCancelledNotification($booking));
            $booking->customer?->notify(new CustomerBookingCancelledNotification($booking));
        }
    }

    private function notifyDriverAssignment(Booking $booking, ?int $previousDriverId): void
    {
        if (! $booking->driver_id || $booking->driver_id === $previousDriverId) {
            return;
        }

        $booking->customer?->notify(new CustomerDriverAssignedNotification($booking));
    }

    private function notifyAdmins(BookingNotification $notification): void
    {
        $admins = Admin::withPermission('bookings.view')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }

    private function formOptions(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'email']),
            'vehicles' => Vehicle::with('category')->orderBy('name')->get(),
            'drivers' => Driver::orderBy('name')->get(['id', 'name', 'is_online', 'is_available']),
            'types' => Booking::TYPES,
            'statuses' => Booking::STATUSES,
            'pricingRules' => $this->pricingRulesForJs(),
        ];
    }

    private function pricingRulesForJs(): array
    {
        $format = fn (PricingRule $rule) => [
            'label' => $rule->label ?? 'Global Default',
            'base_fare' => (float) $rule->base_fare,
            'km_fare' => (float) $rule->km_fare,
            'hour_fare' => (float) $rule->hour_fare,
            'waiting_charge_per_minute' => (float) $rule->waiting_charge_per_minute,
            'free_waiting_minutes' => (int) $rule->free_waiting_minutes,
            'night_charge' => (float) $rule->night_charge,
            'night_start_time' => (string) $rule->night_start_time,
            'night_end_time' => (string) $rule->night_end_time,
            'weekend_charge' => (float) $rule->weekend_charge,
            'weekend_days' => $rule->weekend_days ?? [],
            'toll_charge' => (float) $rule->toll_charge,
            'airport_surcharge' => (float) $rule->airport_surcharge,
            'service_fee' => (float) $rule->service_fee,
            'minimum_fare' => (float) $rule->minimum_fare,
            'included_km' => (float) $rule->included_km,
            'included_hours' => (float) $rule->included_hours,
            'included_passengers' => (int) $rule->included_passengers,
            'extra_passenger_charge' => (float) $rule->extra_passenger_charge,
            'is_active' => (bool) $rule->is_active,
        ];

        $rules = ['global' => $format(PricingRule::global())];

        foreach (VehicleCategory::with('pricingRule')->get() as $category) {
            if ($category->pricingRule) {
                $rules[(string) $category->id] = $format($category->pricingRule);
            }
        }

        return $rules;
    }

    private function validateBooking(Request $request): array
    {
        $type = $request->input('type');

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'type' => ['required', Rule::in(array_keys(Booking::TYPES))],
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'pickup_location' => ['required', 'string', 'max:255'],
            'dropoff_location' => ['required', 'string', 'max:255'],
            'stops' => ['nullable', 'array'],
            'stops.*' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['required', 'date'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'return_date' => [Rule::requiredIf($type === 'round_trip'), 'nullable', 'date'],
            'return_time' => [Rule::requiredIf($type === 'round_trip'), 'nullable', 'date_format:H:i'],
            'hours' => [Rule::requiredIf($type === 'hourly'), 'nullable', 'integer', 'min:1', 'max:24'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'passengers' => ['required', 'integer', 'min:1', 'max:60'],
            'luggage' => ['required', 'integer', 'min:0', 'max:60'],
            'waiting_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'fare_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $data['has_toll'] = $request->boolean('has_toll');

        return $data;
    }

    private function prepareData(array $data): array
    {
        $data['pickup_datetime'] = Carbon::parse($data['pickup_date'].' '.$data['pickup_time']);

        $data['return_datetime'] = ($data['return_date'] ?? null) && ($data['return_time'] ?? null)
            ? Carbon::parse($data['return_date'].' '.$data['return_time'])
            : null;

        $data['stops'] = collect($data['stops'] ?? [])->filter(fn ($stop) => filled($stop))->values()->all();

        unset($data['pickup_date'], $data['pickup_time'], $data['return_date'], $data['return_time']);

        return $data;
    }

}
