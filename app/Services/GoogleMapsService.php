<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around Google's Distance Matrix API. Called server-side only
 * — the API key never reaches the browser through this path (the browser
 * gets its own Maps JS/Places script tag with the same key, restricted by
 * HTTP referrer in Google Cloud Console).
 *
 * Never throws: any failure (missing key, network error, no route found)
 * returns null so the booking widget can degrade to "distance unavailable"
 * instead of a broken page.
 */
class GoogleMapsService
{
    /**
     * @return array{distance_km: float, duration_minutes: int}|null
     */
    public function distance(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            return null;
        }

        // Rounded to ~11m precision so nearby-identical requests (e.g. the
        // user nudging an Autocomplete pin) share a cache entry.
        $cacheKey = sprintf(
            'gmaps.distance.%s.%s.%s.%s',
            round($originLat, 4),
            round($originLng, 4),
            round($destLat, 4),
            round($destLng, 4)
        );

        return Cache::remember($cacheKey, now()->addHour(), function () use ($originLat, $originLng, $destLat, $destLng, $key) {
            try {
                $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => "{$originLat},{$originLng}",
                    'destinations' => "{$destLat},{$destLng}",
                    'units' => 'metric',
                    'key' => $key,
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $element = $response->json('rows.0.elements.0');

                if (! $element || ($element['status'] ?? null) !== 'OK') {
                    return null;
                }

                return [
                    'distance_km' => round($element['distance']['value'] / 1000, 2),
                    'duration_minutes' => (int) round($element['duration']['value'] / 60),
                ];
            } catch (\Throwable $e) {
                Log::warning('Google Distance Matrix request failed: '.$e->getMessage());

                return null;
            }
        });
    }
}
