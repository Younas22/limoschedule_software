<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    public const PAGES = [
        'home' => 'Home',
        'about' => 'About',
        'services' => 'Services',
        'faq' => 'FAQ',
        'contact' => 'Contact',
        'airport-transfer' => 'Airport Transfer',
        'chauffeur-service' => 'Chauffeur Service',
        'corporate-transfer' => 'Corporate Transfer',
        'city-rides' => 'City Rides',
        'hourly-rides' => 'Hourly Rides',
        'vip-transport' => 'VIP Transport',
        'privacy-policy' => 'Privacy Policy',
        'terms' => 'Terms & Conditions',
        'refund-policy' => 'Refund Policy',
        'cookie-policy' => 'Cookie Policy',
    ];

    /**
     * Slugs for the dedicated service pages, each rendered with a
     * banner/description/fleet/CTA layout and linked from the Services
     * overview page rather than the primary site navigation.
     */
    public const SERVICE_PAGES = [
        'airport-transfer',
        'chauffeur-service',
        'corporate-transfer',
        'city-rides',
        'hourly-rides',
        'vip-transport',
    ];

    /**
     * Legal/compliance pages linked from the footer's "Legal" column
     * rather than the primary site navigation.
     */
    public const LEGAL_PAGES = [
        'privacy-policy',
        'terms',
        'refund-policy',
        'cookie-policy',
    ];

    protected $fillable = [
        'slug',
        'name',
        'meta_title',
        'meta_description',
        'custom_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }
}
