<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    protected $fillable = [
        'stockable_type', 'stockable_id',
    'user_id', 'session_id', 'cart_id',
    'quantity', 'expires_at', 'committed_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'committed_at' => 'datetime',
    ];

    public function stockable()
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->expires_at->isFuture() && $this->committed_at === null;
    }

    public function scopeActive($q)
    {
        return $q->where('expires_at', '>', now())->whereNull('committed_at');
    }

    public function scopeExpired($q)
    {
        return $q->where('expires_at', '<=', now())->whereNull('committed_at');
    }

    public function cartItem()
{
    return $this->hasOne(CartItem::class, 'reservation_id');
}
}