<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'name', 'description',
        'type', 'value', 'min_purchase', 'max_discount',
        'usage_limit', 'per_user_limit', 'used_count',
        'applies_to', 'product_ids', 'category_ids',
        'starts_at', 'expires_at', 'active',
    ];

    protected $casts = [
        'value'          => 'decimal:2',
        'min_purchase'   => 'decimal:2',
        'max_discount'   => 'decimal:2',
        'product_ids'    => 'array',
        'category_ids'   => 'array',
        'starts_at'      => 'datetime',
        'expires_at'     => 'datetime',
        'active'         => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /* ============ Helpers ============ */

    public function isActive(): bool
    {
        if (! $this->active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;

        return true;
    }

    public function getUserUsageCount(?int $userId, ?string $sessionId): int
    {
        $query = $this->redemptions();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return 0;
        }

        return $query->count();
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)
                 ->where(function ($q) {
                     $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                 })
                 ->where(function ($q) {
                     $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                 });
    }
}