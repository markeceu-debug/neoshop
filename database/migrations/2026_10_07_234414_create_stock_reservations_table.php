<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->morphs('stockable');            // Product ou VariantItem
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('cart_id')->nullable()->index(); // agrupa itens do mesmo carrinho
            $table->integer('quantity');
            $table->timestamp('expires_at')->index();
            $table->timestamp('committed_at')->nullable(); // quando virou pedido
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};