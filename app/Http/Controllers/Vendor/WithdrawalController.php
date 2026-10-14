<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WithdrawalController extends Controller
{
    /**
     * Lista de saques do vendedor.
     */
    public function index()
    {
        $user   = Auth::user();
        $wallet = Wallet::forUser($user->id);

        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'available'  => (float) $wallet->balance,
            'pending'    => (float) $wallet->pending_balance,
            'hold'       => (float) $wallet->withdrawal_hold,
            'total_paid' => (float) $wallet->total_withdrawn,
        ];

        return view('vendedor.withdrawals.index', compact('wallet', 'withdrawals', 'stats'));
    }

    /**
     * Formulário de novo saque.
     */
    public function create()
    {
        $user   = Auth::user();
        $wallet = Wallet::forUser($user->id);

        $minWithdrawal = (float) Setting::get('min_withdrawal', 5000);

        if ($wallet->balance < $minWithdrawal) {
            return redirect()
                ->route('vendor.withdrawals.index')
                ->with('error', 'Você precisa de pelo menos R$ ' . number_format($minWithdrawal, 2, ',', '.') . ' para solicitar um saque.');
        }

        // Não pode ter saque pendente
        $hasPending = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasPending) {
            return redirect()
                ->route('vendor.withdrawals.index')
                ->with('error', 'Você já tem um saque em andamento. Aguarde a análise.');
        }

        return view('vendedor.withdrawals.create', compact('wallet', 'minWithdrawal'));
    }

    /**
     * Salva a solicitação de saque.
     */
    public function store(Request $request)
    {
        $user   = Auth::user();
        $wallet = Wallet::forUser($user->id);

        $minWithdrawal = (float) Setting::get('min_withdrawal', 5000);

        $data = $request->validate([
            'amount'             => ['required', 'numeric', 'min:' . $minWithdrawal],
            'method'             => ['required', 'in:bank_transfer,multicaixa_express'],
            'bank_holder'        => ['required', 'string', 'max:150'],
            'bank_name'          => ['required', 'string', 'max:100'],
            'bank_iban'          => ['required', 'string', 'max:34'],
            'bank_account'       => ['required', 'string', 'max:30'],
            'bank_express'       => ['nullable', 'string', 'max:20'],
        ], [
            'amount.min' => 'O valor mínimo para saque é R$ ' . number_format($minWithdrawal, 2, ',', '.'),
        ]);

        // Bloqueios
        if ($wallet->blocked) {
            return back()->with('error', 'Sua carteira está bloqueada. Fale com o suporte.');
        }

        if ($wallet->balance < $data['amount']) {
            return back()->with('error', 'Saldo insuficiente.');
        }

        $hasPending = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Você já tem um saque em andamento.');
        }

        DB::transaction(function () use ($user, $wallet, $data) {
            $amount = (float) $data['amount'];
            $before = (float) $wallet->balance;
            $after  = $before - $amount;

            // Cria o saque
            $withdrawal = Withdrawal::create([
                'user_id'    => $user->id,
                'amount'     => $amount,
                'commission' => 0,
                'net_amount' => $amount,
                'method'     => $data['method'],
                'bank_data'  => [
                    'holder'   => $data['bank_holder'],
                    'bank'     => $data['bank_name'],
                    'iban'     => $data['bank_iban'],
                    'account'  => $data['bank_account'],
                    'express'  => $data['bank_express'] ?? null,
                ],
                'status'     => 'pending',
            ]);

            // Move o dinheiro: balance → hold
            $wallet->update([
                'balance'         => $after,
                'withdrawal_hold' => (float) $wallet->withdrawal_hold + $amount,
            ]);

            // Ledger
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'withdrawal_id'  => $withdrawal->id,
                'type'           => 'withdrawal',
                'amount'         => -$amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => 'Saque solicitado',
            ]);
        });

        return redirect()
            ->route('vendor.withdrawals.index')
            ->with('success', 'Saque solicitado! Aguarde a análise.');
    }
}