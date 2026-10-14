<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantItem extends Model
{
    protected $fillable = [
        'variant_id', 'value', 'color_hex', 'price',
        'stock', 'reserved_stock', 'image', 'active', 'position',
    ];

    protected $casts = [
        'price'  => 'decimal:2',
        'active' => 'boolean',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function reservations()
    {
        return $this->morphMany(StockReservation::class, 'stockable');
    }

    /** Estoque disponível = real − reservas ativas */
   public function getAvailableStockAttribute(): int
{
    $reserved = StockReservation::where('stockable_type', static::class)
        ->where('stockable_id', $this->id)
        ->where('expires_at', '>', now())
        ->whereNull('committed_at')
        ->sum('quantity');

    return max(0, (int) $this->stock - (int) $reserved);
}
    public function getIsSoldOutAttribute(): bool
    {
        return $this->available_stock <= 0;
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://picsum.photos/seed/v' . $this->id . '/80/80';
    }
}