<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
   protected $fillable = [
    'user_id', 'session_id', 'status',
    'subtotal', 'discount', 'shipping', 'total',
    'coupon_code',
    'customer_province', 'customer_municipality',
    'shipping_method', 'shipping_hours',
    'expires_at', 'converted_at',
];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'shipping'     => 'decimal:2',
        'total'        => 'decimal:2',
        'expires_at'   => 'datetime',
        'converted_at' => 'datetime',
    ];

    /* ============ Relacionamentos ============ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /* ============ Helpers ============ */

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /* ============ Escopos ============ */

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'open')
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }
}