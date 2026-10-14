<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // NEO-2025-XXXXX
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

           $table->string('status')->default('awaiting_payment')->change();
            // Valores
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Endereços (snapshot)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();

            // Pagamento
            $table->string('payment_method');   // pix, credit_card, boleto, debit_card
            $table->string('payment_status')->default('pending'); // pending, paid, failed

            // Envio
            $table->string('shipping_method')->nullable();   // free, express, sedex
            $table->string('tracking_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};