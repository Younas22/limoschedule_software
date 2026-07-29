<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopularRoute extends Model
{
    protected $fillable = [
        'route_type_id',
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

    public function routeType(): BelongsTo
    {
        return $this->belongsTo(RouteType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, int $routeTypeId): Builder
    {
        return $query->where('route_type_id', $routeTypeId);
    }
}
