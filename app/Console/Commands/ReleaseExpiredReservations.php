<?php

namespace App\Console\Commands;

use App\Services\StockService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'stock:release-expired';
    protected $description = 'Libera reservas de carrinho expiradas';

    public function __construct(private StockService $stock) { parent::__construct(); }

    public function handle(): int
    {
        $count = $this->stock->releaseExpired();
        $this->info("✅ {$count} reserva(s) expirada(s) liberada(s).");
        return self::SUCCESS;
    }
}