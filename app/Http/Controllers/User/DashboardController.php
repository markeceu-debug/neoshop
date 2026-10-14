<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $stats = [
            'total_orders'    => Order::where('user_id', $userId)->count(),
            'pending_payment' => Order::where('user_id', $userId)->whereIn('status', ['awaiting_payment', 'payment_under_review'])->count(),
            'shipped'         => Order::where('user_id', $userId)->where('status', 'shipped')->count(),
            'completed'       => Order::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        $recentOrders = Order::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recentOrders'));
    }
}