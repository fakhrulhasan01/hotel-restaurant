<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class HotelRoom extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'pictures' => 'array',
        'is_active' => 'boolean',
        'no_of_beds' => 'integer',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected $appends = [
        'pictures_urls',
        'effective_price',
    ];

    /**
     * Get the pictures URLs attribute
     */
    public function picturesUrls(): Attribute
    {
        return Attribute::get(function (): array {
            if (!$this->pictures || !is_array($this->pictures)) {
                return [];
            }

            return array_map(function ($picture) {
                return asset_url_local_s3('hotel-rooms/' . $picture);
            }, $this->pictures);
        });
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price)
     */
    public function effectivePrice(): Attribute
    {
        return Attribute::get(function (): float {
            return $this->sale_price ?? $this->price;
        });
    }

    /**
     * Get the hotel that owns the room
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the floor that owns the room
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(HotelFloor::class, 'hotel_floor_id');
    }
}
