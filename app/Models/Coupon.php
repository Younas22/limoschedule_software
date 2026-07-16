<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const DISCOUNT_TYPES = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
    ];

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_fare',
        'max_discount',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_fare' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_type === 'percentage'
            ? "{$this->formattedDiscountValue()}% off"
            : currency($this->discount_value).' off';
    }

    protected function formattedDiscountValue(): string
    {
        return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }
}
