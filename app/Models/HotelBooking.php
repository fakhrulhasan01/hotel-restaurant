<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelBooking extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'checkin_time' => 'datetime:H:i',
        'checkout_time' => 'datetime:H:i',
        'price' => 'decimal:2',
    ];

    /**
     * Get the hotel for this booking
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the customer for this booking
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all rooms for this booking
     */
    public function bookingRooms(): HasMany
    {
        return $this->hasMany(BookingRoom::class, 'booking_id');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'checked-in' => 'green',
            'checked-out' => 'gray',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Calculate total price from all rooms
     */
    public function calculateTotalPrice(): float
    {
        return $this->bookingRooms->sum('price');
    }
}

