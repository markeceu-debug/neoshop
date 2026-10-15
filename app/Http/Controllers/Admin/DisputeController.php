<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\DisputesDataTable;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Models\Setting;
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
            'open'         => Dispute::where('status', 'open')->count(),
            'under_review' => Dispute::where('status', 'under_review')->count(),
            'resolved'     => Dispute::whereIn('status', [
                'resolved_buyer',
                'resolved_seller',
                'resolved_partial',
            ])->count(),
            'total' => Dispute::count(),
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
            'message'       => ['required', 'string', 'min:2', 'max:2000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
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

        try {
            DB::transaction(function () use ($dispute, $data) {
                $order = $dispute->order;

                // O vendedor perde o valor que estava pendente
                if ($order->seller_id) {
                    $this->refundPlatformCommission($order, (float) $order->total);

                    $this->revertSellerFunds(
                        $order,
                        'Reembolso ao cliente — disputa #' . $dispute->id
                    );
                }


                // Recalcula seller_net_amount: vendedor fica com o negativo
                // da comissão (que já foi retida pela plataforma)
                $order->update([
                    'status'            => 'refunded',
                    'refunded_at'       => now(),
                    'refund_amount'     => $order->total,
                    'auto_release_at'   => null,
                    'seller_net_amount' => -$order->commission_amount,
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
                    'message'     => '✅ Reclamação resolvida a favor do cliente. Valor de R$ '
                        . number_format($order->total, 2, ',', '.')
                        . ' será reembolsado. Obrigado pela paciência!',
                ]);
            });
        } catch (RuntimeException $e) {
            // Bloqueia a wallet FORA da transação (persiste mesmo com rollback)
            $this->blockSellerWallet($dispute->order, $e->getMessage());

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida a favor do cliente.');
    }
    /**
     * Estorna a comissão da plataforma proporcional ao reembolso.
     *
     * Exemplo:
     *   Pedido R$ 200, comissão R$ 20, reembolso R$ 100
     *   Estorno = 20 * (100 / 200) = R$ 10
     */
    private function refundPlatformCommission(Order $order, float $refundAmount): void
    {
        $commission = (float) $order->commission_amount;

        if ($commission <= 0 || $refundAmount <= 0) {
            return;
        }

        $orderTotal = (float) $order->total;

        if ($orderTotal <= 0) {
            return;
        }

        // Proporção do estorno
        $ratio = min(1, $refundAmount / $orderTotal);
        $refundCommission = round($commission * $ratio, 2);

        if ($refundCommission <= 0) {
            return;
        }

        $platformWallet = Wallet::forPlatform();
        $platformWallet->debitCommission(
            amount: $refundCommission,
            orderId: $order->id,
            description: sprintf(
                'Estorno de comissão — disputa #%d (pedido %s, reembolso R$ %s)',
                $order->id,
                $order->number,
                number_format($refundAmount, 2, ',', '.')
            )
        );
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
            $order         = $dispute->order;
            $guaranteeDays = (int) Setting::get('guarantee_days', 7);

            // Zera resíduos de refund anteriores (ex.: disputa parcial revertida)
            $order->update([
                'status'          => 'delivered',
                'refund_amount'   => 0,
                'refunded_at'     => null,
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
                'message'     => '✅ Reclamação analisada e resolvida. O pagamento ao vendedor será liberado no prazo normal.',
            ]);
        });

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida a favor do vendedor.');
    }

    /**
     * Resolve PARCIAL (reembolso de parte do valor).
     *
     * Regra: reembolso parcial encerra a disputa e libera o restante ao
     * vendedor no prazo normal (garantia).
     */
    public function resolvePartial(Request $request, Dispute $dispute)
    {
        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'refund_amount'    => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $dispute->order->total,
            ],
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ], [
            'refund_amount.max' => 'O reembolso não pode ser maior que o valor do pedido.',
        ]);

        try {
            DB::transaction(function () use ($dispute, $data) {
                $order  = $dispute->order;
                $refund = (float) $data['refund_amount'];

                if ($order->seller_id) {
                    // ✅ Estorna comissão proporcional
                    $this->refundPlatformCommission($order, $refund);

                    $this->revertSellerFunds(
                        $order,
                        'Reembolso parcial — disputa #' . $dispute->id,
                        $refund
                    );
                }

                // Ajusta o valor líquido do vendedor
                $newNet = max(0, (float) $order->seller_net_amount - $refund);

                $order->update([
                    'status'            => 'partially_refunded',
                    'refund_amount'     => $refund,
                    'refunded_at'       => now(),
                    'seller_net_amount' => $newNet,
                    'auto_release_at'   => now()->addDays((int) Setting::get('guarantee_days', 7)),
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
                    'message'     => '✅ Reclamação resolvida com reembolso parcial de R$ '
                        . number_format($refund, 2, ',', '.')
                        . '. O restante será liberado ao vendedor no prazo normal.',
                ]);
            });
        } catch (RuntimeException $e) {
            $this->blockSellerWallet($dispute->order, $e->getMessage());

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('success', 'Reclamação resolvida com reembolso parcial.');
    }

    /* ============================================================
     * HELPERS PRIVADOS
     * ============================================================ */

    /**
     * Bloqueia a carteira do vendedor FORA da transação.
     * Chamado no catch de resolveBuyer/resolvePartial quando
     * revertSellerFunds detecta saldo insuficiente.
     */
    private function blockSellerWallet(Order $order, string $reason): void
    {
        if (! $order->seller_id) {
            return;
        }

        Wallet::forUser($order->seller_id)->update([
            'blocked'      => 1,
            'block_reason' => $reason,
        ]);
    }

    /**
     * Remove o valor do pending_balance do vendedor.
     * Se já foi liberado para o balance, retira de lá também.
     * Lança RuntimeException se não houver saldo suficiente —
     * abortando a transação inteira (o bloqueio da wallet é feito
     * no catch do controller, fora da transação).
     */
    private function revertSellerFunds(
        Order $order,
        string $description,
        ?float $customAmount = null
    ): void {
        $wallet = Wallet::forUser($order->seller_id);
        $amount = $customAmount ?? (float) $order->seller_net_amount;

        if ($amount <= 0) {
            return;
        }

        $pendingBefore = (float) $wallet->pending_balance;
        $balanceBefore = (float) $wallet->balance;

        // 1) Tenta retirar do pending primeiro
        $fromPending = min($amount, $pendingBefore);
        $remaining   = $amount - $fromPending;

        // 2) O restante sai do balance (caso já tenha sido liberado)
        $fromBalance = min($remaining, $balanceBefore);

        // 3) Quanto ficou faltando? (vendedor sem saldo suficiente)
        $shortfall = round($remaining - $fromBalance, 2);

        if ($shortfall > 0.01) {
            // Só lança — o bloqueio da wallet é feito FORA da transação
            // (no catch do resolveBuyer/resolvePartial) para persistir
            throw new RuntimeException(sprintf(
                'Vendedor #%d sem saldo para cobrir o reembolso. Faltam R$ %s.',
                $order->seller_id,
                number_format($shortfall, 2, ',', '.')
            ));
        }

        // 4) Debita efetivamente
        $wallet->update([
            'pending_balance' => $pendingBefore - $fromPending,
            'balance'         => $balanceBefore - $fromBalance,
            'total_earned'    => max(0, (float) $wallet->total_earned - $amount),
        ]);

        // 5) Log de auditoria com detalhamento
        WalletTransaction::create([
            'wallet_id'      => $wallet->id,
            'order_id'       => $order->id,
            'type'           => 'refund',
            'amount'         => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceBefore - $fromBalance,
            'description'    => sprintf(
                '%s (pending: -%s, balance: -%s)',
                $description,
                number_format($fromPending, 2, ',', '.'),
                number_format($fromBalance, 2, ',', '.')
            ),
            'created_by'     => Auth::id(),
        ]);
    }
}
