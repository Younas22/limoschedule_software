<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PricingRule extends Model
{
    public const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    protected $fillable = [
        'vehicle_category_id',
        'label',
        'base_fare',
        'base_fare_threshold_km',
        'km_fare',
        'long_distance_threshold_km',
        'long_distance_km_fare',
        'hour_fare',
        'waiting_charge_per_minute',
        'free_waiting_minutes',
        'night_charge',
        'night_start_time',
        'night_end_time',
        'weekend_charge',
        'weekend_days',
        'toll_charge',
        'airport_surcharge',
        'service_fee',
        'minimum_fare',
        'included_km',
        'included_hours',
        'included_passengers',
        'extra_passenger_charge',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fare' => 'decimal:2',
            'base_fare_threshold_km' => 'decimal:2',
            'km_fare' => 'decimal:2',
            'long_distance_threshold_km' => 'decimal:2',
            'long_distance_km_fare' => 'decimal:2',
            'hour_fare' => 'decimal:2',
            'waiting_charge_per_minute' => 'decimal:2',
            'free_waiting_minutes' => 'integer',
            'night_charge' => 'decimal:2',
            'weekend_charge' => 'decimal:2',
            'weekend_days' => 'array',
            'toll_charge' => 'decimal:2',
            'airport_surcharge' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'minimum_fare' => 'decimal:2',
            'included_km' => 'decimal:2',
            'included_hours' => 'decimal:2',
            'included_passengers' => 'integer',
            'extra_passenger_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public static function global(): self
    {
        return static::firstOrCreate(
            ['vehicle_category_id' => null],
            [
                'label' => 'Global Default',
                'base_fare' => 25,
                'km_fare' => 2.5,
                'hour_fare' => 40,
                'waiting_charge_per_minute' => 0.5,
                'free_waiting_minutes' => 10,
                'night_charge' => 15,
                'weekend_charge' => 10,
                'weekend_days' => ['Saturday', 'Sunday'],
                'toll_charge' => 8,
                'airport_surcharge' => 20,
                'service_fee' => 5,
                'minimum_fare' => 0,
                'included_km' => 0,
                'included_hours' => 0,
                'included_passengers' => 4,
                'extra_passenger_charge' => 0,
            ]
        );
    }

    public static function resolveForVehicle(Vehicle $vehicle): self
    {
        $rule = $vehicle->vehicle_category_id
            ? static::where('vehicle_category_id', $vehicle->vehicle_category_id)->where('is_active', true)->first()
            : null;

        return $rule ?? static::global();
    }

    /**
     * Whether the base fare applies to a trip of this total distance — when
     * a threshold is set, only short trips (at or under it) get the base
     * fare; longer trips bill purely on distance instead.
     */
    public function appliesBaseFare(float $totalDistanceKm): bool
    {
        return $this->base_fare_threshold_km === null || $totalDistanceKm <= (float) $this->base_fare_threshold_km;
    }

    /**
     * The per-km rate to bill for a trip of this total distance — the whole
     * trip bills at long_distance_km_fare once the threshold is crossed,
     * rather than blending both rates across the trip.
     */
    public function effectiveKmFare(float $totalDistanceKm): float
    {
        if (
            $this->long_distance_threshold_km !== null
            && $this->long_distance_km_fare !== null
            && $totalDistanceKm >= (float) $this->long_distance_threshold_km
        ) {
            return (float) $this->long_distance_km_fare;
        }

        return (float) $this->km_fare;
    }

    public function isNight(Carbon $pickupDateTime): bool
    {
        $time = $pickupDateTime->format('H:i:s');
        $start = $this->night_start_time instanceof Carbon ? $this->night_start_time->format('H:i:s') : (string) $this->night_start_time;
        $end = $this->night_end_time instanceof Carbon ? $this->night_end_time->format('H:i:s') : (string) $this->night_end_time;

        if ($start === $end) {
            return false;
        }

        // Overnight window (e.g. 22:00 - 06:00) wraps past midnight.
        if ($start > $end) {
            return $time >= $start || $time < $end;
        }

        return $time >= $start && $time < $end;
    }

    public function isWeekend(Carbon $pickupDateTime): bool
    {
        return in_array($pickupDateTime->format('l'), $this->weekend_days ?? [], true);
    }
}
