<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrdersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\OrderTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Injeta o OrderTrackingService no construtor.
     * Sem isso, $this->tracking é null e tudo quebra.
     */
    public function __construct(
        private OrderTrackingService $tracking
    ) {}

    public function index(Request $request, OrdersDataTable $dataTable)
    {
        return $dataTable->render('admin.orders.index', [
            'currentStatus' => $request->input('status'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'items',
            'user',
            'seller',
            'paymentProofs.uploader',
            'paymentProofs.reviewer',
            'disputes',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /* ============================================================
     * APROVAR PAGAMENTO
     * ============================================================ */
    public function approvePayment(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'proof_id' => ['required', 'exists:payment_proofs,id'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        if ($order->status !== 'payment_under_review') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este pedido não está aguardando análise. Status atual: ' . $order->status,
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $data) {

                // 1. Marca o comprovativo como aprovado
                $proof = PaymentProof::findOrFail($data['proof_id']);
                $proof->update([
                    'status'      => 'approved',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);

                // 2. Rejeita os outros comprovativos pendentes
                $order->paymentProofs()
                    ->where('id', '!=', $proof->id)
                    ->where('status', 'pending')
                    ->update([
                        'status'           => 'rejected',
                        'reviewed_by'      => Auth::id(),
                        'reviewed_at'      => now(),
                        'rejection_reason' => 'Outro comprovativo aprovado.',
                    ]);

                // 3. Atualiza o pedido
                $order->update([
                    'status'         => 'paid',
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                    'approved_at'    => now(),
                ]);

                // 4. Log de rastreio
                $this->tracking->log(
                    $order,
                    'paid',
                    location: $order->seller?->municipality
                );

                // 5. Credita o vendedor
                if ($order->seller_id) {
                    $this->creditSeller($order);
                }
            });

            return response()->json([
                'status'   => 'success',
                'message'  => 'Pagamento aprovado! O vendedor foi notificado.',
                'redirect' => route('admin.orders.show', $order),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro ao aprovar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     * REJEITAR PAGAMENTO
     * ============================================================ */
    public function rejectPayment(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'proof_id' => ['required', 'exists:payment_proofs,id'],
            'reason'   => ['required', 'string', 'max:500'],
        ]);

        if ($order->status !== 'payment_under_review') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este pedido não está aguardando análise. Status atual: ' . $order->status,
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $data) {
                $proof = PaymentProof::findOrFail($data['proof_id']);
                $proof->update([
                    'status'           => 'rejected',
                    'reviewed_by'      => Auth::id(),
                    'reviewed_at'      => now(),
                    'rejection_reason' => $data['reason'],
                ]);

                $order->update([
                    'status' => 'payment_rejected',
                ]);

                $this->tracking->log(
                    $order,
                    'payment_rejected',
                    description: "Comprovativo rejeitado: {$data['reason']}"
                );
            });

            return response()->json([
                'status'   => 'success',
                'message'  => 'Comprovativo rejeitado. Cliente pode reenviar.',
                'redirect' => route('admin.orders.show', $order),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro ao rejeitar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     * MARCAR COMO ENVIADO
     * ============================================================ */
    public function markShipped(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'tracking_code' => ['nullable', 'string', 'max:100'],
        ]);

        if ($order->status !== 'paid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Só é possível marcar como enviado se o pagamento estiver aprovado. Status: ' . $order->status,
            ], 422);
        }

        try {
            $order->update([
                'status'        => 'shipped',
                'shipped_at'    => now(),
                'tracking_code' => $data['tracking_code'],
            ]);

            $this->tracking->log(
                $order,
                'shipped',
                description: $data['tracking_code']
                    ? "Saiu para entrega. Código: {$data['tracking_code']}"
                    : 'Saiu para entrega',
                location: $order->seller?->municipality,
            );

            return response()->json([
                'status'   => 'success',
                'message'  => 'Pedido marcado como enviado!',
                'redirect' => route('admin.orders.show', $order),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     * MARCAR COMO ENTREGUE
     * ============================================================ */
    public function markDelivered(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'tracking_note' => ['nullable', 'string', 'max:200'],
        ]);

        if (! in_array($order->status, ['shipped', 'paid'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este pedido não pode ser marcado como entregue. Status: ' . $order->status,
            ], 422);
        }

        try {
            $order->update([
                'status'          => 'delivered',
                'delivered_at'    => now(),
                'auto_release_at' => now()->addDays((int) Setting::get('guarantee_days', 7)),
            ]);

            $this->tracking->log(
                $order,
                'delivered',
                description: $request->input('tracking_note') ?: 'Entregue ao cliente',
                location: $order->shipping_address['neighborhood'] ?? null,
            );

            return response()->json([
                'status'   => 'success',
                'message'  => 'Pedido marcado como entregue!',
                'redirect' => route('admin.orders.show', $order),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     * HELPER — Credita o vendedor
     * ============================================================ */
    private function creditSeller(Order $order): void
    {
        $wallet = Wallet::forUser($order->seller_id);

        if ($wallet->blocked) return;

        $amount = (float) $order->seller_net_amount;
        $before = (float) $wallet->pending_balance;
        $after  = $before + $amount;

        $wallet->update([
            'pending_balance' => $after,
            'total_earned'    => (float) $wallet->total_earned + $amount,
        ]);

        WalletTransaction::create([
            'wallet_id'      => $wallet->id,
            'order_id'       => $order->id,
            'type'           => 'sale_pending',
            'amount'         => $amount,
            'balance_before' => $before,
            'balance_after'  => $after,
            'description'    => "Venda #{$order->number} aguardando garantia",
            'created_by'     => Auth::id(),
        ]);

        $guaranteeDays = (int) Setting::get('guarantee_days', 7);
        $order->update([
            'auto_release_at' => now()->addDays($guaranteeDays),
        ]);
    }
}