<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'name',
        'email',
        'phone',
        'address',
        'license_number',
        'passport_number',
        'national_id',
        'photo',
        'status',
        'commission_rate',
        'is_online',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_online' => 'boolean',
            'is_available' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('uploads/drivers/'.$this->photo) : null;
    }

    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->reviews()->approved()->avg('rating');

        return $average ? round((float) $average, 1) : null;
    }
}
