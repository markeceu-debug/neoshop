<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona o novo valor ao ENUM existente
        DB::statement("
            ALTER TABLE wallet_transactions
            MODIFY COLUMN type ENUM(
                'sale_pending',
                'sale_released',
                'refund',
                'commission',
                'commission_refund',
                'withdrawal',
                'withdrawal_paid'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE wallet_transactions
            MODIFY COLUMN type ENUM(
                'sale_pending',
                'sale_released',
                'refund',
                'commission',
                'withdrawal',
                'withdrawal_paid'
            ) NOT NULL
        ");
    }
};