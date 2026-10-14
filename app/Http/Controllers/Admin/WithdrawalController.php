<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\WithdrawalsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(WithdrawalsDataTable $dataTable)
    {
        $stats = [
            'pending'  => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::where('status', 'approved')->count(),
            'paid'     => Withdrawal::where('status', 'paid')->count(),
            'total_paid' => Withdrawal::where('status', 'paid')->sum('amount'),
        ];

        return $dataTable->render('admin.withdrawals.index', compact('stats'));
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['user', 'reviewer']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Aprova o saque (aprovação interna, ainda não pagou).
     */
    public function approve(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Este saque não está pendente.');
        }

        $withdrawal->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Saque aprovado! Agora realize o pagamento.');
    }

    /**
     * Marca como PAGO (já transferiu o dinheiro).
     */
    public function markPaid(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100'],
        ]);

        if (! in_array($withdrawal->status, ['pending', 'approved'])) {
            return back()->with('error', 'Este saque não pode ser marcado como pago.');
        }

        DB::transaction(function () use ($withdrawal, $data) {
            $wallet = Wallet::forUser($withdrawal->user_id);

            $amount = (float) $withdrawal->amount;
            $holdBefore = (float) $wallet->withdrawal_hold;

            $wallet->update([
                'withdrawal_hold' => max(0, $holdBefore - $amount),
                'total_withdrawn' => (float) $wallet->total_withdrawn + $amount,
            ]);

            $withdrawal->update([
                'status'            => 'paid',
                'paid_at'           => now(),
                'reviewed_by'       => Auth::id(),
                'reviewed_at'       => $withdrawal->reviewed_at ?? now(),
                'payment_reference' => $data['payment_reference'],
            ]);

            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'withdrawal_id'  => $withdrawal->id,
                'type'           => 'withdrawal_paid',
                'amount'         => -$amount,
                'balance_before' => (float) $wallet->balance,
                'balance_after'  => (float) $wallet->balance,
                'description'    => "Saque pago · Ref: {$data['payment_reference']}",
            ]);
        });

        return back()->with('success', 'Saque marcado como pago!');
    }

    /**
     * Rejeita o saque (devolve o valor pro balance).
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($withdrawal->status === 'paid') {
            return back()->with('error', 'Não é possível rejeitar um saque já pago.');
        }

        DB::transaction(function () use ($withdrawal, $data) {
            $wallet = Wallet::forUser($withdrawal->user_id);

            $amount = (float) $withdrawal->amount;
            $holdBefore = (float) $wallet->withdrawal_hold;
            $balanceBefore = (float) $wallet->balance;

            $wallet->update([
                'withdrawal_hold' => max(0, $holdBefore - $amount),
                'balance'         => $balanceBefore + $amount,
            ]);

            $withdrawal->update([
                'status'           => 'rejected',
                'reviewed_by'      => Auth::id(),
                'reviewed_at'      => now(),
                'rejection_reason' => $data['reason'],
            ]);

            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'withdrawal_id'  => $withdrawal->id,
                'type'           => 'adjustment',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceBefore + $amount,
                'description'    => "Saque rejeitado: {$data['reason']}",
                'created_by'     => Auth::id(),
            ]);
        });

        return back()->with('success', 'Saque rejeitado e valor devolvido.');
    }
}