<?php

namespace App\Http\Controllers\User;

use App\DataTables\UserOrdersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    public function index(UserOrdersDataTable $dataTable)
    {
        $userId = Auth::id();

        $stats = [
            'total'     => Order::where('user_id', $userId)->count(),
            'pending'   => Order::where('user_id', $userId)->whereIn('status', ['awaiting_payment', 'payment_under_review'])->count(),
            'shipped'   => Order::where('user_id', $userId)->where('status', 'shipped')->count(),
            'completed' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        return $dataTable->render('user.orders.index', compact('stats'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items', 'seller', 'paymentProofs']);

        return view('user.orders.show', compact('order'));
    }


 
public function markDelivered(Order $order)
{
    // Segurança
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    // Só pode confirmar se foi enviado
    if ($order->status !== 'shipped') {
        return back()->with('error', 'Este pedido ainda não foi enviado.');
    }

    DB::transaction(function () use ($order) {
        $guaranteeDays = (int) \App\Models\Setting::get('guarantee_days', 7);

        $order->update([
            'status'          => 'delivered',
            'delivered_at'    => now(),
            'auto_release_at' => now()->addDays($guaranteeDays),
        ]);
    });

    return redirect()
        ->route('user.orders.show', $order)
        ->with('success', 'Recebimento confirmado! Obrigado pela compra.');
}
}