<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Saldo disponível para saque
            $table->decimal('balance', 14, 2)->default(0);

            // Saldo em período de garantia (7 dias)
            $table->decimal('pending_balance', 14, 2)->default(0);

            // Histórico total
            $table->decimal('total_earned', 14, 2)->default(0);
            $table->decimal('total_withdrawn', 14, 2)->default(0);

            // Bloqueio (se admin suspender a conta)
            $table->boolean('blocked')->default(false);
            $table->text('block_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};