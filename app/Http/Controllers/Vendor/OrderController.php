<?php

namespace App\Http\Controllers\Vendor;

use App\DataTables\VendorOrdersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Lista de pedidos do vendedor logado.
     */
    public function index(VendorOrdersDataTable $dataTable)
    {
        $sellerId = Auth::id();

        $stats = [
            'total'     => Order::where('seller_id', $sellerId)->count(),
            'pending'   => Order::where('seller_id', $sellerId)->where('status', 'paid')->count(),
            'shipped'   => Order::where('seller_id', $sellerId)->where('status', 'shipped')->count(),
            'completed' => Order::where('seller_id', $sellerId)->where('status', 'completed')->count(),
        ];

        return $dataTable->render('vendedor.orders.index', compact('stats'));
    }

    /**
     * Detalhe do pedido.
     */
   public function show(Order $order)
{
    if ($order->seller_id !== Auth::id()) {
        abort(403);
    }

    $order->load(['items', 'user', 'paymentProofs']);

    return view('vendedor.orders.show', compact('order'));
}

/**
 * Marca o pedido como enviado.
 */
public function markShipped(Request $request, Order $order)
{
    // Segurança: só o vendedor dono
    if ($order->seller_id !== Auth::id()) {
        abort(403);
    }

    // Só pode marcar se estiver pago
    if ($order->status !== 'paid') {
        return back()->with('error', 'Só é possível marcar como enviado se o pagamento estiver aprovado.');
    }

    $data = $request->validate([
        'courier_name'  => ['required', 'string', 'max:100'],
        'courier_phone' => ['nullable', 'string', 'max:20'],
        'shipping_note' => ['nullable', 'string', 'max:500'],
    ], [
        'courier_name.required' => 'Informe o nome do motoboy.',
    ]);

    $order->update([
        'status'        => 'shipped',
        'shipped_at'    => now(),
        'courier_name'  => $data['courier_name'],
        'courier_phone' => $data['courier_phone'] ?? null,
        'shipping_note' => $data['shipping_note'] ?? null,
    ]);

    return redirect()
        ->route('vendor.orders.show', $order)
        ->with('success', 'Pedido marcado como enviado!');
}
}