<?php

namespace App\Console\Commands;

use App\Services\LowStockService;
use Illuminate\Console\Command;

class StockAlertCommand extends Command
{
    protected $signature = 'stock:alert
                            {--threshold=10 : Limite de estoque para alertar}';

    protected $description = 'Verifica e notifica produtos com estoque baixo';

    public function handle(LowStockService $service): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║     NeoShop — Alerta de Estoque Baixo          ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->info('');

        $result = $service->checkAndNotify();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Novos alertas enviados', $result['notified']],
                ['Alertas limpos (OK)',   $result['cleaned']],
            ]
        );

        if ($result['notified'] === 0 && $result['cleaned'] === 0) {
            $this->info('  ✅ Nenhuma alteração. Estoque em ordem.');
        } elseif ($result['notified'] > 0) {
            $this->warn("  ⚠  {$result['notified']} notificação(ões) enviada(s).");
        }

        $this->info('');

        return self::SUCCESS;
    }
}