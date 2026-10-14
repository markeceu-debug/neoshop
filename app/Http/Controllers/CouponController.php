<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {}

    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $cart = $this->cart->resolveCart();

        try {
            $result = $this->coupons->applyToCart($data['code'], $cart);

            return response()->json([
                'status'   => 'success',
                'message'  => 'Cupom aplicado!',
                'coupon'   => [
                    'code'   => $result['coupon']->code,
                    'type'   => $result['coupon']->type,
                    'value'  => (float) $result['coupon']->value,
                ],
                'discount' => number_format($result['discount'], 2, ',', '.'),
                'total'    => number_format($cart->fresh()->total, 2, ',', '.'),
                'subtotal' => number_format($cart->fresh()->subtotal, 2, ',', '.'),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function remove(): JsonResponse
    {
        $cart = $this->cart->resolveCart();
        $this->coupons->removeFromCart($cart);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Cupom removido.',
            'total'    => number_format($cart->fresh()->total, 2, ',', '.'),
            'subtotal' => number_format($cart->fresh()->subtotal, 2, ',', '.'),
        ]);
    }
}