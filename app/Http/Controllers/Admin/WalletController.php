<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::forPlatform();

        $stats = [
            'balance'         => (float) $wallet->balance,
            'total_earned'    => (float) $wallet->total_earned,
            'total_withdrawn' => (float) $wallet->total_withdrawn,
            'commissions'     => WalletTransaction::where('wallet_id', $wallet->id)
                ->where('type', 'commission')
                ->count(),
        ];

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->paginate(30);

        return view('admin.wallet.index', compact('wallet', 'stats', 'transactions'));
    }
}