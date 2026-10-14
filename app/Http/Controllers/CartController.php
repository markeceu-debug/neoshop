<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariantItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    /* ============================================================
     * PÁGINA COMPLETA /cart
     * ============================================================ */
public function index(): View
{
    $cart = $this->cart->resolveCart();
    $items = $cart->items()->with(['product.user', 'variantItem', 'reservation'])->get();

    // Detecta divergências
    $issues = $this->cart->revalidate($cart);

    // Detecta itens fora do município
    $customerMunicipality = $this->cart->customerMunicipality();
    $outOfZone = [];

    if ($customerMunicipality) {
        foreach ($items as $item) {
            $seller = $item->product?->user;
            if ($seller && $seller->municipality && $seller->municipality !== $customerMunicipality) {
                $outOfZone[] = [
                    'item_id' => $item->id,
                    'name'    => $item->display_name,
                    'seller'  => $seller->name,
                    'seller_municipality' => $seller->municipality,
                    'customer_municipality' => $customerMunicipality,
                ];
            }
        }
    }

    return view('shop.cart', [
        'cart'      => $cart,
        'items'     => $items,
        'issues'    => $issues,
        'outOfZone' => $outOfZone,
    ]);
}

    /* ============================================================
     * MINI-CART (partial para offcanvas)
     * ============================================================ */
    public function mini(): View
    {
        $cart  = $this->cart->resolveCart(false);
        $items = $cart
            ? $cart->items()->with(['product', 'variantItem', 'reservation'])->get()
            : collect();

        return view('shop.partials.mini-cart', [
            'cart'  => $cart,
            'items' => $items,
        ]);
    }

    /* ============================================================
     * ADICIONAR ITEM (AJAX)
     * ============================================================ */
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'      => ['required', 'integer', 'exists:products,id'],
            'variant_item_id' => ['nullable', 'integer', 'exists:variant_items,id'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $stockable = $data['variant_item_id']
            ? VariantItem::findOrFail($data['variant_item_id'])
            : $product;

        // Se o produto tem variantes e nenhuma foi escolhida → erro
        if (! $data['variant_item_id'] && $product->has_variants) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Escolha uma variante antes de adicionar.',
            ], 422);
        }

        try {
            $item = $this->cart->add($stockable, $data['quantity'], $product);

            return response()->json([
                'status'      => 'success',
                'message'     => 'Produto adicionado ao carrinho!',
                'cart_count'  => $this->cart->count(),
                'cart_total'  => number_format($this->cart->subtotal(), 2, ',', '.'),
                'item'        => [
                    'id'             => $item->id,
                    'display_name'   => $item->display_name,
                    'quantity'       => $item->quantity,
                    'subtotal'       => number_format($item->subtotal, 2, ',', '.'),
                    'unit_price'     => number_format($item->unit_price, 2, ',', '.'),
                    'image_url'      => $item->image_url,
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ============================================================
     * ALTERAR QUANTIDADE (AJAX)
     * ============================================================ */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            if ($data['quantity'] === 0) {
                $this->cart->removeItem($item);

                return response()->json([
                    'status'     => 'success',
                    'message'    => 'Item removido.',
                    'cart_count' => $this->cart->count(),
                    'cart_total' => number_format($this->cart->subtotal(), 2, ',', '.'),
                    'removed'    => true,
                ]);
            }

            $item = $this->cart->updateQty($item, $data['quantity']);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Quantidade atualizada.',
                'cart_count' => $this->cart->count(),
                'cart_total' => number_format($this->cart->subtotal(), 2, ',', '.'),
                'item'       => [
                    'id'       => $item->id,
                    'quantity' => $item->quantity,
                    'subtotal' => number_format($item->subtotal, 2, ',', '.'),
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ============================================================
     * REMOVER ITEM (AJAX)
     * ============================================================ */
    public function remove(CartItem $item): JsonResponse
    {
        try {
            $this->cart->removeItem($item);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Item removido do carrinho.',
                'cart_count' => $this->cart->count(),
                'cart_total' => number_format($this->cart->subtotal(), 2, ',', '.'),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
public function sync(): JsonResponse
{
    $cart = $this->cart->resolveCart();
    $changed = $this->cart->syncPricesAndStock($cart);

    return response()->json([
        'status'     => 'success',
        'changed'    => $changed,
        'cart_count' => $this->cart->count(),
        'cart_total' => number_format($this->cart->subtotal(), 2, ',', '.'),
    ]);
}
    /* ============================================================
     * LIMPAR CARRINHO (AJAX)
     * ============================================================ */
    public function clear(): JsonResponse
    {
        $this->cart->clear();

        return response()->json([
            'status'     => 'success',
            'message'    => 'Carrinho esvaziado.',
            'cart_count' => 0,
            'cart_total' => '0,00',
        ]);
    }
}