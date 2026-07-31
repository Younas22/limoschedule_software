<?php

namespace App\Services;

use App\Models\PageSection;

/**
 * Resolves the head office's coordinates for dispatch logic without ever
 * asking the admin to enter them — the office address is already stored in
 * the "Contact Info" page section's content JSON (used for the public
 * contact page). The first time coordinates are needed, this geocodes that
 * address once and persists the result back into the same section, so it
 * is never re-geocoded.
 */
class OfficeLocationService
{
    public function __construct(private readonly GoogleMapsService $googleMaps) {}

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function coordinates(): ?array
    {
        $section = PageSection::where('type', 'contact_info')->first();

        if (! $section) {
            return null;
        }

        $content = $section->content ?? [];

        if (isset($content['office_lat'], $content['office_lng'])) {
            return [
                'lat' => (float) $content['office_lat'],
                'lng' => (float) $content['office_lng'],
            ];
        }

        $address = $content['address'] ?? null;

        if (! $address) {
            return null;
        }

        $geocoded = $this->googleMaps->geocode($address);

        if (! $geocoded) {
            return null;
        }

        $section->update([
            'content' => array_merge($content, [
                'office_lat' => $geocoded['lat'],
                'office_lng' => $geocoded['lng'],
            ]),
        ]);

        return $geocoded;
    }
}
