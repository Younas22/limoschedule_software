<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PopularRoute extends Model
{
    public const TYPES = [
        'airport' => 'Airport Route',
        'city' => 'City Route',
        'intercity' => 'Intercity Route',
    ];

    protected $fillable = [
        'type',
        'pickup',
        'dropoff',
        'distance',
        'distance_unit',
        'estimated_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
            'estimated_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
