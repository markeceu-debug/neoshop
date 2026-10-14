<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\StockReservation;
use App\Models\ShippingZone;
use App\Models\VariantItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CartService
{
    public const CART_TTL_MINUTES = 30;

    public function __construct(private StockService $stock) {}

    /* ============================================================
     * RESOLVER CARRINHO
     * ============================================================ */

    public function resolveCart(bool $createIfMissing = true): ?Cart
    {
        $userId    = Auth::id();
        $sessionId = Session::getId();

        $query = Cart::query()->where('status', 'open');

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }

        $cart = $query->latest('id')->first();

        if ($cart) {
            if ($cart->expires_at && $cart->expires_at->diffInMinutes(now(), true) < 5) {
                $cart->update(['expires_at' => now()->addMinutes(self::CART_TTL_MINUTES)]);
            }
            return $cart;
        }

        if (! $createIfMissing) return null;

        return Cart::create([
            'user_id'    => $userId,
            'session_id' => $sessionId,
            'status'     => 'open',
            'expires_at' => now()->addMinutes(self::CART_TTL_MINUTES),
        ]);
    }

    /* ============================================================
     * CÁLCULO DE PREÇO (padrão Shopee)
     * ============================================================ */

    /**
     * Resolve o preço unitário final de um stockable,
     * já aplicando a promoção ativa (padrão Shopee).
     */
    public function resolveUnitPrice(Model $stockable, ?Product $parentProduct = null): float
    {
        // ---------- 1. Preço base ----------
        $basePrice = 0.0;

        if ($stockable instanceof VariantItem) {
            $variantPrice = (float) ($stockable->price ?? 0);
            $basePrice = $variantPrice > 0
                ? $variantPrice
                : (float) ($parentProduct?->price ?? $stockable->variant?->product?->price ?? 0);
        } elseif ($stockable instanceof Product) {
            $basePrice = (float) ($stockable->price ?? 0);
        }

        if ($basePrice <= 0) return 0.0;

        // ---------- 2. Promoção ativa ----------
        $product = $parentProduct
            ?? ($stockable instanceof Product ? $stockable : $stockable->variant?->product);

        if (! $product) return $basePrice;

        $promotion = $this->getActivePromotionForProduct($product);

        if (! $promotion) return $basePrice;

        // ---------- 3. Aplica desconto ----------
        if ($promotion->type === 'percent') {
            return round($basePrice * (1 - $promotion->value / 100), 2);
        }

        if ($promotion->type === 'fixed') {
            return max(0, round($basePrice - $promotion->value, 2));
        }

        return $basePrice;
    }

    /**
     * Busca a promoção ativa que aplica a este produto.
     */
    public function getActivePromotionForProduct(Product $product): ?Promotion
    {
        return Promotion::active()
            ->where(function ($q) use ($product) {
                $q->where('applies_to', 'all')
                  ->orWhere(function ($q2) use ($product) {
                      $q2->where('applies_to', 'products')
                         ->whereJsonContains('product_ids', $product->id);
                  })
                  ->orWhere(function ($q2) use ($product) {
                      $q2->where('applies_to', 'categories')
                         ->whereJsonContains('category_ids', $product->category_id);
                  });
            })
            ->orderByDesc('priority')
            ->first();
    }

    /* ============================================================
     * ADICIONAR ITEM
     * ============================================================ */

    public function add(Model $stockable, int $qty, ?Product $parentProduct = null): CartItem
    {
        if ($qty <= 0) {
            throw new RuntimeException('Quantidade deve ser maior que zero.');
        }

    $this->validateMunicipality($stockable, $parentProduct);

        return DB::transaction(function () use ($stockable, $qty, $parentProduct) {
            $cart = $this->resolveCart();

            $isVariant     = $stockable instanceof VariantItem;
            $productId     = $isVariant ? $stockable->variant->product_id : $stockable->id;
            $variantItemId = $isVariant ? $stockable->id : null;

            $product   = $parentProduct ?? Product::find($productId);
            $unitPrice = $this->resolveUnitPrice($stockable, $product);

            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where('variant_item_id', $variantItemId)
                ->first();

            $currentQty = $existing?->quantity ?? 0;
            $newQty     = $currentQty + $qty;

            $newReservation = $this->stock->reserve(
                $stockable,
                $qty,
                Session::getId(),
                (string) $cart->id
            );

            if ($existing) {
                $existing->quantity = $newQty;
                $existing->subtotal = $newQty * $existing->unit_price;

                $oldReservation = $existing->reservation;
                $existing->reservation_id = $newReservation->id;
                $existing->save();

                if ($oldReservation && ! $oldReservation->committed_at) {
                    $oldReservation->delete();
                }

                $newReservation->update(['quantity' => $newQty]);

                $item = $existing;
            } else {
                $item = CartItem::create([
                    'cart_id'         => $cart->id,
                    'product_id'      => $productId,
                    'variant_item_id' => $variantItemId,
                    'quantity'        => $qty,
                    'unit_price'      => $unitPrice,
                    'subtotal'        => $unitPrice * $qty,
                    'reservation_id'  => $newReservation->id,
                ]);
            }

            $this->recalculate($cart);

            return $item->fresh(['product', 'variantItem', 'reservation']);
        });
    }
/**
 * Valida se o produto pode ser comprado pelo cliente.
 * Regras:
 *  1. Se o cliente tem município definido, o vendedor precisa ser do mesmo
 *  2. O carrinho não pode ter produtos de vendedores de municípios diferentes
 *  3. Se o carrinho já tem itens, todos precisam ser do mesmo vendedor
 */
private function validateMunicipality(Model $stockable, ?Product $parentProduct = null): void
{
    // Descobre o produto pai
    $product = $parentProduct
        ?? ($stockable instanceof Product ? $stockable : $stockable->variant?->product);

    if (! $product) return;

    $seller = $product->user;

    // Se o produto não tem vendedor (é da própria plataforma), permite
    if (! $seller) return;

    // Município do cliente
    $customerMunicipality = $this->customerMunicipality();

    // Se o cliente ainda não informou o município, deixa adicionar
    // (a validação real acontece no checkout)
    if (! $customerMunicipality) {
        // Mas se o carrinho já tem itens, precisa ser do mesmo vendedor
        $this->ensureSingleSeller($product);
        return;
    }

    // ---------- Regra 1: vendedor precisa ser do mesmo município ----------
    if ($seller->municipality && $seller->municipality !== $customerMunicipality) {
        throw new RuntimeException(
            "Este produto é vendido em {$seller->municipality} e você está em {$customerMunicipality}. " .
            "Só entregamos no seu município."
        );
    }

    // ---------- Regra 2: o carrinho não pode ter vendedores diferentes ----------
    $this->ensureSingleSeller($product);
}

/**
 * Garante que todos os itens do carrinho são do mesmo vendedor.
 */
private function ensureSingleSeller(Product $newProduct): void
{
    $cart = $this->resolveCart(false);
    if (! $cart || $cart->items()->count() === 0) return;

    // Vendedor do carrinho atual
    $currentSellerId = $cart->items()
        ->with('product')
        ->get()
        ->pluck('product.user_id')
        ->filter()
        ->unique()
        ->first();

    // Se o carrinho está vazio de vendedores, não restringe
    if (! $currentSellerId) return;

    $newSellerId = $newProduct->user_id;

    // Se o novo produto é da plataforma, permite misturar
    if (! $newSellerId) return;

    // Se o vendedor atual é diferente do novo → bloqueia
    if ($currentSellerId !== $newSellerId) {
        throw new RuntimeException(
            "Você já tem produtos de outro vendedor no carrinho. " .
            "Finalize o pedido atual ou esvazie o carrinho antes de adicionar itens de outro vendedor."
        );
    }
}
    /* ============================================================
     * ALTERAR QUANTIDADE
     * ============================================================ */

    public function updateQty(CartItem $item, int $newQty): CartItem
    {
        return DB::transaction(function () use ($item, $newQty) {
            if ($newQty <= 0) {
                $this->removeItem($item);
                return $item;
            }

            $cart      = $item->cart;
            $stockable = $item->stockable;

            if (! $stockable) {
                throw new RuntimeException('Item do carrinho não existe mais.');
            }

            // Re-resolve o preço (promoção pode ter mudado)
            $product   = $item->product;
            $unitPrice = $this->resolveUnitPrice($stockable, $product);

            $diff = $newQty - $item->quantity;

            if ($diff > 0) {
                $extra = $this->stock->reserve(
                    $stockable, $diff, Session::getId(), (string) $cart->id
                );

                $old = $item->reservation;
                $item->reservation_id = $extra->id;
                $extra->update(['quantity' => $newQty]);
                $item->save();

                if ($old && ! $old->committed_at) {
                    $old->delete();
                }
            } elseif ($diff < 0) {
                $reservation = $item->reservation;
                if ($reservation && ! $reservation->committed_at) {
                    $reservation->update(['quantity' => $newQty]);
                }
            }

            $item->unit_price = $unitPrice;
            $item->quantity   = $newQty;
            $item->subtotal   = $unitPrice * $newQty;
            $item->save();

            $this->recalculate($cart);

            return $item->fresh(['product', 'variantItem', 'reservation']);
        });
    }

    /* ============================================================
     * REMOVER ITEM
     * ============================================================ */

    public function removeItem(CartItem $item): void
    {
        DB::transaction(function () use ($item) {
            $cart = $item->cart;

            if ($item->reservation && ! $item->reservation->committed_at) {
                $this->stock->releaseReservation($item->reservation);
            }

            $item->delete();

            $this->recalculate($cart);
        });
    }

    /* ============================================================
     * LIMPAR CARRINHO
     * ============================================================ */

    public function clear(?Cart $cart = null): int
    {
        $cart = $cart ?? $this->resolveCart(false);
        if (! $cart) return 0;

        return DB::transaction(function () use ($cart) {
            $this->stock->releaseCart((string) $cart->id);

            $count = $cart->items()->count();
            $cart->items()->delete();

            $this->recalculate($cart);

            return $count;
        });
    }

    /* ============================================================
     * RECALCULAR TOTAIS
     * ============================================================ */

    /**
     * O `subtotal` já inclui o desconto da promoção (embutido no preço do item).
     * O `discount` do cart guarda APENAS o valor do cupom.
     */
  public function recalculate(Cart $cart): Cart
{
    $subtotal = (float) $cart->items()->sum('subtotal');

    // Recalcula o cupom SE houver um aplicado
    $discount = 0;
    if ($cart->coupon_code) {
        try {
            $coupon = \App\Models\Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon && $coupon->isActive()) {
                $discount = app(\App\Services\CouponService::class)
                    ->calculateDiscount($coupon, $cart);
            } else {
                // Cupom expirou ou foi removido → limpa
                $cart->updateQuietly(['coupon_code' => null]);
            }
        } catch (\Throwable $e) {
            // Se não se aplica mais, limpa
            $cart->updateQuietly(['coupon_code' => null]);
            $discount = 0;
        }
    }

    $cart->update([
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total'    => max(0, $subtotal - $discount + (float) $cart->shipping),
    ]);

    return $cart;
}
    /* ============================================================
     * REVALIDAR
     * ============================================================ */

    public function revalidate(Cart $cart): array
    {
        $issues = [];

        foreach ($cart->items()->with(['product', 'variantItem'])->get() as $item) {
            $stockable = $item->stockable;

            if (! $stockable) {
                $issues[] = [
                    'type'    => 'removed',
                    'item_id' => $item->id,
                    'message' => 'Um produto foi removido do catálogo e precisa ser retirado do carrinho.',
                ];
                continue;
            }

            // Preço mudou?
            $currentPrice = $this->resolveUnitPrice($stockable, $item->product);
            if (abs($currentPrice - (float) $item->unit_price) > 0.01) {
                $issues[] = [
                    'type'      => 'price_changed',
                    'item_id'   => $item->id,
                    'name'      => $item->display_name,
                    'old_price' => (float) $item->unit_price,
                    'new_price' => $currentPrice,
                    'message'   => sprintf(
                        'O preço de "%s" mudou de R$ %s para R$ %s.',
                        $item->display_name,
                        number_format($item->unit_price, 2, ',', '.'),
                        number_format($currentPrice, 2, ',', '.')
                    ),
                ];
            }

            // Estoque caiu?
            $available = $this->stock->available($stockable);
            if ($available < $item->quantity) {
                $issues[] = [
                    'type'      => 'out_of_stock',
                    'item_id'   => $item->id,
                    'name'      => $item->display_name,
                    'available' => $available,
                    'needed'    => $item->quantity,
                    'message'   => sprintf(
                        'Só restam %d unidade(s) de "%s". Você pediu %d.',
                        $available,
                        $item->display_name,
                        $item->quantity
                    ),
                ];
            }
        }

        return $issues;
    }

    /**
     * Sincroniza preços e estoque (usar no botão "Atualizar carrinho").
     */
    public function syncPricesAndStock(Cart $cart): bool
    {
        $changed = false;

        DB::transaction(function () use ($cart, &$changed) {
            foreach ($cart->items()->with(['product', 'variantItem'])->get() as $item) {
                $stockable = $item->stockable;
                if (! $stockable) continue;

                $newPrice = $this->resolveUnitPrice($stockable, $item->product);
                if (abs($newPrice - (float) $item->unit_price) > 0.01) {
                    $item->unit_price = $newPrice;
                    $item->subtotal   = $newPrice * $item->quantity;
                    $item->save();
                    $changed = true;
                }

                $available = $this->stock->available($stockable);
                if ($available < $item->quantity) {
                    if ($available <= 0) {
                        $this->removeItem($item);
                    } else {
                        $item->quantity = $available;
                        $item->subtotal = $item->unit_price * $available;
                        $item->save();

                        if ($item->reservation && ! $item->reservation->committed_at) {
                            $item->reservation->update(['quantity' => $available]);
                        }
                    }
                    $changed = true;
                }
            }

            $this->recalculate($cart);
        });

        return $changed;
    }

    /* ============================================================
     * MIGRAR CARRINHO ANÔNIMO NO LOGIN
     * ============================================================ */

    public function mergeAnonymousIntoUser(int $userId): ?Cart
    {
        $sessionId = Session::getId();

        $anonymous = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $sessionId)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (! $anonymous || $anonymous->items()->count() === 0) {
            return Cart::query()
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->latest('id')
                ->first();
        }

        return DB::transaction(function () use ($anonymous, $userId) {
            $userCart = Cart::query()
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if (! $userCart) {
                $anonymous->update(['user_id' => $userId]);
                return $anonymous;
            }

            foreach ($anonymous->items as $anonItem) {
                $existing = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $anonItem->product_id)
                    ->where('variant_item_id', $anonItem->variant_item_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => $existing->quantity + $anonItem->quantity,
                        'subtotal' => ($existing->quantity + $anonItem->quantity) * $existing->unit_price,
                    ]);
                    if ($anonItem->reservation) {
                        $anonItem->reservation->delete();
                    }
                } else {
                    $anonItem->update(['cart_id' => $userCart->id]);
                }
            }

            $anonymous->update(['status' => 'converted', 'converted_at' => now()]);

            $this->recalculate($userCart);

            return $userCart->fresh();
        });
    }


/**
 * Calcula o frete para o carrinho com base no município do cliente.
 * Usa o frete customizado do vendedor se existir, ou o padrão da zona.
 */
public function calculateShipping(Cart $cart, string $method = 'standard'): array
{
    if (! $cart->customer_province || ! $cart->customer_municipality) {
        return ['fee' => 0, 'hours' => 0, 'options' => []];
    }

    // Pega a zona baseada no município do cliente
    $zone = ShippingZone::forMunicipality(
        $cart->customer_province,
        $cart->customer_municipality
    );

    if (! $zone) {
        return ['fee' => 0, 'hours' => 0, 'options' => [], 'error' => 'Ainda não entregamos neste município.'];
    }

    // Descobre o vendedor principal do carrinho
    $seller = $this->resolveSellerFromCart($cart);

    // Calcula o frete (custom do vendedor OU padrão da zona)
    $fee   = $this->resolveShippingFee($seller, $zone, $cart->subtotal, $method);
    $hours = $this->resolveShippingHours($seller, $zone, $method);

    return [
        'fee'     => $fee,
        'hours'   => $hours,
        'zone'    => $zone,
        'method'  => $method,
        'options' => $this->buildShippingOptions($seller, $zone, $cart->subtotal),
    ];
}

/**
 * Retorna o vendedor do carrinho (se todos os itens forem do mesmo).
 */
private function resolveSellerFromCart(Cart $cart)
{
    $sellers = $cart->items()
        ->with('product.user')
        ->get()
        ->pluck('product.user')
        ->filter()
        ->unique('id');

    return $sellers->count() === 1 ? $sellers->first() : null;
}

/**
 * Calcula o valor do frete.
 */
private function resolveShippingFee($seller, ShippingZone $zone, float $subtotal, string $method): float
{
    // ---------- Frete grátis? ----------
    $freeAbove = null;

    if ($seller && $seller->custom_shipping_enabled) {
        $freeAbove = $seller->custom_free_above;
    } else {
        $freeAbove = $zone->free_above;
    }

    if ($freeAbove !== null && $subtotal >= (float) $freeAbove) {
        return 0.0;
    }

    // ---------- Vendedor tem frete próprio? ----------
    if ($seller && $seller->custom_shipping_enabled && $seller->custom_shipping_fee !== null) {
        return (float) $seller->custom_shipping_fee;
    }

    // ---------- Senão, usa o padrão da zona ----------
    return match ($method) {
        'express' => (float) $zone->express_fee,
        default   => (float) $zone->standard_fee,
    };
}

/**
 * Calcula o tempo estimado.
 */
private function resolveShippingHours($seller, ShippingZone $zone, string $method): int
{
    if ($seller && $seller->custom_shipping_enabled && $seller->custom_shipping_hours) {
        return (int) $seller->custom_shipping_hours;
    }

    return match ($method) {
        'express' => (int) $zone->express_hours,
        default   => (int) $zone->standard_hours,
    };
}

/**
 * Monta a lista de opções de entrega para o cliente.
 */
private function buildShippingOptions($seller, ShippingZone $zone, float $subtotal): array
{
    $options = [];

    // ---------- Padrão ----------
    $standardFee = $this->resolveShippingFee($seller, $zone, $subtotal, 'standard');
    $options[] = [
        'code'    => 'standard',
        'name'    => 'Motoboy Padrão',
        'hours'   => $this->resolveShippingHours($seller, $zone, 'standard'),
        'fee'     => $standardFee,
        'is_free' => $standardFee <= 0,
    ];

    // ---------- Expresso ----------
    $expressFee = $this->resolveShippingFee($seller, $zone, $subtotal, 'express');
    $expressAvailable = $seller && $seller->custom_shipping_enabled
        ? true
        : ($zone->express_fee > 0);

    if ($expressAvailable && $expressFee > 0) {
        $options[] = [
            'code'    => 'express',
            'name'    => 'Motoboy Expresso',
            'hours'   => $this->resolveShippingHours($seller, $zone, 'express'),
            'fee'     => $expressFee,
            'is_free' => $expressFee <= 0,
        ];
    }

    return $options;
}


/**
 * Salva a localização do cliente no carrinho.
 */
public function setCustomerLocation(Cart $cart, string $province, string $municipality, string $method = 'standard'): void
{
    $result = $this->calculateShipping($cart, $method);

    if (isset($result['error'])) {
        throw new \RuntimeException($result['error']);
    }

    $cart->update([
        'customer_province'    => $province,
        'customer_municipality' => $municipality,
        'shipping_method'      => $method,
        'shipping_hours'       => $result['hours'],
        'shipping'             => $result['fee'],
    ]);

    $this->recalculate($cart);
}

/**
 * Atualiza apenas o método de envio.
 */
public function setShippingMethod(Cart $cart, string $method): void
{
    if (! $cart->customer_province || ! $cart->customer_municipality) {
        throw new \RuntimeException('Defina o endereço de entrega primeiro.');
    }

    $result = $this->calculateShipping($cart, $method);

    $cart->update([
        'shipping_method' => $method,
        'shipping_hours'  => $result['hours'],
        'shipping'        => $result['fee'],
    ]);

    $this->recalculate($cart);
}

/**
 * Retorna o município do cliente (logado ou sessão).
 */
public function customerMunicipality(): ?string
{
    if (Auth::check() && Auth::user()->municipality) {
        return Auth::user()->municipality;
    }
    return Session::get('customer_municipality');
}

public function customerProvince(): ?string
{
    if (Auth::check() && Auth::user()->province) {
        return Auth::user()->province;
    }
    return Session::get('customer_province');
}
    /* ============================================================
     * HELPERS DE LEITURA
     * ============================================================ */

    public function count(): int
    {
        $cart = $this->resolveCart(false);
        return $cart ? $cart->items_count : 0;
    }

    public function subtotal(): float
    {
        $cart = $this->resolveCart(false);
        return $cart ? (float) $cart->subtotal : 0;
    }

    public function items()
    {
        $cart = $this->resolveCart(false);
        return $cart
            ? $cart->items()->with(['product', 'variantItem', 'reservation'])->get()
            : collect();
    }
}