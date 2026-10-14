<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'variant_item_id',
        'quantity', 'unit_price', 'subtotal', 'reservation_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    /* ============ Relacionamentos ============ */

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantItem(): BelongsTo
    {
        return $this->belongsTo(VariantItem::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(StockReservation::class, 'reservation_id');
    }

    /* ============ Helpers ============ */

    /**
     * Retorna o stockable real deste item:
     * - VariantItem se houver variante escolhida
     * - Product se não houver variante
     */
    public function getStockableAttribute(): ?Model
    {
        if ($this->variant_item_id) {
            return $this->variantItem;
        }

        return $this->product;
    }

    /**
     * Preço efetivo do item (variante pode ter preço próprio).
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->variantItem && $this->variantItem->price !== null) {
            return (float) $this->variantItem->price;
        }

        return (float) ($this->product?->price ?? $this->unit_price);
    }

    /**
     * Nome amigável para exibir no carrinho.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product?->name ?? 'Produto removido';

        if ($this->variantItem) {
            $name .= ' — ' . $this->variantItem->value;
        }

        return $name;
    }

    /**
     * Imagem para exibir (variante pode ter imagem própria).
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->variantItem && $this->variantItem->image) {
            return asset('storage/' . $this->variantItem->image);
        }

        return $this->product?->cover_url ?? 'https://picsum.photos/seed/empty/300/300';
    }
}