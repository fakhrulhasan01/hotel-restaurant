<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class HotelFloor extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'picture_url',
    ];

    /**
     * Get the picture URL attribute
     */
    public function pictureUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->picture ? asset_url_local_s3('hotel-floors/' . $this->picture) : null;
        });
    }

    /**
     * Get the hotel that owns the floor
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all rooms on this floor
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class)->orderBy('sort_order');
    }
}
