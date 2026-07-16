<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'site.settings';

    protected $fillable = [
        'company_name',
        'tagline',
        'logo',
        'favicon',
        'address',
        'email',
        'phone',
        'whatsapp',
        'timezone',
        'date_format',
        'tax_label',
        'tax_rate',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'background_color',
        'theme_mode',
        'meta_description',
        'meta_keywords',
        'og_image',
        'google_site_verification',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'youtube_url',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::firstOrCreate(['id' => 1], ['company_name' => config('app.name', 'Limo Schedule')]);
        });
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('uploads/settings/'.$this->logo) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? asset('uploads/settings/'.$this->favicon) : null;
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image ? asset('uploads/settings/'.$this->og_image) : null;
    }

    /**
     * Social profile URLs for structured data ("sameAs") and footer links.
     *
     * @return array<int, string>
     */
    public function getSocialLinksAttribute(): array
    {
        return array_values(array_filter([
            $this->facebook_url,
            $this->instagram_url,
            $this->twitter_url,
            $this->linkedin_url,
            $this->youtube_url,
        ]));
    }

    public function isDarkMode(): bool
    {
        return $this->theme_mode !== 'light';
    }
}
