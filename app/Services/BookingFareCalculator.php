<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * Dynamic pricing engine: resolves the vehicle's category pricing rule
 * (falling back to the global default) and builds a full fare breakdown
 * from it. The result is a suggestion — the admin can always override the
 * final fare_amount by hand.
 */
class BookingFareCalculator
{
    public function calculate(
        Vehicle $vehicle,
        string $type,
        ?float $distanceKm,
        ?int $hours,
        Carbon $pickupDateTime,
        int $waitingMinutes = 0,
        bool $hasToll = false
    ): float {
        return $this->breakdown($vehicle, $type, $distanceKm, $hours, $pickupDateTime, $waitingMinutes, $hasToll)['total'];
    }

    /**
     * @return array<string, float>
     */
    public function breakdown(
        Vehicle $vehicle,
        string $type,
        ?float $distanceKm,
        ?int $hours,
        Carbon $pickupDateTime,
        int $waitingMinutes = 0,
        bool $hasToll = false
    ): array {
        $rule = PricingRule::resolveForVehicle($vehicle);
        $distanceKm = $distanceKm ?? 0;
        $isRoundTrip = $type === 'round_trip';
        $legMultiplier = $isRoundTrip ? 2 : 1;

        $baseFare = round((float) $rule->base_fare * $legMultiplier, 2);

        $distanceFare = $type === 'hourly'
            ? 0.0
            : round((float) $rule->km_fare * $distanceKm * $legMultiplier, 2);

        $hourFare = $type === 'hourly'
            ? round((float) $rule->hour_fare * max($hours ?? 1, 1), 2)
            : 0.0;

        $waitingChargeableMinutes = max($waitingMinutes - $rule->free_waiting_minutes, 0);
        $waitingCharge = round((float) $rule->waiting_charge_per_minute * $waitingChargeableMinutes, 2);

        $nightCharge = $rule->isNight($pickupDateTime) ? round((float) $rule->night_charge, 2) : 0.0;
        $weekendCharge = $rule->isWeekend($pickupDateTime) ? round((float) $rule->weekend_charge, 2) : 0.0;
        $tollCharge = $hasToll ? round((float) $rule->toll_charge, 2) : 0.0;
        $airportSurcharge = $type === 'airport_transfer' ? round((float) $rule->airport_surcharge, 2) : 0.0;
        $serviceFee = round((float) $rule->service_fee, 2);

        $total = round(
            $baseFare + $distanceFare + $hourFare + $waitingCharge
            + $nightCharge + $weekendCharge + $tollCharge + $airportSurcharge + $serviceFee,
            2
        );

        return [
            'base_fare' => $baseFare,
            'distance_fare' => $distanceFare,
            'hour_fare' => $hourFare,
            'waiting_charge' => $waitingCharge,
            'night_charge' => $nightCharge,
            'weekend_charge' => $weekendCharge,
            'toll_charge' => $tollCharge,
            'airport_surcharge' => $airportSurcharge,
            'service_fee' => $serviceFee,
            'total' => $total,
        ];
    }
}
