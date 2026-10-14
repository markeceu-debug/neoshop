<?php

namespace App\Services;

use App\Models\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use App\Models\VariantItem;
use App\Notifications\LowStockNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class LowStockService
{
    public const THRESHOLD = 10;

    /**
     * Verifica TODOS os produtos + itens de variante e dispara notificações
     * somente para quem cruzou o limite E ainda não foi avisado.
     */
public function checkAndNotify(): array
{
    $notified = 0;
    $cleaned  = 0;

    // ---------- PRODUTOS ----------
    $productQuery = Product::query();

    if (method_exists(Product::class, 'user')) {
        $productQuery->with('user');
    }

    $productQuery->chunk(200, function ($products) use (&$notified, &$cleaned) {
        foreach ($products as $product) {
            $vendor = method_exists($product, 'user') ? $product->user : null;
            [$n, $c] = $this->handleOne($product, $vendor);
            $notified += $n;
            $cleaned  += $c;
        }
    });

    // ---------- ITENS DE VARIANTE ----------
    $itemQuery = VariantItem::query();

    if (method_exists(VariantItem::class, 'variant')) {
        $itemQuery->with('variant.product');
    }

    $itemQuery->chunk(200, function ($items) use (&$notified, &$cleaned) {
        foreach ($items as $item) {
            $vendor = null;

            if (method_exists($item, 'variant')
                && $item->variant
                && $item->variant->product
                && method_exists($item->variant->product, 'user')
            ) {
                $vendor = $item->variant->product->user;
            }

            [$n, $c] = $this->handleOne($item, $vendor);
            $notified += $n;
            $cleaned  += $c;
        }
    });

    return ['notified' => $notified, 'cleaned' => $cleaned];
}
    /**
     * Trata um stockable (Product ou VariantItem).
     * Retorna [notificados, limpos].
     */
    private function handleOne(Model $stockable, ?User $vendor): array
    {
        $alert = LowStockAlert::where('stockable_type', get_class($stockable))
            ->where('stockable_id', $stockable->id)
            ->first();

        $lowStock = $stockable->stock < self::THRESHOLD;

        // -------- ESTOQUE OK: limpa o alerta anterior --------
        if (! $lowStock) {
            if ($alert) {
                $alert->delete();
                return [0, 1];
            }
            return [0, 0];
        }

        // -------- ESTOQUE BAIXO: só notifica se ainda não avisou nesta rodada --------
        if ($alert) {
            // Já avisamos e o estoque não mudou → não repete
            if ($alert->last_stock === $stockable->stock) {
                return [0, 0];
            }

            // Estoque mudou (aumentou ou diminuiu) mas continua abaixo do limite
            // → atualiza o snapshot e re-notifica (útil quando piorou)
            $alert->update([
                'last_stock'  => $stockable->stock,
                'notified_at' => now(),
            ]);
        } else {
            // Primeira vez que cruzou o limite
            LowStockAlert::create([
                'stockable_type' => get_class($stockable),
                'stockable_id'   => $stockable->id,
                'last_stock'     => $stockable->stock,
                'notified_at'    => now(),
            ]);
        }

        // ---------- DISPARA NOTIFICAÇÃO ----------
        $notification = new LowStockNotification($stockable, self::THRESHOLD);

        // Vendor (dono do produto)
        if ($vendor) {
            $vendor->notify($notification);
        }

        // Todos os admins
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }

        return [1, 0];
    }


}