<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 14, 2);         // valor bruto solicitado
            $table->decimal('commission', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2);     // líquido a receber

            // Método + dados bancários (snapshot)
            $table->string('method', 30);              // bank_transfer | multicaixa_express
            $table->json('bank_data');                 // titular, banco, iban, conta, express

            // Workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('payment_reference')->nullable();  // nº de comprovativo do admin

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};