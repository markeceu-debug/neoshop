<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CouponService
{
    /**
     * Valida e calcula o desconto de um cupom para o carrinho.
     *
     * @return array{ coupon: Coupon, discount: float }
     * @throws RuntimeException
     */
    public function validate(string $code, Cart $cart): array
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new RuntimeException('Cupom não encontrado.');
        }

        if (! $coupon->isActive()) {
            throw new RuntimeException('Este cupom não está mais ativo.');
        }

        // ---------- Valor mínimo ----------
        if ($cart->subtotal < $coupon->min_purchase) {
            throw new RuntimeException(sprintf(
                'Este cupom exige uma compra mínima de R$ %s.',
                number_format($coupon->min_purchase, 2, ',', '.')
            ));
        }

        // ---------- Limite por usuário ----------
        if ($coupon->per_user_limit !== null) {
            $used = $coupon->getUserUsageCount(Auth::id(), Session::getId());
            if ($used >= $coupon->per_user_limit) {
                throw new RuntimeException('Você já utilizou este cupom o número máximo de vezes.');
            }
        }

        // ---------- Produtos aplicáveis ----------
        $discount = $this->calculateDiscount($coupon, $cart);

        if ($discount <= 0) {
            throw new RuntimeException('Este cupom não se aplica aos itens do seu carrinho.');
        }

        return ['coupon' => $coupon, 'discount' => $discount];
    }

    /**
     * Calcula o desconto efetivo do cupom para o carrinho.
     */
    public function calculateDiscount(Coupon $coupon, Cart $cart): float
    {
        // ---------- Frete grátis ----------
        if ($coupon->type === 'free_shipping') {
            return (float) $cart->shipping;
        }

        // ---------- Base de cálculo ----------
        $base = $this->getEligibleSubtotal($coupon, $cart);

        if ($base <= 0) return 0;

        // ---------- Percentual ----------
        if ($coupon->type === 'percent') {
            $discount = $base * ($coupon->value / 100);

            if ($coupon->max_discount !== null) {
                $discount = min($discount, (float) $coupon->max_discount);
            }

            return round($discount, 2);
        }

        // ---------- Valor fixo ----------
        if ($coupon->type === 'fixed') {
            return min((float) $coupon->value, $base);
        }

        return 0;
    }

    /**
     * Soma dos itens elegíveis (produtos/categorias específicos).
     */
    private function getEligibleSubtotal(Coupon $coupon, Cart $cart): float
    {
        $items = $cart->items()->with(['product'])->get();

        if ($coupon->applies_to === 'all') {
            return (float) $items->sum('subtotal');
        }

        $productIds  = $coupon->product_ids ?? [];
        $categoryIds = $coupon->category_ids ?? [];

        return (float) $items
            ->filter(function ($item) use ($coupon, $productIds, $categoryIds) {
                if (! $item->product) return false;

                if ($coupon->applies_to === 'products') {
                    return in_array($item->product_id, $productIds);
                }

                if ($coupon->applies_to === 'categories') {
                    return in_array($item->product->category_id, $categoryIds);
                }

                return false;
            })
            ->sum('subtotal');
    }

    /**
     * Aplica o cupom ao carrinho (grava o código e o desconto).
     */
    public function applyToCart(string $code, Cart $cart): array
    {
        $result = $this->validate($code, $cart);

        $cart->update([
            'coupon_code'    => $result['coupon']->code,
            'discount'       => $result['discount'],
            'total'          => $cart->subtotal - $result['discount'] + $cart->shipping,
        ]);

        return $result;
    }

    /**
     * Remove o cupom do carrinho.
     */
    public function removeFromCart(Cart $cart): void
    {
        $cart->update([
            'coupon_code' => null,
            'discount'    => 0,
            'total'       => $cart->subtotal + $cart->shipping,
        ]);
    }

    /**
     * Registra o uso do cupom quando o pedido é finalizado.
     */
    public function redeem(Coupon $coupon, ?int $orderId, float $discount): CouponRedemption
    {
        $coupon->increment('used_count');

        return CouponRedemption::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => Auth::id(),
            'session_id'      => Session::getId(),
            'order_id'        => $orderId,
            'discount_amount' => $discount,
        ]);
    }
}