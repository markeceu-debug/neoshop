<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\VariantItem;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private StockService $stock,
        private CouponService $coupons,
        private OrderTrackingService $tracking,
    ) {}

    /**
     * Processa o checkout do carrinho atual.
     * Cria o pedido com status "awaiting_payment" e devolve o Order.
     */
    public function process(Cart $cart, array $data): Order
    {
        // ---------- 1. Revalidar ----------
        $issues = $this->cart->revalidate($cart);

        if (! empty($issues)) {
            $messages = collect($issues)->pluck('message')->join(' ');
            throw new RuntimeException('Não foi possível finalizar: ' . $messages);
        }

        if ($cart->items()->count() === 0) {
            throw new RuntimeException('Seu carrinho está vazio.');
        }
        // ---------- 2. Validar município dos vendedores ----------
        $this->validateCartMunicipality($cart, $data);
        return DB::transaction(function () use ($cart, $data) {

            // ---------- 2. Descobrir o vendedor ----------
            $sellerId = $this->resolveSellerId($cart);

            // ---------- 3. Comissão ----------
            $commissionRate   = (float) Setting::get('commission_rate', 10);
            $commissionMin    = (float) Setting::get('commission_min', 200);

            $commissionAmount = max(
                round((float) $cart->subtotal * ($commissionRate / 100), 2),
                $commissionMin
            );

            $sellerNetAmount  = round((float) $cart->total - $commissionAmount, 2);

            // ---------- 4. Criar Order ----------
            $order = Order::create([
                'number'             => Order::generateNumber(),
                'user_id'            => Auth::id(),
                'seller_id'          => $sellerId,
                'status'             => 'awaiting_payment',       // ← MUDOU
                'subtotal'           => $cart->subtotal,
                'discount'           => $cart->discount,
                'shipping'           => $cart->shipping,
                'total'              => $cart->total,
                'commission_rate'    => $commissionRate,
                'commission_amount'  => $commissionAmount,
                'seller_net_amount'  => $sellerNetAmount,
                'customer_name'      => $data['name'],
                'customer_email'     => $data['email'],
                'customer_phone'     => $data['phone'] ?? null,
                'shipping_address'   => $data['shipping_address'],
                'billing_address'    => $data['billing_address'] ?? $data['shipping_address'],
                'payment_method'     => $data['payment_method'],
                'payment_status'     => 'pending',
                'shipping_method'    => $data['shipping_method'] ?? 'free',
                'notes'              => $data['notes'] ?? null,
            ]);

            $this->tracking->log(
                $order,
                'awaiting_payment',
                location: 'Aguardando comprovativo'
            );

            // ---------- 5. Criar OrderItems ----------
            foreach ($cart->items()->with(['product', 'variantItem'])->get() as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variantItem;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product?->id,
                    'variant_item_id' => $variant?->id,
                    'product_name'    => $product?->name ?? 'Produto removido',
                    'variant_value'   => $variant?->value,
                    'product_image'   => $cartItem->image_url,
                    'product_sku'     => $product?->sku,
                    'unit_price'      => $cartItem->unit_price,
                    'quantity'        => $cartItem->quantity,
                    'subtotal'        => $cartItem->subtotal,
                ]);
            }

            // ---------- 6. Commit do estoque (real) ----------
            // O estoque é decrementado agora, para reservar as unidades.
            // Se o pagamento for rejeitado, devolvemos no cancelamento.
            $this->stock->commitCart((string) $cart->id, $order->number);

            // ---------- 7. Registrar uso do cupom ----------
            if ($cart->coupon_code) {
                $coupon = \App\Models\Coupon::where('code', $cart->coupon_code)->first();
                if ($coupon) {
                    $this->coupons->redeem($coupon, $order->id, (float) $cart->discount);
                }
            }

            // ---------- 8. Marcar cart como convertido ----------
            $cart->update([
                'status'       => 'converted',
                'converted_at' => now(),
            ]);

            return $order->fresh(['items']);
        });
    }
    /**
     * Revalida que todos os vendedores entregam no município do cliente.
     */
    private function validateCartMunicipality(Cart $cart, array $data): void
    {
        $customerMunicipality = $data['shipping_address']['municipality'] ?? null;

        if (! $customerMunicipality) {
            throw new RuntimeException('Escolha o município de entrega.');
        }

        // Vendedores únicos no carrinho
        $sellers = $cart->items()
            ->with('product.user')
            ->get()
            ->pluck('product.user')
            ->filter()
            ->unique('id');

        if ($sellers->count() > 1) {
            throw new RuntimeException(
                'Seu carrinho tem produtos de vendedores diferentes. ' .
                    'Só é possível finalizar com produtos do mesmo vendedor.'
            );
        }

        foreach ($sellers as $seller) {
            if ($seller->municipality && $seller->municipality !== $customerMunicipality) {
                throw new RuntimeException(
                    "O vendedor {$seller->name} não entrega em {$customerMunicipality}. " .
                        "Ele vende apenas em {$seller->municipality}."
                );
            }
        }
    }

    /**
     * Descobre o vendedor do pedido:
     * - Se todos os itens forem do mesmo vendedor → usa esse
     * - Se forem de vendedores diferentes → usa o primeiro (ou null)
     * - Se ninguém for vendedor → null (a plataforma é a vendedora)
     */
    private function resolveSellerId(Cart $cart): ?int
    {
        $sellerIds = $cart->items()
            ->with('product')
            ->get()
            ->pluck('product.user_id')
            ->filter()
            ->unique();

        if ($sellerIds->count() === 1) {
            return (int) $sellerIds->first();
        }

        return null;
    }
}
