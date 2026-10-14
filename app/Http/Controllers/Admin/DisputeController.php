<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\DisputesDataTable;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisputeController extends Controller
{
    public function index(DisputesDataTable $dataTable)
    {
        $stats = [
            'open'        => Dispute::where('status', 'open')->count(),
            'under_review'=> Dispute::where('status', 'under_review')->count(),
            'resolved'    => Dispute::whereIn('status', ['resolved_buyer', 'resolved_seller', 'resolved_partial'])->count(),
            'total'       => Dispute::count(),
        ];

        return $dataTable->render('admin.disputes.index', compact('stats'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.items',
            'order.user',
            'order.seller',
            'messages.sender',
            'opener',
            'resolver',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Admin envia mensagem no chat.
     */
    public function message(Request $request, Dispute $dispute)
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'message'     => ['required', 'string', 'min:2', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('disputes/' . $dispute->order_id, 'public');
            }
        }

        DisputeMessage::create([
            'dispute_id'  => $dispute->id,
            'sender_id'   => Auth::id(),
            'sender_role' => 'admin',
            'message'     => $data['message'],
            'attachments' => $attachments,
        ]);

        // Se estava "open", passa para "under_review"
        if ($dispute->status === 'open') {
            $dispute->update(['status' => 'under_review']);
        }

        return back()->with('success', 'Mensagem enviada.');
    }

    /**
     * Resolve a favor do CLIENTE (reembolso total).
     */
    public function resolveBuyer(Request $request, Dispute $dispute)
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($dispute, $data) {
            $order = $dispute->order;

            // O vendedor perde o valor que estava pendente
            if ($order->seller_id) {
                $this->revertSellerFunds($order, 'Reembolso ao cliente — disputa #' . $dispute->id);
            }

            $order->update([
                'status'        => 'refunded',
                'refunded_at'   => now(),
                'refund_amount' => $order->total,
                'auto_release_at' => null,
            ]);

            $dispute->update([
                'status'           => 'resolved_buyer',
                'resolution_notes' => $data['resolution_notes'],
                'refund_amount'    => $order->total,
                'resolved_by'      => Auth::id(),
                'resolved_at'      => now(),
            ]);

            DisputeMessage::create([
                'dispute_id'  => $dispute->id,
                'sender_id'   => Auth::id(),
                'sender_role' => 'admin',
                'message'     => "✅ Reclamação resolvida a favor do cliente. Valor de R$ "
                    . number_format($order->total, 2, ',', '.')
                    . " será reembolsado. Obrigado pela paciência!",
            ]);
        });

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida a favor do cliente.');
    }

    /**
     * Resolve a favor do VENDEDOR (libera o pagamento).
     */
    public function resolveSeller(Request $request, Dispute $dispute)
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($dispute, $data) {
            $order = $dispute->order;
            $guaranteeDays = (int) \App\Models\Setting::get('guarantee_days', 7);

            // Volta o pedido para "delivered" e agenda a liberação
            $order->update([
                'status'          => 'delivered',
                'auto_release_at' => now()->addDays($guaranteeDays),
            ]);

            $dispute->update([
                'status'           => 'resolved_seller',
                'resolution_notes' => $data['resolution_notes'],
                'refund_amount'    => 0,
                'resolved_by'      => Auth::id(),
                'resolved_at'      => now(),
            ]);

            DisputeMessage::create([
                'dispute_id'  => $dispute->id,
                'sender_id'   => Auth::id(),
                'sender_role' => 'admin',
                'message'     => "✅ Reclamação analisada e resolvida. O pagamento ao vendedor será liberado no prazo normal.",
            ]);
        });

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida a favor do vendedor.');
    }

    /**
     * Resolve PARCIAL (reembolso de parte do valor).
     */
    public function resolvePartial(Request $request, Dispute $dispute)
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'refund_amount'    => ['required', 'numeric', 'min:0.01', 'max:' . $dispute->order->total],
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ], [
            'refund_amount.max' => 'O reembolso não pode ser maior que o valor do pedido.',
        ]);

        DB::transaction(function () use ($dispute, $data) {
            $order  = $dispute->order;
            $refund = (float) $data['refund_amount'];

            // O vendedor perde a parte do reembolso
            if ($order->seller_id) {
                $this->revertSellerFunds($order, 'Reembolso parcial — disputa #' . $dispute->id, $refund);
            }

            // Ajusta o valor líquido do vendedor
            $newNet = max(0, (float) $order->seller_net_amount - $refund);

            $order->update([
                'status'          => 'delivered',       // volta pra liberação
                'refund_amount'   => $refund,
                'refunded_at'     => now(),
                'seller_net_amount' => $newNet,
                'auto_release_at' => now()->addDays((int) \App\Models\Setting::get('guarantee_days', 7)),
            ]);

            $dispute->update([
                'status'           => 'resolved_partial',
                'resolution_notes' => $data['resolution_notes'],
                'refund_amount'    => $refund,
                'resolved_by'      => Auth::id(),
                'resolved_at'      => now(),
            ]);

            DisputeMessage::create([
                'dispute_id'  => $dispute->id,
                'sender_id'   => Auth::id(),
                'sender_role' => 'admin',
                'message'     => "✅ Reclamação resolvida com reembolso parcial de R$ "
                    . number_format($refund, 2, ',', '.')
                    . ". O restante será liberado ao vendedor no prazo normal.",
            ]);
        });

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida com reembolso parcial.');
    }

    /* ============================================================
     * HELPER — Reverter fundos do vendedor
     * ============================================================ */

    /**
     * Remove o valor do pending_balance do vendedor.
     * Se já foi liberado para o balance, retira de lá também.
     */
    private function revertSellerFunds(Order $order, string $description, ?float $customAmount = null): void
    {
        $wallet = Wallet::forUser($order->seller_id);
        $amount = $customAmount ?? (float) $order->seller_net_amount;

        if ($amount <= 0) return;

        $pendingBefore = (float) $wallet->pending_balance;
        $balanceBefore = (float) $wallet->balance;

        // Primeiro tenta retirar do pending
        $fromPending = min($amount, $pendingBefore);
        $remaining   = $amount - $fromPending;

        // O resto sai do balance (se já foi liberado)
        $fromBalance = min($remaining, $balanceBefore);

        $wallet->update([
            'pending_balance' => $pendingBefore - $fromPending,
            'balance'         => $balanceBefore - $fromBalance,
            'total_earned'    => max(0, (float) $wallet->total_earned - $amount),
        ]);

        WalletTransaction::create([
            'wallet_id'      => $wallet->id,
            'order_id'       => $order->id,
            'type'           => 'refund',
            'amount'         => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceBefore - $fromBalance,
            'description'    => $description,
            'created_by'     => Auth::id(),
        ]);
    }
}