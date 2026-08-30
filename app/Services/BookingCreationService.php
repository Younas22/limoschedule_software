<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Coupon;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\Customer\BookingConfirmedNotification as CustomerBookingConfirmedNotification;
use App\Notifications\Customer\DriverAssignedNotification as CustomerDriverAssignedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Shared booking-creation logic used by both the admin manual-booking form
 * and the public website booking widget, so fare calculation and business
 * policies (auto-confirmation, driver auto-assignment) never drift between
 * the two entry points.
 */
class BookingCreationService
{
    public function __construct(private readonly BookingFareCalculator $calculator)
    {
    }

    public function attachFareBreakdown(array $data): array
    {
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        $data['fare_breakdown'] = $this->calculator->breakdown(
            $vehicle,
            $data['type'],
            isset($data['distance_km']) ? (float) $data['distance_km'] : null,
            $data['hours'] ?? null,
            $data['pickup_datetime'],
            (int) ($data['waiting_minutes'] ?? 0),
            (bool) ($data['has_toll'] ?? false),
            (int) ($data['passengers'] ?? 1),
            isset($data['return_distance_km']) ? (float) $data['return_distance_km'] : null
        );

        $data['fare_amount'] = $data['fare_amount'] ?? $data['fare_breakdown']['total'];

        return $data;
    }

    /**
     * Re-validates a submitted coupon code against the fare that was just
     * computed and, if it still holds up, discounts fare_amount and records
     * which coupon was used — this is the one authoritative place a coupon
     * ever actually changes what a customer is charged (the widget's own
     * live preview is a courtesy, never trusted on its own). Silently no-ops
     * for a blank/unknown/no-longer-valid code rather than failing the
     * booking outright — a lapsed coupon shouldn't block a real ride.
     *
     * @return array{0: array, 1: ?Coupon} the (possibly discounted) $data, and the coupon actually applied, if any
     */
    public function applyCoupon(array $data, ?string $couponCode): array
    {
        if (blank($couponCode)) {
            return [$data, null];
        }

        $coupon = Coupon::where('code', strtoupper($couponCode))->first();
        $fareAmount = (float) $data['fare_amount'];

        if (! $coupon || ! $coupon->isValidFor($fareAmount)) {
            return [$data, null];
        }

        $discount = $coupon->discountFor($fareAmount);

        $data['coupon_id'] = $coupon->id;
        $data['discount_amount'] = $discount;
        $data['fare_amount'] = round($fareAmount - $discount, 2);

        return [$data, $coupon];
    }

    /**
     * Applies business rules from Booking Settings to a freshly-submitted
     * booking: driver auto-assignment (takes precedence, being the more
     * advanced pipeline state) and auto-confirmation.
     */
    public function applyPolicies(array $data): array
    {
        $settings = BookingSetting::current();

        if ($settings->driver_auto_assignment_enabled && empty($data['driver_id'])) {
            $driver = Driver::where('is_online', true)
                ->where('is_available', true)
                ->inRandomOrder()
                ->first();

            if ($driver) {
                $data['driver_id'] = $driver->id;

                if (in_array($data['status'], ['pending', 'confirmed'], true)) {
                    $data['status'] = 'assigned';
                }
            }
        }

        if ($settings->auto_confirmation_enabled && $data['status'] === 'pending') {
            $data['status'] = 'confirmed';
        }

        return $data;
    }

    /**
     * Notifies permission-holding admins that a new booking has just been
     * created, plus a follow-up confirmation notice if policies already
     * advanced it straight to "confirmed".
     */
    public function notifyAdminsOfCreation(Booking $booking): void
    {
        $admins = Admin::withPermission('bookings.view')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new BookingCreatedNotification($booking));

        if ($booking->status === 'confirmed') {
            Notification::send($admins, new BookingConfirmedNotification($booking));
        }
    }

    /**
     * Notifies the customer of the outcome once policies (auto-assignment,
     * auto-confirmation) have already been applied to a freshly-created booking.
     */
    public function notifyCustomerOfCreation(Booking $booking): void
    {
        if (! $booking->customer) {
            return;
        }

        if ($booking->status === 'confirmed') {
            $booking->customer->notify(new CustomerBookingConfirmedNotification($booking));
        }

        if ($booking->driver_id) {
            $booking->customer->notify(new CustomerDriverAssignedNotification($booking));
        }
    }
}
