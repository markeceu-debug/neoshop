<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseOrdersCommand extends Command
{
    protected $signature = 'orders:release';
    protected $description = 'Libera pagamentos de pedidos entregues há mais de X dias sem disputa';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║   NeoShop — Liberação de Pagamentos          ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        $orders = Order::query()
            ->whereIn('status', ['delivered', 'partially_refunded'])
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->whereDoesntHave('disputes', function ($q) {
                $q->whereIn('status', ['open', 'under_review']);
            })
            ->get();

        if ($orders->isEmpty()) {
            $this->info('  ✅ Nenhum pedido pendente de liberação.');
            return self::SUCCESS;
        }

        $released = 0;
        $failed   = 0;

        foreach ($orders as $order) {
            try {
                $this->releaseOrder($order);
                $released++;
                $this->line("  ✅ Pedido {$order->number} liberado · R$ " . number_format($order->seller_net_amount, 2, ',', '.'));
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  ❌ Pedido {$order->number}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Pedidos liberados', $released],
                ['Falhas',            $failed],
            ]
        );

        return self::SUCCESS;
    }

    private function releaseOrder(Order $order): void
    {
        if (! $order->seller_id) {
            throw new \RuntimeException('Pedido sem vendedor.');
        }

        DB::transaction(function () use ($order) {
            $wallet = Wallet::forUser($order->seller_id);

            if ($wallet->blocked) {
                throw new \RuntimeException('Carteira do vendedor bloqueada.');
            }

            $amount = (float) $order->seller_net_amount;

            // Move de pending_balance → balance
            $pendingBefore = (float) $wallet->pending_balance;
            $balanceBefore = (float) $wallet->balance;

            $wallet->update([
                'pending_balance' => max(0, $pendingBefore - $amount),
                'balance'         => $balanceBefore + $amount,
            ]);

            // Registra no ledger
            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'order_id'       => $order->id,
                'type'           => 'sale_released',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceBefore + $amount,
                'description'    => "Liberação do pedido #{$order->number}",
            ]);

            // Marca o pedido como concluído
            $order->update([
                'status'      => 'completed',
                'released_at' => now(),
            ]);
        });
    }
}
