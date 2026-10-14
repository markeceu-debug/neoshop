<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\VariantItem;
use App\Services\StockService;
use Illuminate\Console\Command;

class StockCheckCommand extends Command
{
    /**
     * A assinatura do comando.
     */
    protected $signature = 'stock:check
                            {--dry-run : Apenas simula, sem alterar nada}
                            {--only-products : Verifica apenas produtos, ignora variantes}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Sincroniza o status de produtos e variantes com o estoque atual';

    public function __construct(private StockService $stock)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyProducts = (bool) $this->option('only-products');

        $this->info('');
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║     NeoShop — Verificação de Estoque          ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->info('');

        if ($dryRun) {
            $this->warn('  Modo DRY-RUN ativado: nada será alterado.');
            $this->info('');
        }

        $stats = [
            'products_total'    => 0,
            'products_changed'  => 0,
            'items_total'       => 0,
            'items_changed'     => 0,
            'negatives_fixed'   => 0,
        ];

        // ------------------------------------------------------
        // PRODUTOS
        // ------------------------------------------------------
        $this->line('  <fg=cyan>▶ Verificando produtos...</>');

        Product::query()
            ->with('variants.items')
            ->chunk(200, function ($products) use (&$stats, $dryRun) {

                $bar = $this->output->createProgressBar($products->count());
                $bar->start();

                foreach ($products as $product) {
                    $stats['products_total']++;
                    $changed = false;

                    // 1) Corrige estoque negativo (se houver)
                    if ($product->stock < 0) {
                        $stats['negatives_fixed']++;
                        if (! $dryRun) {
                            $this->stock->setAbsolute($product, 0, 'correção automática de negativo');
                        }
                        $changed = true;
                    }

                    // 2) Sincroniza status com o estoque
                    $shouldBeOutOfStock = $product->stock <= 0;
                    $isOutOfStock       = $product->status === 'out_of_stock';

                    if ($shouldBeOutOfStock && ! $isOutOfStock) {
                        $changed = true;
                        if (! $dryRun) {
                            $this->stock->syncProductStatus($product);
                        }
                    } elseif (! $shouldBeOutOfStock && $isOutOfStock) {
                        $changed = true;
                        if (! $dryRun) {
                            $this->stock->syncProductStatus($product);
                        }
                    }

                    if ($changed) $stats['products_changed']++;

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            });

        $this->info('');

        // ------------------------------------------------------
        // VARIANTES
        // ------------------------------------------------------
        if (! $onlyProducts) {
            $this->line('  <fg=cyan>▶ Verificando itens de variantes...</>');

            VariantItem::query()
                ->chunk(200, function ($items) use (&$stats, $dryRun) {

                    $bar = $this->output->createProgressBar($items->count());
                    $bar->start();

                    foreach ($items as $item) {
                        $stats['items_total']++;

                        // Corrige estoque negativo
                        if ($item->stock < 0) {
                            $stats['negatives_fixed']++;
                            if (! $dryRun) {
                                $this->stock->setAbsolute($item, 0, 'correção automática de negativo');
                            }
                            $stats['items_changed']++;
                        }

                        // Sincroniza flag "active" se estoque = 0
                        if ($item->stock === 0 && $item->active) {
                            if (! $dryRun) {
                                $item->update(['active' => false]);
                            }
                            $stats['items_changed']++;
                        } elseif ($item->stock > 0 && ! $item->active) {
                            if (! $dryRun) {
                                $item->update(['active' => true]);
                            }
                            $stats['items_changed']++;
                        }

                        $bar->advance();
                    }

                    $bar->finish();
                    $this->newLine();
                });

            $this->info('');
        }

        // ------------------------------------------------------
        // RELATÓRIO
        // ------------------------------------------------------
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║                   RELATÓRIO                    ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->info('');

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Produtos verificados',   number_format($stats['products_total'])],
                ['Produtos alterados',     number_format($stats['products_changed'])],
                ['Itens verificados',      number_format($stats['items_total'])],
                ['Itens alterados',        number_format($stats['items_changed'])],
                ['Negativos corrigidos',   number_format($stats['negatives_fixed'])],
            ]
        );

        $totalChanges = $stats['products_changed'] + $stats['items_changed'];

        if ($totalChanges === 0) {
            $this->info('');
            $this->info('  ✅ Tudo certo! Nenhuma inconsistência encontrada.');
        } else {
            $this->info('');
            if ($dryRun) {
                $this->warn("  ⚠  DRY-RUN: {$totalChanges} alteração(ões) seriam aplicadas.");
                $this->line('     Rode sem --dry-run para aplicar.');
            } else {
                $this->info("  ✅ {$totalChanges} alteração(ões) aplicadas com sucesso.");
            }
        }

        $this->info('');

        return self::SUCCESS;
    }
}