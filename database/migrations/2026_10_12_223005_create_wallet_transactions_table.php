<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable(); // FK será criada depois

            // Tipo de transação
            $table->enum('type', [
                'sale_pending',      // venda entrou (período de garantia)
                'sale_released',     // dinheiro liberado pra saldo
                'commission',        // comissão descontada
                'withdrawal',        // saque solicitado
                'withdrawal_paid',   // saque pago
                'refund',            // reembolso ao cliente
                'adjustment',        // ajuste manual do admin
            ]);

            $table->decimal('amount', 14, 2);            // pode ser negativo (débito)
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['wallet_id', 'type']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};