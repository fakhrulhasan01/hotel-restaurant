<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Hotel extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'checkin_time' => 'datetime:H:i',
        'checkout_time' => 'datetime:H:i',
    ];

    protected $appends = [
        'logo_url',
    ];

    /**
     * Get the logo URL attribute
     */
    public function logoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->logo ? asset_url_local_s3('hotel-logos/' . $this->logo) : null;
        });
    }

    /**
     * Get all floors for this hotel
     */
    public function floors(): HasMany
    {
        return $this->hasMany(HotelFloor::class)->orderBy('sort_order');
    }

    /**
     * Get all rooms for this hotel
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class)->orderBy('sort_order');
    }

    /**
     * Get all restaurants for this hotel
     */
    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    /**
     * Get all bookings for this hotel
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class);
    }
}
