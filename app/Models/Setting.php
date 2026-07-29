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
        'meta_title',
        'logo',
        'logo_dark',
        'invoice_logo_dark',
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
        'google_analytics_id',
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
            'invoice_logo_dark' => 'boolean',
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
        return $this->logo ? asset('public/uploads/settings/'.$this->logo) : null;
    }

    public function getLogoDarkUrlAttribute(): ?string
    {
        return $this->logo_dark ? asset('public/uploads/settings/'.$this->logo_dark) : null;
    }

    /**
     * The logo to render on invoices, honoring the Black/White toggle with a
     * fallback to whichever logo is actually uploaded.
     */
    public function getInvoiceLogoUrlAttribute(): ?string
    {
        return $this->invoice_logo_dark
            ? ($this->logo_dark_url ?? $this->logo_url)
            : ($this->logo_url ?? $this->logo_dark_url);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? asset('public/uploads/settings/'.$this->favicon) : null;
    }

    /**
     * Local filesystem path (not a URL) of the active invoice logo, for
     * dompdf which reads image files directly rather than over HTTP.
     */
    public function getInvoiceLogoPathAttribute(): ?string
    {
        $filename = $this->invoice_logo_dark
            ? ($this->logo_dark ?: $this->logo)
            : ($this->logo ?: $this->logo_dark);

        if (! $filename) {
            return null;
        }

        $path = public_path('uploads/settings/'.$filename);

        return file_exists($path) ? $path : null;
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image ? asset('public/uploads/settings/'.$this->og_image) : null;
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
