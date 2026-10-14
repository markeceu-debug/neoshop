<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\VariantItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /** Minutos que uma reserva de carrinho fica ativa. */
    public const RESERVATION_MINUTES = 30;

    /* ============================================================
     * LEITURA — disponibilidade
     * ============================================================ */

    /**
     * Estoque disponível de um stockable (produto ou item de variante).
     * Já desconta reservas ativas.
     */
    public function available(Model $stockable): int
    {
        if ($stockable instanceof VariantItem) {
            return $stockable->available_stock;
        }

        if ($stockable instanceof Product && $stockable->has_variants) {
            return $stockable->available_stock;
        }

        // Produto sem variante
        $reserved = StockReservation::where('stockable_type', get_class($stockable))
            ->where('stockable_id', $stockable->id)
            ->active()
            ->sum('quantity');

        return max(0, (int) $stockable->stock - (int) $reserved);
    }

    /* ============================================================
     * ADMIN — movimentação direta (reposição, perda, ajuste manual)
     * ============================================================ */

    public function increment(Model $stockable, int $qty, string $reason = 'reposição', ?string $ref = null): ?StockMovement
    {
        $this->assertPositive($qty);
        return $this->apply($stockable, $qty, 'in', $reason, $ref);
    }

    public function decrement(Model $stockable, int $qty, string $reason = 'venda', ?string $ref = null): StockMovement
    {
        $this->assertPositive($qty);

        if ($this->available($stockable) < $qty) {
            throw new RuntimeException("Estoque insuficiente. Disponível: {$this->available($stockable)}.");
        }

        return $this->apply($stockable, -$qty, 'out', $reason, $ref);
    }

    public function setAbsolute(Model $stockable, int $newValue, string $reason = 'ajuste manual', ?string $ref = null): ?StockMovement
    {
        $newValue = max(0, $newValue);
        $diff = $newValue - $stockable->stock;
        if ($diff === 0) return null;

        return $this->apply($stockable, $diff, 'adjust', $reason, $ref);
    }

    /* ============================================================
     * CARRINHO — reservas temporárias
     * ============================================================ */

    /**
     * Reserva N unidades por X minutos.
     * Lança exceção se não houver disponível.
     */
    public function reserve(
        Model $stockable,
        int $qty,
        ?string $sessionId = null,
        ?string $cartId = null
    ): StockReservation {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($stockable, $qty, $sessionId, $cartId) {
            // Bloqueia a linha para evitar race condition
            $fresh = $stockable->newQuery()->lockForUpdate()->find($stockable->getKey());

            if (! $fresh) {
                throw new RuntimeException('Item não encontrado para reserva.');
            }

            $available = $this->available($fresh);
            if ($available < $qty) {
                throw new RuntimeException("Só restam {$available} unidade(s) deste item.");
            }

            return StockReservation::create([
                'stockable_type' => get_class($fresh),
                'stockable_id'   => $fresh->getKey(),
                'user_id'        => Auth::id(),
                'session_id'     => $sessionId,
                'cart_id'        => $cartId,
                'quantity'       => $qty,
                'expires_at'     => now()->addMinutes(self::RESERVATION_MINUTES),
            ]);
        });
    }

    /**
     * Libera uma reserva individual (cliente removeu item do carrinho).
     */
    public function releaseReservation(StockReservation $reservation): void
    {
        if (! $reservation->committed_at) {
            $reservation->delete();
        }
    }

    /**
     * Libera TODAS as reservas de um carrinho.
     * Retorna a quantidade liberada.
     */
    public function releaseCart(string $cartId): int
    {
        return StockReservation::where('cart_id', $cartId)
            ->whereNull('committed_at')
            ->delete();
    }

    /**
     * Libera reservas expiradas (chamado por job agendado).
     */
    public function releaseExpired(): int
    {
        return StockReservation::expired()->delete();
    }

    /* ============================================================
     * CHECKOUT — comprometer reserva como venda
     * ============================================================ */

    /**
     * Converte uma reserva em decremento definitivo.
     */
    public function commitReservation(StockReservation $reservation, string $reference): StockMovement
    {
        return DB::transaction(function () use ($reservation, $reference) {
            // Recarrega bloqueando
            $reservation = StockReservation::lockForUpdate()->find($reservation->id);

            if (! $reservation) {
                throw new RuntimeException('Reserva não encontrada.');
            }

            if ($reservation->committed_at) {
                throw new RuntimeException('Esta reserva já foi convertida.');
            }

            $stockable = $reservation->stockable()->lockForUpdate()->first();
            if (! $stockable) {
                throw new RuntimeException('Item da reserva não existe mais.');
            }

            if ($stockable->stock < $reservation->quantity) {
                throw new RuntimeException('Estoque insuficiente no momento do fechamento.');
            }

            $movement = $this->apply(
                $stockable,
                -$reservation->quantity,
                'out',
                'venda',
                $reference
            );

            $reservation->update(['committed_at' => now()]);

            return $movement;
        });
    }

    /**
     * Compromete TODAS as reservas de um carrinho de uma vez.
     * Retorna a quantidade de reservas convertidas.
     */
    public function commitCart(string $cartId, string $reference): int
    {
        $count = 0;

        $reservations = StockReservation::where('cart_id', $cartId)
            ->whereNull('committed_at')
            ->active()
            ->get();

        foreach ($reservations as $r) {
            $this->commitReservation($r, $reference);
            $count++;
        }

        return $count;
    }

    /* ============================================================
     * AJUSTE EM MASSA
     * ============================================================ */

    public function bulkAdjustTargets(
        array $productIds,
        string $mode,
        int $value,
        string $target = 'auto',
        string $reason = 'ajuste em massa'
    ): array {
        $success   = 0;
        $failed    = [];
        $breakdown = ['products' => 0, 'variants' => 0, 'skipped' => 0];

        foreach ($productIds as $id) {
            $product = Product::with('variants.items')->find($id);
            if (! $product) {
                $failed[] = ['id' => $id, 'reason' => 'produto não encontrado'];
                continue;
            }

            $hasVariants = (bool) $product->has_variants;

            $shouldAdjustProduct = match ($target) {
                'products' => ! $hasVariants,
                'variants' => false,
                'both'     => ! $hasVariants,
                'auto'     => ! $hasVariants,
                default    => false,
            };

            $shouldAdjustVariants = match ($target) {
                'products' => false,
                'variants' => $hasVariants,
                'both'     => $hasVariants,
                'auto'     => $hasVariants,
                default    => false,
            };

            if ($target === 'products' && $hasVariants) {
                $breakdown['skipped']++;
                continue;
            }
            if ($target === 'variants' && ! $hasVariants) {
                $breakdown['skipped']++;
                continue;
            }

            // ---------- PRODUTO ----------
            if ($shouldAdjustProduct) {
                try {
                    $this->applyMode($product, $mode, $value, $reason);
                    $success++;
                    $breakdown['products']++;
                } catch (\Throwable $e) {
                    $failed[] = ['id' => "produto#{$id}", 'reason' => $e->getMessage()];
                }
            }

            // ---------- VARIANTES ----------
            if ($shouldAdjustVariants) {
                foreach ($product->variants as $variant) {
                    foreach ($variant->items as $item) {
                        try {
                            $this->applyMode($item, $mode, $value, $reason);
                            $success++;
                            $breakdown['variants']++;
                        } catch (\Throwable $e) {
                            $failed[] = [
                                'id'     => "variante#{$item->id} ({$item->value})",
                                'reason' => $e->getMessage(),
                            ];
                        }
                    }
                }
            }
        }

        $this->refreshProductCache($productIds);

        return ['success' => $success, 'failed' => $failed, 'breakdown' => $breakdown];
    }

    /* ============================================================
     * SINCRONIZAÇÃO / CACHE
     * ============================================================ */

    /**
     * Recalcula `total_stock` e sincroniza `status`. Chamado após qualquer mudança.
     */
    public function refreshProductCache(array $productIds = []): void
    {
        $query = Product::query();
        if (! empty($productIds)) {
            $query->whereIn('id', $productIds);
        }

        $query->chunk(200, function ($products) {
            foreach ($products as $p) {
                if ($p->has_variants) {
                    $total = (int) VariantItem::whereIn('variant_id', $p->variants()->pluck('id'))
                                              ->sum('stock');
                } else {
                    $total = (int) ($p->getAttributes()['stock'] ?? 0);
                }

                $status = $p->status;
                if ($total <= 0 && $status !== 'out_of_stock') {
                    $status = 'out_of_stock';
                } elseif ($total > 0 && $status === 'out_of_stock') {
                    $status = 'active';
                }

                $p->updateQuietly([
                    'total_stock' => $total,
                    'status'      => $status,
                ]);
            }
        });
    }

    public function syncProductStatus(Product $product): void
    {
        $this->refreshProductCache([$product->id]);
    }

    /* ============================================================
     * CORE — único ponto que escreve no banco
     * ============================================================ */

    private function apply(Model $stockable, int $delta, string $type, string $reason, ?string $reference): StockMovement
    {
        return DB::transaction(function () use ($stockable, $delta, $type, $reason, $reference) {
            $fresh = $stockable->newQuery()->lockForUpdate()->find($stockable->getKey());

            $before = (int) $fresh->stock;
            $after  = max(0, $before + $delta);

            $fresh->update(['stock' => $after]);
            $stockable->stock = $after;

            $movement = StockMovement::create([
                'stockable_type' => get_class($fresh),
                'stockable_id'   => $fresh->getKey(),
                'type'           => $type,
                'quantity'       => $delta,
                'stock_before'   => $before,
                'stock_after'    => $after,
                'reason'         => $reason,
                'user_id'        => Auth::id(),
                'reference'      => $reference,
            ]);

            // Atualiza cache do produto pai
            if ($fresh instanceof Product) {
                $this->refreshProductCache([$fresh->id]);
            } elseif ($fresh instanceof VariantItem) {
                $this->refreshProductCache([$fresh->variant->product_id]);
            }

            return $movement;
        });
    }

    private function applyMode(Model $stockable, string $mode, int $value, string $reason): void
    {
        match ($mode) {
            'increment' => $this->increment($stockable, $value, $reason),
            'decrement' => $this->decrement($stockable, $value, $reason),
            'set'       => $this->setAbsolute($stockable, $value, $reason),
            default     => throw new RuntimeException("Modo inválido: {$mode}"),
        };
    }

    private function assertPositive(int $qty): void
    {
        if ($qty <= 0) {
            throw new RuntimeException('Quantidade deve ser maior que zero.');
        }
    }
}