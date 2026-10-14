<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'slug', 'sku', 'description',
        'price', 'old_price', 'stock', 'has_variants', 'total_stock',
        'sold', 'category', 'image', 'status',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'old_price'    => 'decimal:2',
        'has_variants' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($p) {
            if (empty($p->slug)) $p->slug = Str::slug($p->name) . '-' . uniqid();
            if (empty($p->sku))  $p->sku  = 'SKU-' . strtoupper(Str::random(8));
        });
    }

    /* ============================================================
     * RELACIONAMENTOS
     * ============================================================ */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class)->orderBy('position');
    }

    /**
     * Relacionamento hasManyThrough corrigido — evita a ambiguidade do `id`.
     * Usado quando você quiser carregar os itens como coleção Eloquent.
     */
    public function variantItems()
    {
        return $this->hasManyThrough(
            VariantItem::class,
            Variant::class,
            'product_id',   // FK em variants
            'variant_id',   // FK em variant_items
            'id',           // PK em products
            'id'            // PK em variants
        )->select('variant_items.*');
    }

    /**
     * Query builder dos itens de variante deste produto.
     * Mais performático que hasManyThrough e sem ambiguidade.
     */
    public function variantItemsQuery()
    {
        return VariantItem::query()
            ->whereIn('variant_id', $this->variants()->pluck('id'));
    }

    public function variantItemIds()
{
    return VariantItem::whereIn('variant_id', $this->variants()->pluck('id'))
        ->pluck('id');
}


public function categoryRelation()
{
    return $this->belongsTo(Category::class, 'category_id');
}
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function coverImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_cover', true);
    }

    public function reservations()
    {
        return $this->morphMany(StockReservation::class, 'stockable');
    }

    /* ============================================================
     * ACESSORES — URL da imagem
     * ============================================================ */

    public function getCoverUrlAttribute(): string
    {
        if ($this->coverImage) return $this->coverImage->url;
        if ($this->image)      return asset('storage/' . $this->image);

        return 'https://picsum.photos/seed/p' . $this->id . '/300/300';
    }

    /* ============================================================
     * ACESSORES — estoque
     * ============================================================ */

    /**
     * Estoque REAL (físico) — soma das variantes ou coluna do produto.
     * Não desconta reservas.
     */
    public function getRealStockAttribute(): int
    {
        if ($this->has_variants) {
            return (int) VariantItem::whereIn('variant_id', $this->variants()->pluck('id'))
                                   ->sum('stock');
        }

        return (int) ($this->attributes['stock'] ?? 0);
    }

    /**
     * Estoque DISPONÍVEL — desconta reservas ativas.
     * É este valor que a vitrine deve mostrar.
     */
  public function getAvailableStockAttribute(): int
{
    
    $reserved = StockReservation::where('stockable_type', static::class)
        ->where('stockable_id', $this->id)
        ->where('expires_at', '>', now())
        ->whereNull('committed_at')
        ->sum('quantity');

    // Reservas ativas dos itens de variante
    if ($this->has_variants) {
        $variantItemIds = VariantItem::whereIn('variant_id', $this->variants()->pluck('id'))
                                     ->pluck('id');

        if ($variantItemIds->isNotEmpty()) {
            $reserved += StockReservation::where('stockable_type', VariantItem::class)
                ->whereIn('stockable_id', $variantItemIds)
                ->where('expires_at', '>', now())
                ->whereNull('committed_at')
                ->sum('quantity');
        }
    }

    return max(0, $this->real_stock - (int) $reserved);
}

    public function getIsSoldOutAttribute(): bool
    {
        return $this->available_stock <= 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        $available = $this->available_stock;

        return $available > 0 && $available <= 5;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'Ativo',
            'inactive'     => 'Inativo',
            'out_of_stock' => 'Esgotado',
            default        => '—',
        };
    }

    /* ============================================================
 * PROMOÇÕES
 * ============================================================ */

/**
 * Retorna a promoção ativa que se aplica a este produto (ou null).
 * Cacheia em memória para não reconsultar no mesmo request.
 */
public function getActivePromotionAttribute()
{
    if (array_key_exists('_active_promotion', $this->attributes)) {
        return $this->attributes['_active_promotion'];
    }

    $promo = \App\Models\Promotion::active()
        ->where(function ($q) {
            $q->where('applies_to', 'all');

            $q->orWhere(function ($q2) {
                $q2->where('applies_to', 'products')
                   ->where(function ($q3) {
                       $q3->whereNull('product_ids')
                          ->orWhereJsonContains('product_ids', (string) $this->id)
                          ->orWhereJsonContains('product_ids', $this->id);
                   });
            });

            $q->orWhere(function ($q2) {
                $q2->where('applies_to', 'categories')
                   ->where(function ($q3) {
                       $q3->whereNull('category_ids')
                          ->orWhereJsonContains('category_ids', (string) $this->category_id)
                          ->orWhereJsonContains('category_ids', $this->category_id);
                   });
            });
        })
        ->orderByDesc('priority')
        ->first();

    $this->attributes['_active_promotion'] = $promo;

    return $promo;
}

/**
 * Preço final com promoção aplicada (se houver).
 */
public function getFinalPriceAttribute(): float
{
    $base = (float) $this->price;
    $promo = $this->active_promotion;

    if (! $promo) {
        return $base;
    }

    if ($promo->type === 'percent') {
        $discount = $base * ($promo->value / 100);
    } elseif ($promo->type === 'fixed') {
        $discount = min((float) $promo->value, $base);
    } else {
        $discount = 0;
    }

    return max(0, $base - $discount);
}

/**
 * Preço original (o "de") — só retorna se realmente for maior que o final.
 */
public function getOriginalPriceAttribute(): ?float
{
    $final = $this->final_price;

    // Se promoção aplicou, o "de" é o preço base
    if ($this->active_promotion && $final < (float) $this->price) {
        return (float) $this->price;
    }

    // Senão, usa o old_price (campo normal)
    if ($this->old_price && (float) $this->old_price > $final) {
        return (float) $this->old_price;
    }

    return null;
}

/**
 * % de desconto exibido no badge.
 */
public function getDiscountPercentAttribute(): int
{
    $original = $this->original_price;
    $final    = $this->final_price;

    if (! $original || $original <= 0) return 0;

    return (int) round((1 - $final / $original) * 100);
}
}