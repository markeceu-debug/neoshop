<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Página inicial de rastreio.
     */
    public function index(Request $request): View
    {
        $order = null;
        $searched = false;

        if ($request->filled('q')) {
            $searched = true;
            $order = $this->findOrder($request->input('q'));

            if ($order) {
                $order->load(['items', 'trackingEvents']);
            }
        }

        return view('shop.tracking.index', compact('order', 'searched'));
    }

    /**
     * Detalhe de um pedido pelo número (link direto).
     */
    public function show(string $number): View
    {
        $order = Order::where('number', $number)
            ->with(['items', 'trackingEvents'])
            ->firstOrFail();

        return view('shop.tracking.show', compact('order'));
    }

    /**
     * Busca pedido por número ou tracking code.
     */
    private function findOrder(string $query): ?Order
    {
        $query = trim($query);

        return Order::where('number', $query)
            ->orWhere('tracking_code', $query)
            ->with(['items', 'trackingEvents'])
            ->first();
    }
}