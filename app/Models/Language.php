<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    public const CACHE_ACTIVE_KEY = 'languages.active';

    public const CACHE_DEFAULT_KEY = 'languages.default';

    protected $fillable = [
        'name',
        'native_name',
        'code',
        'flag',
        'direction',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_ACTIVE_KEY);
        Cache::forget(self::CACHE_DEFAULT_KEY);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public function getFlagUrlAttribute(): ?string
    {
        return $this->flag ? asset('public/uploads/languages/'.$this->flag) : null;
    }

    /**
     * All enabled languages, cached.
     */
    public static function active(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(self::CACHE_ACTIVE_KEY, function () {
            return self::where('is_active', true)->orderBy('name')->get();
        });
    }

    public static function default(): ?self
    {
        return Cache::rememberForever(self::CACHE_DEFAULT_KEY, function () {
            return self::where('is_default', true)->first() ?? self::orderBy('id')->first();
        });
    }

    public static function defaultCode(): string
    {
        return self::default()?->code ?? config('app.locale', 'en');
    }

    public static function findActiveByCode(string $code): ?self
    {
        return self::active()->firstWhere('code', $code);
    }

    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }
}
