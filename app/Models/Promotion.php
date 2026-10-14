<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name', 'description', 'banner',
        'type', 'value',
        'applies_to', 'product_ids', 'category_ids',
        'buy_qty', 'get_qty',
        'starts_at', 'expires_at', 'priority', 'active',
    ];

    protected $casts = [
        'value'        => 'decimal:2',
        'product_ids'  => 'array',
        'category_ids' => 'array',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'active'       => 'boolean',
    ];

    public function isActive(): bool
    {
        if (! $this->active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;

        return true;
    }

    public function appliesToProduct(Product $product): bool
    {
        if ($this->applies_to === 'all') return true;

        if ($this->applies_to === 'products') {
            return in_array($product->id, $this->product_ids ?? []);
        }

        if ($this->applies_to === 'categories') {
            return in_array($product->category_id, $this->category_ids ?? []);
        }

        return false;
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)
                 ->where(function ($q) {
                     $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                 })
                 ->where(function ($q) {
                     $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                 })
                 ->orderByDesc('priority');
    }
}