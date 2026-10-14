<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();

            $table->string('reason', 100);        // produto_nao_recebido, produto_errado, defeito, etc
            $table->text('description');
            $table->json('attachments')->nullable(); // até N imagens

            $table->enum('status', [
                'open',
                'under_review',
                'resolved_buyer',     // reembolso total ao cliente
                'resolved_seller',    // mantém pagamento ao vendedor
                'resolved_partial',   // reembolso parcial
                'closed',
            ])->default('open');

            $table->text('resolution_notes')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};