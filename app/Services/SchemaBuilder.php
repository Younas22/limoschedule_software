<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Setting;

/**
 * Assembles JSON-LD structured-data blocks purely from data the admin has
 * already entered (Settings, active Areas, a page's own FAQ content) —
 * nothing here is invented or hard-coded per customer. Kept as a plain PHP
 * service (not written inline in a .blade.php file) because Blade's
 * compiler rewrites "@word" sequences as directives even inside embedded
 * PHP/JSON, which corrupts literal "@context"/"@type" array keys — see the
 * same note on organization_schema() in app/helpers.php, which this class
 * builds on rather than duplicates.
 */
class SchemaBuilder
{
    /**
     * The subset of Setting::BUSINESS_TYPES that map onto a real,
     * unambiguous schema.org LocalBusiness subtype. Anything else (limo,
     * chauffeur, black car, "other"...) has no matching schema.org type, so
     * it stays a plain LocalBusiness rather than inventing one.
     */
    private const SCHEMA_TYPE_MAP = [
        'taxi' => 'TaxiService',
        'airport_transfer' => 'TaxiService',
    ];

    /**
     * The site-wide Organization/LocalBusiness block — present on every
     * page. Extends organization_schema()'s base fields with the business
     * type, geo coordinates, opening hours, and service areas, all sourced
     * from data the admin already maintains in Settings/Areas.
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $settings = Setting::current();
        $schema = organization_schema();

        $schema['@type'] = self::SCHEMA_TYPE_MAP[$settings->business_type] ?? 'LocalBusiness';

        if ($settings->office_lat && $settings->office_lng) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $settings->office_lat,
                'longitude' => (float) $settings->office_lng,
            ];
        }

        $hours = $this->openingHours($settings);
        if ($hours !== []) {
            $schema['openingHoursSpecification'] = $hours;
        }

        $areas = Area::active()->ordered()->pluck('name');
        if ($areas->isNotEmpty()) {
            $schema['areaServed'] = $areas->values()->all();
        }

        return $schema;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function openingHours(Setting $settings): array
    {
        $days = [
            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
        ];

        $specs = [];

        foreach ($settings->business_hours_list as $day => $hours) {
            if (! empty($hours['closed'])) {
                continue;
            }

            $specs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'https://schema.org/'.$days[$day],
                'opens' => $hours['open'],
                'closes' => $hours['close'],
            ];
        }

        return $specs;
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => setting('company_name'),
            'url' => url('/'),
        ];
    }

    /**
     * @param  array<int, array{question?: string, answer?: string}>  $items
     * @return array<string, mixed>|null
     */
    public function faqPage(array $items): ?array
    {
        $entities = collect($items)
            ->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))
            ->map(fn ($item) => [
                '@type' => 'Question',
                'name' => strip_tags($item['question']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($item['answer']),
                ],
            ])
            ->values();

        if ($entities->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities->all(),
        ];
    }

    /**
     * @param  array<int, array{label: string, url: ?string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $listItems = collect($items)->values()->map(fn ($item, $index) => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ]))->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }
}
